<?php

namespace App\Support;

use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Support\Collection;

class SmartMatcher
{
    /**
     * Common stop-words that inflate match scores without adding real signal.
     */
    private const STOP_WORDS = [
        'the', 'and', 'for', 'with', 'was', 'that', 'this', 'from', 'are',
        'but', 'not', 'you', 'all', 'can', 'her', 'his', 'has', 'had', 'its',
        'our', 'out', 'were', 'been', 'have', 'will', 'would', 'could',
        'should', 'into', 'about', 'than', 'then', 'them', 'these', 'those',
        'some', 'such', 'very', 'just', 'also', 'item', 'found', 'lost',
        'report', 'please', 'looking', 'think', 'maybe', 'there', 'their',
        'which', 'when', 'where', 'what', 'here', 'over', 'under',
    ];

    /**
     * Minimum score required to consider a pair a valid match.
     */
    public const THRESHOLD = 55;

    /**
     * Compute match score between a lost item and a found item.
     *
     * Scoring breakdown (max 100):
     *  - Same category:       +45
     *  - Shared keywords:     up to +25  (7 pts per shared word, capped)
     *  - Location overlap:    +15
     *  - Date proximity:      up to +15  (graduated: ≤3d → 15, ≤7d → 10, ≤14d → 5)
     */
    public static function score(LostItem $lost, FoundItem $found): int
    {
        $score = 0;

        // --- Category ---
        if ($lost->category_id === $found->category_id) {
            $score += 45;
        }

        // --- Shared keywords (with stop-word filtering) ---
        $lostWords  = self::words($lost->title . ' ' . ($lost->description ?? ''));
        $foundWords = self::words($found->title . ' ' . ($found->description ?? ''));
        $sharedCount = $lostWords->intersect($foundWords)->count();
        $score += min(25, $sharedCount * 7);

        // --- Fuzzy similarity bonus for longer words (≥5 chars, ≥80% similar) ---
        $longLost  = $lostWords->filter(fn($w) => strlen($w) >= 5);
        $longFound = $foundWords->filter(fn($w) => strlen($w) >= 5);
        foreach ($longLost as $lw) {
            foreach ($longFound as $fw) {
                if ($lw !== $fw) {
                    similar_text($lw, $fw, $pct);
                    if ($pct >= 80) {
                        $score += 3;
                        break 2; // one-time bonus
                    }
                }
            }
        }

        // --- Location overlap (bidirectional, min 3 chars) ---
        $ll = strtolower(trim($lost->location_lost ?? ''));
        $lf = strtolower(trim($found->location_found ?? ''));
        if (strlen($ll) >= 3 && strlen($lf) >= 3
            && (str_contains($lf, $ll) || str_contains($ll, $lf))) {
            $score += 15;
        }

        // --- Date proximity (graduated) ---
        if ($lost->date_lost && $found->date_found) {
            $days = abs($lost->date_lost->diffInDays($found->date_found, false));
            if ($days <= 3) {
                $score += 15;
            } elseif ($days <= 7) {
                $score += 10;
            } elseif ($days <= 14) {
                $score += 5;
            }
        }

        return min(100, $score);
    }

    /**
     * Human-readable reasons explaining why two items matched.
     */
    public static function reasons(LostItem $lost, FoundItem $found): array
    {
        $reasons = [];

        if ($lost->category_id === $found->category_id) {
            $reasons[] = 'Same category';
        }

        $lostWords  = self::words($lost->title . ' ' . ($lost->description ?? ''));
        $foundWords = self::words($found->title . ' ' . ($found->description ?? ''));
        if ($lostWords->intersect($foundWords)->isNotEmpty()) {
            $reasons[] = 'Similar details';
        }

        $ll = strtolower(trim($lost->location_lost ?? ''));
        $lf = strtolower(trim($found->location_found ?? ''));
        if (strlen($ll) >= 3 && strlen($lf) >= 3
            && (str_contains($lf, $ll) || str_contains($ll, $lf))) {
            $reasons[] = 'Location match';
        }

        if ($lost->date_lost && $found->date_found) {
            $days = abs($lost->date_lost->diffInDays($found->date_found, false));
            if ($days <= 14) {
                $reasons[] = 'Close date';
            }
        }

        return $reasons;
    }

    /**
     * Tokenise text into meaningful words, stripping stop-words and noise.
     */
    public static function words(string $text): Collection
    {
        return collect(preg_split('/[^a-z0-9]+/i', strtolower($text)))
            ->filter(fn($w) => strlen($w) > 2 && !in_array($w, self::STOP_WORDS, true))
            ->unique()
            ->values();
    }

    /**
     * Build match groups: for each "source" item, find top-N candidates
     * from the "pool" that meet the threshold.
     *
     * @param  Collection<LostItem>   $sources  Lost items to match
     * @param  Collection<FoundItem>  $pool     Found items to match against
     * @param  int                    $topN     Max candidates per source
     * @param  Collection<int>        $dismissedFoundIds  Found-item IDs the user has dismissed
     * @return Collection  Each entry: ['lost' => LostItem, 'candidates' => Collection]
     */
    public static function buildMatches(
        Collection $sources,
        Collection $pool,
        int $topN = 4,
        Collection $dismissedFoundIds = new Collection(),
    ): Collection {
        return $sources->map(function (LostItem $lost) use ($pool, $topN, $dismissedFoundIds) {
            $candidates = $pool
                ->reject(fn(FoundItem $f) => $dismissedFoundIds->contains($f->id))
                ->map(fn(FoundItem $found) => [
                    'found'   => $found,
                    'score'   => self::score($lost, $found),
                    'reasons' => self::reasons($lost, $found),
                ])
                ->filter(fn($m) => $m['score'] >= self::THRESHOLD)
                ->sortByDesc('score')
                ->take($topN)
                ->values();

            return ['lost' => $lost, 'candidates' => $candidates];
        })->filter(fn($g) => $g['candidates']->isNotEmpty())->values();
    }

    /**
     * Build match groups in reverse direction (staff view):
     * for each found item, find top-N lost reports.
     */
    public static function buildReverseMatches(
        Collection $sources,
        Collection $pool,
        int $topN = 4,
        Collection $dismissedLostIds = new Collection(),
    ): Collection {
        return $sources->map(function (FoundItem $found) use ($pool, $topN, $dismissedLostIds) {
            $candidates = $pool
                ->reject(fn(LostItem $l) => $dismissedLostIds->contains($l->id))
                ->map(fn(LostItem $lost) => [
                    'lost'    => $lost,
                    'score'   => self::score($lost, $found),
                    'reasons' => self::reasons($lost, $found),
                ])
                ->filter(fn($m) => $m['score'] >= self::THRESHOLD)
                ->sortByDesc('score')
                ->take($topN)
                ->values();

            return ['found' => $found, 'candidates' => $candidates];
        })->filter(fn($g) => $g['candidates']->isNotEmpty())->values();
    }
}

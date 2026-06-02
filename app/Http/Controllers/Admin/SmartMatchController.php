<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SmartMatchController extends Controller
{
    public function index(): View
    {
        $lostItems = LostItem::with(['user', 'category'])
            ->where('status', 'lost')
            ->latest()
            ->take(30)
            ->get();

        $foundItems = FoundItem::with(['staff', 'category'])
            ->where('status', 'unclaimed')
            ->latest()
            ->take(80)
            ->get();

        $matches = $lostItems->map(function (LostItem $lost) use ($foundItems) {
            $candidates = $foundItems
                ->map(fn(FoundItem $found) => [
                    'found' => $found,
                    'score' => $this->score($lost, $found),
                    'reasons' => $this->reasons($lost, $found),
                ])
                ->filter(fn($match) => $match['score'] >= 45)
                ->sortByDesc('score')
                ->take(3)
                ->values();

            return ['lost' => $lost, 'candidates' => $candidates];
        })->filter(fn($group) => $group['candidates']->isNotEmpty())->values();

        return view('admin.matches.index', compact('matches'));
    }

    private function score(LostItem $lost, FoundItem $found): int
    {
        $score = 0;

        if ($lost->category_id === $found->category_id) {
            $score += 45;
        }

        $lostWords = $this->words($lost->title.' '.$lost->description);
        $foundWords = $this->words($found->title.' '.$found->description);
        $sharedWords = $lostWords->intersect($foundWords)->count();
        $score += min(25, $sharedWords * 7);

        if ($lost->location_lost && $found->location_found && str_contains(strtolower($found->location_found), strtolower($lost->location_lost))) {
            $score += 15;
        }

        if ($lost->date_lost && $found->date_found && abs($lost->date_lost->diffInDays($found->date_found, false)) <= 14) {
            $score += 15;
        }

        return min(100, $score);
    }

    private function reasons(LostItem $lost, FoundItem $found): array
    {
        $reasons = [];

        if ($lost->category_id === $found->category_id) {
            $reasons[] = 'Same category';
        }

        if ($this->words($lost->title.' '.$lost->description)->intersect($this->words($found->title.' '.$found->description))->isNotEmpty()) {
            $reasons[] = 'Similar words';
        }

        if ($lost->date_lost && $found->date_found && abs($lost->date_lost->diffInDays($found->date_found, false)) <= 14) {
            $reasons[] = 'Close date';
        }

        return $reasons;
    }

    private function words(string $text): Collection
    {
        return collect(preg_split('/[^a-z0-9]+/i', strtolower($text)))
            ->filter(fn($word) => strlen($word) > 2)
            ->unique()
            ->values();
    }
}

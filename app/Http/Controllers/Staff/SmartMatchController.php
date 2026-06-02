<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartMatchController extends Controller
{
    public function index(Request $request): View
    {
        $foundItems = FoundItem::with(['category', 'claims'])
            ->where('staff_id', $request->user()->id)
            ->where('status', 'unclaimed')
            ->latest()
            ->take(40)
            ->get();

        $lostItems = LostItem::with(['user', 'category'])
            ->where('status', 'lost')
            ->latest()
            ->take(100)
            ->get();

        $matches = $foundItems->map(function (FoundItem $found) use ($lostItems) {
            $candidates = $lostItems
                ->map(fn(LostItem $lost) => [
                    'lost' => $lost,
                    'score' => $this->score($found, $lost),
                    'reasons' => $this->reasons($found, $lost),
                ])
                ->filter(fn($match) => $match['score'] >= 45)
                ->sortByDesc('score')
                ->take(4)
                ->values();

            return ['found' => $found, 'candidates' => $candidates];
        })->filter(fn($group) => $group['candidates']->isNotEmpty())->values();

        return view('staff.matches.index', compact('matches'));
    }

    private function score(FoundItem $found, LostItem $lost): int
    {
        $score = 0;

        if ($found->category_id === $lost->category_id) {
            $score += 45;
        }

        $sharedWords = $this->words($found->title.' '.$found->description)
            ->intersect($this->words($lost->title.' '.$lost->description))
            ->count();
        $score += min(25, $sharedWords * 7);

        if ($found->location_found && $lost->location_lost && str_contains(strtolower($found->location_found), strtolower($lost->location_lost))) {
            $score += 15;
        }

        if ($found->date_found && $lost->date_lost && abs($found->date_found->diffInDays($lost->date_lost, false)) <= 14) {
            $score += 15;
        }

        return min(100, $score);
    }

    private function reasons(FoundItem $found, LostItem $lost): array
    {
        $reasons = [];

        if ($found->category_id === $lost->category_id) {
            $reasons[] = 'Same category';
        }

        if ($this->words($found->title.' '.$found->description)->intersect($this->words($lost->title.' '.$lost->description))->isNotEmpty()) {
            $reasons[] = 'Similar words';
        }

        if ($found->date_found && $lost->date_lost && abs($found->date_found->diffInDays($lost->date_lost, false)) <= 14) {
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

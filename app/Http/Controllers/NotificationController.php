<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->notifications();
        $notificationStats = [
            ['label' => 'All Alerts', 'value' => (clone $query)->count(), 'icon' => 'fa-bell', 'tone' => 'primary'],
            ['label' => 'Unread', 'value' => (clone $query)->where('is_read', false)->count(), 'icon' => 'fa-envelope', 'tone' => 'warning'],
            ['label' => 'Read', 'value' => (clone $query)->where('is_read', true)->count(), 'icon' => 'fa-envelope-open', 'tone' => 'success'],
        ];

        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(15),
            'notificationStats' => $notificationStats,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->notifications()->update(['is_read' => true]);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'count' => 0,
            ]);
        }

        return back()->with('success', 'Notifications marked as read.');
    }

    public function count(Request $request): JsonResponse
    {
        return response()->json(['count' => $request->user()->notifications()->where('is_read', false)->count()]);
    }

    public function markRead(Request $request, $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'count' => $request->user()->notifications()->where('is_read', false)->count(),
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->user()->notifications()->where('id', $id)->delete();

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'count' => $request->user()->notifications()->where('is_read', false)->count(),
            ]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    public function bulkDestroy(Request $request): JsonResponse|RedirectResponse
    {
        $rawIds = $request->input('ids', '');
        $ids = is_array($rawIds) ? $rawIds : explode(',', $rawIds);
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (!empty($ids)) {
            $request->user()->notifications()->whereIn('id', $ids)->delete();
        }

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'count' => $request->user()->notifications()->where('is_read', false)->count(),
                'deleted' => count($ids),
            ]);
        }

        return back()->with('success', count($ids).' notification(s) deleted.');
    }

    public function bulkRead(Request $request): JsonResponse|RedirectResponse
    {
        $rawIds = $request->input('ids', '');
        $ids = is_array($rawIds) ? $rawIds : explode(',', $rawIds);
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (!empty($ids)) {
            $request->user()->notifications()->whereIn('id', $ids)->update(['is_read' => true]);
        }

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'count' => $request->user()->notifications()->where('is_read', false)->count(),
                'marked_read' => count($ids),
            ]);
        }

        return back()->with('success', count($ids).' notification(s) marked as read.');
    }

    public function dismissMatch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lost_item_id'  => 'nullable|exists:lost_items,id',
            'found_item_id' => 'nullable|exists:found_items,id',
        ]);

        \App\Models\DismissedMatch::firstOrCreate([
            'user_id'       => $request->user()->id,
            'lost_item_id'  => $data['lost_item_id'] ?? null,
            'found_item_id' => $data['found_item_id'] ?? null,
        ]);

        return back()->with('success', 'Match dismissed.');
    }
}

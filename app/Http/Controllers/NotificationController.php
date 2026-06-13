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
        $ids = is_array($rawIds) ? $rawIds : array_filter(explode(',', $rawIds));
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
}

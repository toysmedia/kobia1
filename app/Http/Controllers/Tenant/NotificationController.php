<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\TenantNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Handles tenant admin notifications.
 * All methods operate on the tenant DB connection.
 */
class NotificationController extends Controller
{
    /**
     * Full notification center page (paginated).
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $query = TenantNotification::latest();

        match ($filter) {
            'unread'           => $query->unread(),
            'broadcast'        => $query->ofType('broadcast'),
            'system_warning'   => $query->ofType('system_warning'),
            'feature_release'  => $query->ofType('feature_release'),
            'subscription_alert' => $query->ofType('subscription_alert'),
            default            => null,
        };

        $notifications = $query->paginate(20)->withQueryString();

        $unreadCount = TenantNotification::unread()->count();

        return view('admin.pages.notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $notification = TenantNotification::findOrFail($id);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        TenantNotification::unread()->update(['is_read' => true, 'read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Return the unread notification count (for AJAX polling).
     */
    public function count(): JsonResponse
    {
        return response()->json([
            'count' => TenantNotification::unread()->count(),
        ]);
    }
}
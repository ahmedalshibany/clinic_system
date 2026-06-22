<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Return the count of unread notifications (for AJAX).
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get latest notifications for dropdown (for AJAX).
     */
    public function getLatest()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return response()->json([
            'html' => view('notifications.partials.dropdown-items', compact('notifications'))->render(),
            'count' => Notification::where('user_id', auth()->id())->unread()->count()
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return back()->with('success', __('messages.notificationMarkedRead'));
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', __('messages.allNotificationsMarkedRead'));
    }

    /**
     * Live feed — returns unread count + new notifications since a timestamp.
     */
    public function liveFeed(Request $request)
    {
        $since = $request->query('since');

        $query = Notification::where('user_id', auth()->id());

        $new = [];
        if ($since) {
            $new = (clone $query)
                ->where('created_at', '>', $since)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($n) {
                    return [
                        'id'               => $n->id,
                        'type'             => $n->type,
                        'title'            => $n->title,
                        'message'          => $n->message,
                        'created_at_diff'  => $n->created_at->diffForHumans(),
                        'link'             => $n->link,
                    ];
                });
        }

        $count = (clone $query)->unread()->count();

        return response()->json([
            'count' => $count,
            'new'   => $new,
        ]);
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', __('messages.notificationRemoved'));
    }

    /**
     * Delete all notifications.
     */
    public function clearAll()
    {
        Notification::where('user_id', auth()->id())->delete();

        return back()->with('success', __('messages.allNotificationsCleared'));
    }
}

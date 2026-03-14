<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    // ── GET /api/notifications ────────────────────────────────
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success'    => true,
            'data'       => $notifications->items(),
            'pagination' => [
                'total'        => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
            ],
        ]);
    }

    // ── GET /api/notifications/unread-count ───────────────────
    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['success' => true, 'count' => $count]);
    }

    // ── PATCH /api/notifications/{id}/read ────────────────────
    public function markRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->update(['read_at' => Carbon::now()]);

        return response()->json(['success' => true, 'message' => 'Marked as read.']);
    }

    // ── POST /api/notifications/read-all ─────────────────────
    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
    }

    // ── DELETE /api/notifications/{id} ───────────────────────
    public function destroy(Request $request, $id)
    {
        Notification::where('user_id', $request->user()->id)
            ->findOrFail($id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Notification deleted.']);
    }
}

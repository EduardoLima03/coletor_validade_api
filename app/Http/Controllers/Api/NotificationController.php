<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::forUser(auth()->id())
            ->orderBy('created_at', 'desc');

        $unreadOnly = $request->boolean('unread_only');
        if ($unreadOnly) {
            $query->unread();
        }

        $limit = min((int) $request->get('limit', 50), 100);
        $notifications = $query->limit($limit)->get();

        $unreadCount = Notification::forUser(auth()->id())
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::forUser(auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notificação marcada como lida.']);
    }

    public function markAllAsRead()
    {
        Notification::forUser(auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Todas as notificações foram marcadas como lidas.']);
    }
}

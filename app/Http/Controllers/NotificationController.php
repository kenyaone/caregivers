<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return view('shared.notifications', compact('notifications'));
    }
}

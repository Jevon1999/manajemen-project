<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->user_id === (int) $id;
});

// Task notification channel - user dapat notifikasi tentang task mereka
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->user_id === (int) $userId;
});

// Project channel - semua member project dapat notifikasi
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return \App\Models\ProjectMember::where('project_id', $projectId)
        ->where('user_id', $user->user_id)
        ->exists();
});

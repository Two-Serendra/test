<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('role.{roleId}', function ($user, $roleId) {
    return (int) $user->role_id === (int) $roleId;
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Private per-user channel: notification bell + background export events.
Broadcast::channel('App.Models.User.{id}', fn (User $user, int|string $id): bool => (int) $user->id === (int) $id);

<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Media $media): bool
    {
        return $this->update($user, $media);
    }

    public function update(User $user, Media $media): bool
    {
        return $user->canPublish() || $media->user_id === $user->id;
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->canPublish() && ! $media->articles()->withTrashed()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canPublish();
    }
}

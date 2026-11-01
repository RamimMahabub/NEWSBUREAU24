<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        return $this->update($user, $article);
    }

    public function update(User $user, Article $article): bool
    {
        return $user->canPublish() || ($article->user_id === $user->id && in_array($article->status, ['draft', 'review'], true));
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->canPublish();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canPublish();
    }

    public function restoreAny(User $user): bool
    {
        return $user->canPublish();
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->canPublish();
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return false;
    }
}

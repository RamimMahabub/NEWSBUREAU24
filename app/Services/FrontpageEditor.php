<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Frontpage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FrontpageEditor
{
    public function validate(array $layout, User $user): array
    {
        abort_unless($user->canPublish(), 403);
        validator($layout, ['lead' => 'required|array|size:1', 'lead.*' => 'required|integer', 'top' => 'present|array|max:4', 'top.*' => 'required|integer'])->validate();
        $ids = array_merge($layout['lead'], $layout['top']);
        if (count(array_unique($ids)) !== count($ids)) {
            throw ValidationException::withMessages(['layout' => 'Each article may appear only once in the top stories.']);
        }
        $eligible = Article::whereIn('id', $ids)->where(fn ($q) => $q->published('bn')->orWhere(fn ($q) => $q->published('en')))->count();
        if ($eligible !== count($ids)) {
            throw ValidationException::withMessages(['layout' => 'Every selected story must have a published translation.']);
        }

        return ['lead' => array_map('intval', $layout['lead']), 'top' => array_map('intval', $layout['top'])];
    }

    public function draft(User $user): Frontpage
    {
        abort_unless($user->canPublish(), 403);

        return DB::transaction(function () {
            $live = Frontpage::where('state', 'live')->lockForUpdate()->latest('id')->first();

            return Frontpage::firstOrCreate(['state' => 'draft'], ['layout' => $live?->layout ?? ['lead' => [], 'top' => []], 'version' => 1]);
        });
    }

    public function save(array $layout, int $version, User $user, bool $publish = false): Frontpage
    {
        $layout = $this->validate($layout, $user);

        return DB::transaction(function () use ($layout, $version, $user, $publish) {
            $draft = Frontpage::where('state', 'draft')->lockForUpdate()->firstOrFail();
            if ($draft->version !== $version) {
                throw ValidationException::withMessages(['layout' => 'Another editor changed the homepage. Reload to compare; your current positions have not been published.']);
            }
            $draft->update(['layout' => $layout, 'version' => $version + 1, 'user_id' => $user->id]);
            if ($publish) {
                Frontpage::where('state', 'live')->update(['state' => 'archived']);
                Frontpage::create(['state' => 'live', 'layout' => $layout, 'version' => $draft->version, 'user_id' => $user->id]);
            }

            return $draft;
        });
    }
}

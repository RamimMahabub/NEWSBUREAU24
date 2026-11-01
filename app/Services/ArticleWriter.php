<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ArticleWriter
{
    public function save(?Article $article, array $data, User $user, ?int $version = null): Article
    {
        return DB::transaction(function () use ($article, $data, $user, $version) {
            if ($article) {
                $article = Article::lockForUpdate()->findOrFail($article->id);
                Gate::forUser($user)->authorize('update', $article);
                if ($version !== null && $article->version !== $version) {
                    throw ValidationException::withMessages(['data.title.bn' => 'Another editor saved this article. Your changes are retained in the autosave. Reload and compare before saving.']);
                }
            } else {
                Gate::forUser($user)->authorize('create', Article::class);
            }
            if (! $user->canPublish() && (in_array($data['status'] ?? 'draft', ['approved', 'published', 'scheduled', 'archived'], true)
                || ! empty($data['published_bn_at']) || ! empty($data['published_en_at']))) {
                abort(403);
            }
            validator($data, ['title.bn' => 'required|string|max:250', 'title.en' => 'nullable|string|max:250',
                'summary.bn' => 'nullable|string|max:1000', 'summary.en' => 'nullable|string|max:1000',
                'body.bn' => 'nullable|string|max:500000', 'body.en' => 'nullable|string|max:500000',
                'category' => 'required|in:'.implode(',', array_keys(Article::CATEGORIES)), 'district' => 'nullable|string|max:80',
                'slug' => 'nullable|alpha_dash:ascii|max:180|unique:articles,slug,'.($article?->id ?? 'NULL'),
                'status' => 'required|in:draft,review,approved,scheduled,published,archived',
                'published_bn_at' => 'nullable|date', 'published_en_at' => 'nullable|date', 'media_id' => 'nullable|integer|exists:media,id',
                'editorial_notes' => 'nullable|string|max:20000'])->validate();
            if ($mediaId = $data['media_id'] ?? null) {
                $media = Media::findOrFail($mediaId);
                abort_unless($user->canPublish() || $media->user_id === $user->id, 403);
            }
            if (in_array($data['status'], ['published', 'scheduled'], true)) {
                $hasLanguage = false;
                foreach (['bn', 'en'] as $locale) {
                    if (! empty($data['published_'.$locale.'_at'])) {
                        $hasLanguage = true;
                        if (empty($data['title'][$locale]) || empty(trim(strip_tags($data['body'][$locale] ?? '')))) {
                            throw ValidationException::withMessages(["data.body.$locale" => 'A headline and article body are required for each published language.']);
                        }
                    }
                }
                if (! $hasLanguage) {
                    throw ValidationException::withMessages(['data.published_bn_at' => 'Select a publication date for at least one language.']);
                }
            }
            $values = Arr::only($data, ['title', 'summary', 'body', 'category', 'district', 'slug', 'status', 'published_bn_at', 'published_en_at', 'media_id', 'editorial_notes']);
            $values['version'] = ($article?->version ?? 0) + 1;
            if (! $article) {
                $values['user_id'] = $user->id;
                $article = new Article;
            }
            $article->fill($values)->save();
            DB::table('editor_drafts')->where('article_id', $article->id)->where('user_id', $user->id)->delete();

            return $article;
        });
    }
}

<?php

namespace App\Models;

use App\Support\CleanHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['title' => 'array', 'summary' => 'array', 'body' => 'array', 'is_demo' => 'boolean',
            'published_bn_at' => 'datetime', 'published_en_at' => 'datetime'];
    }

    public const CATEGORIES = ['national' => 'জাতীয়', 'crime' => 'অপরাধ', 'courts' => 'আদালত',
        'investigations' => 'অনুসন্ধান', 'districts' => 'জেলা', 'special-reports' => 'বিশেষ প্রতিবেদন',
        'video' => 'ভিডিও', 'data-maps' => 'ডেটা ও মানচিত্র', 'opinion' => 'মতামত'];

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            if (! $article->slug) {
                $article->slug = (Str::slug($article->title['en'] ?? '') ?: 'story').'-'.Str::lower(Str::random(8));
            }
            $bodies = $article->body ?? [];
            foreach ($bodies as $locale => $html) {
                $bodies[$locale] = CleanHtml::sanitize($html ?? '');
            }
            $article->body = $bodies;
        });
        static::saved(function (Article $article) {
            ArticleRevision::create(['article_id' => $article->id, 'user_id' => auth()->id(),
                'snapshot' => $article->attributesToArray(), 'event' => $article->status]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function revisions()
    {
        return $this->hasMany(ArticleRevision::class);
    }

    public function scopePublished(Builder $query, string $locale): Builder
    {
        abort_unless(in_array($locale, ['bn', 'en'], true), 404);

        return $query->where('status', 'published')->whereNotNull("published_{$locale}_at")
            ->where("published_{$locale}_at", '<=', now());
    }

    public function scopeCards(Builder $query): Builder
    {
        return $query->select(['id', 'user_id', 'media_id', 'title', 'summary', 'slug', 'category', 'district', 'status',
            'published_bn_at', 'published_en_at', 'demo_image', 'is_demo', 'updated_at', 'created_at']);
    }

    public function text(string $field, ?string $locale = null): string
    {
        return $this->{$field}[$locale ?? app()->getLocale()] ?? '';
    }

    public function image(): string
    {
        return $this->media ? route('media.show', $this->media) : asset('images/'.($this->demo_image ?: 'masthead').'.webp');
    }

    public function url(?string $locale = null): string
    {
        return route('article', ['locale' => $locale ?? app()->getLocale(), 'slug' => $this->slug]);
    }
}

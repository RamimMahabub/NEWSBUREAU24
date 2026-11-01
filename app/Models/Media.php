<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['alt' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Media $media) {
            if ($media->articles()->withTrashed()->exists()) {
                throw new \RuntimeException('This image is referenced by an article. Remove its references before deleting it.');
            }
        });
        static::deleted(function (Media $media) {
            DB::afterCommit(fn () => Storage::disk('local')->delete($media->path));
        });
    }
}

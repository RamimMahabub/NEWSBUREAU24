<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublishDueArticles extends Command
{
    protected $signature = 'articles:publish-due';

    protected $description = 'Publish approved scheduled articles with a due language publication date';

    public function handle(): int
    {
        $count = 0;
        Article::where('status', 'scheduled')->where(fn ($q) => $q->where('published_bn_at', '<=', now())->orWhere('published_en_at', '<=', now()))->select('id')->chunkById(100, function ($rows) use (&$count) {
            foreach ($rows as $row) {
                DB::transaction(function () use ($row, &$count) {
                    $article = Article::lockForUpdate()->find($row->id);
                    if ($article?->status !== 'scheduled') {
                        return;
                    }
                    $article->status = 'published';
                    $article->version++;
                    $article->save();
                    $count++;
                });
            }
        });
        $this->info("Published {$count} due articles.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Services\ArticleWriter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ArticleWriter::class)->save(null, $data, auth()->user());
    }

    protected function getRedirectUrl(): string
    {
        return ArticleResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}

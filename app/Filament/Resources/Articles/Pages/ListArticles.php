<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Story')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Stories')
                ->badge(Article::query()->when(! auth()->user()->canPublish(), fn ($q) => $q->where('user_id', auth()->id()))->count()),

            'published' => Tab::make('Published')
                ->badge(Article::query()->when(! auth()->user()->canPublish(), fn ($q) => $q->where('user_id', auth()->id()))->where('status', 'published')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),

            'review' => Tab::make('In Review')
                ->badge(Article::query()->when(! auth()->user()->canPublish(), fn ($q) => $q->where('user_id', auth()->id()))->where('status', 'review')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'review')),

            'draft' => Tab::make('Drafts')
                ->badge(Article::query()->when(! auth()->user()->canPublish(), fn ($q) => $q->where('user_id', auth()->id()))->where('status', 'draft')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),

            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived')),
        ];
    }
}

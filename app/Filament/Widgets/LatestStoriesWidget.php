<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestStoriesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest Stories Activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Article::query()->latest('updated_at')->limit(8)
            )
            ->columns([
                TextColumn::make('title.bn')
                    ->label('Bangla Headline')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('title.en')
                    ->label('English Headline')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('-', ' ', $state))),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'published' => 'success',
                        'review' => 'warning',
                        'approved' => 'info',
                        'archived' => 'gray',
                        default => 'secondary'
                    }),

                TextColumn::make('user.name')
                    ->label('Author')
                    ->default('Editorial Team'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since(),
            ])
            ->recordUrl(
                fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record])
            )
            ->paginated(false);
    }
}


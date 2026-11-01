<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title.bn')->label('Bangla headline')->searchable()->limit(65)->wrap(),
                TextColumn::make('title.en')->label('English headline')->searchable()->limit(50)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'published' => 'success','review' => 'warning','archived' => 'gray',default => 'info'
                }),
                TextColumn::make('category')->badge(),
                TextColumn::make('user.name')->label('Reporter'),
                TextColumn::make('updated_at')->dateTime('d M H:i')->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'review' => 'Review', 'approved' => 'Approved', 'scheduled' => 'Scheduled', 'published' => 'Published', 'archived' => 'Archived']),
                SelectFilter::make('category')->options(Article::CATEGORIES),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])->defaultSort('updated_at', 'desc')->paginationPageOptions([15, 30, 50]);
    }
}

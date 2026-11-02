<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
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
                ImageColumn::make('featured_thumbnail')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->image())
                    ->square()
                    ->size(44),

                TextColumn::make('title.bn')
                    ->label('Bangla Headline')
                    ->searchable()
                    ->weight('bold')
                    ->limit(60)
                    ->wrap()
                    ->description(fn (Article $record): string => $record->text('summary', 'bn') ? \Illuminate\Support\Str::limit($record->text('summary', 'bn'), 70) : ''),

                TextColumn::make('title.en')
                    ->label('English Headline')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('-', ' ', $state)))
                    ->colors([
                        'primary' => 'investigations',
                        'danger' => 'crime',
                        'warning' => 'courts',
                        'success' => 'special-reports',
                        'info' => 'national',
                    ]),

                TextColumn::make('district')
                    ->label('District')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'published' => 'success',
                        'review' => 'warning',
                        'approved' => 'info',
                        'archived' => 'gray',
                        default => 'danger'
                    }),

                TextColumn::make('user.name')
                    ->label('Reporter')
                    ->icon('heroicon-m-user')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'In Review',
                        'approved' => 'Approved',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                SelectFilter::make('category')
                    ->options(Article::CATEGORIES),
                SelectFilter::make('district')
                    ->options([
                        'dhaka' => 'Dhaka',
                        'narayanganj' => 'Narayanganj',
                        'chattogram' => 'Chattogram',
                        'cumilla' => 'Cumilla',
                    ]),
            ])
            ->recordActions([
                Action::make('view_live')
                    ->label('View Story')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Article $record): string => route('article', ['locale' => 'bn', 'slug' => $record->slug]))
                    ->openUrlInNewTab()
                    ->visible(fn (Article $record) => $record->status === 'published'),

                Action::make('publish_now')
                    ->label('Publish')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Article $record) => auth()->user()->canPublish() && $record->status !== 'published')
                    ->action(function (Article $record) {
                        $record->update([
                            'status' => 'published',
                            'published_bn_at' => $record->published_bn_at ?? now(),
                            'published_en_at' => $record->published_en_at ?? now(),
                        ]);
                        Notification::make()
                            ->title('Article published successfully')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginationPageOptions([15, 30, 50]);
    }
}

<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Preview')
                    ->getStateUsing(fn ($record) => route('media.show', $record))
                    ->square()
                    ->size(56),

                TextColumn::make('original_name')
                    ->searchable()
                    ->weight('bold')
                    ->label('File Name')
                    ->description(fn (Media $record): string => $record->credit ? 'Credit: '.$record->credit : ''),

                TextColumn::make('mime')
                    ->badge()
                    ->label('Type')
                    ->color('gray'),

                TextColumn::make('size')
                    ->label('File Size')
                    ->formatStateUsing(function ($state) {
                        if (! $state) return '0 B';
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $power = $state > 0 ? floor(log($state, 1024)) : 0;
                        return number_format($state / pow(1024, $power), 1) . ' ' . $units[$power];
                    }),

                TextColumn::make('articles_count')
                    ->counts('articles')
                    ->label('Used In')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->label('Uploaded At'),
            ])
            ->recordActions([
                Action::make('open_file')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Media $record) => route('media.show', $record))
                    ->openUrlInNewTab(),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([15, 30, 50]);
    }
}

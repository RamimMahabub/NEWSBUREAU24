<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NewsroomStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $publishedCount = Article::where('status', 'published')->count();
        $inReviewCount = Article::where('status', 'review')->count();
        $draftsCount = Article::where('status', 'draft')->count();
        $mediaCount = Media::count();

        return [
            Stat::make('Published Stories', $publishedCount)
                ->description('Active stories live on site')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending Editorial Review', $inReviewCount)
                ->description('Stories requiring editorial approval')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($inReviewCount > 0 ? 'warning' : 'gray'),

            Stat::make('Drafts in Progress', $draftsCount)
                ->description('Work in progress by reporters')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Media Assets', $mediaCount)
                ->description('Uploaded photos & graphics')
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary'),
        ];
    }
}


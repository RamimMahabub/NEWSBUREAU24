<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use App\Models\Media;
use App\Services\MediaUploader;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Language')->tabs(array_map(fn ($locale) => Tab::make($locale === 'bn' ? 'বাংলা' : 'English')->schema([
                    TextInput::make('title.'.$locale)->label('Headline')->required($locale === 'bn')->maxLength(250)->live(debounce: 1500),
                    Textarea::make('summary.'.$locale)->label('Summary')->rows(3)->maxLength(1000)->live(debounce: 1800),
                    RichEditor::make('body.'.$locale)->label('Article body')->fileAttachments(false)
                        ->toolbarButtons([['bold', 'italic', 'underline', 'strike', 'link'], ['h2', 'h3', 'blockquote'], ['bulletList', 'orderedList', 'table'], ['undo', 'redo']])
                        ->live(debounce: 2500)->columnSpanFull(),
                    DateTimePicker::make('published_'.$locale.'_at')->label('Publish this language at')->seconds(false)->timezone('Asia/Dhaka')
                        ->helperText('Leave empty to keep this translation private. Future dates are hidden until due.')
                        ->visible(fn () => auth()->user()->canPublish()),
                ]), ['bn', 'en']))->columnSpanFull(),
                Section::make('Story details')->schema([
                    Select::make('category')->options(Article::CATEGORIES)->required()->default('crime'),
                    Select::make('district')->options(['dhaka' => 'Dhaka', 'narayanganj' => 'Narayanganj', 'chattogram' => 'Chattogram', 'cumilla' => 'Cumilla'])->searchable(),
                    TextInput::make('slug')->maxLength(180)->unique(ignoreRecord: true)->helperText('Generated automatically when empty.'),
                    Select::make('status')->options(fn () => auth()->user()->canPublish() ? ['draft' => 'Draft', 'review' => 'In review', 'approved' => 'Approved', 'scheduled' => 'Scheduled', 'published' => 'Published', 'archived' => 'Archived'] : ['draft' => 'Draft', 'review' => 'Submit for review'])->default('draft')->required(),
                    Select::make('media_id')->label('Featured image')->searchable()->getSearchResultsUsing(fn (string $search) => Media::query()->when(! auth()->user()->canPublish(), fn ($q) => $q->where('user_id', auth()->id()))->where('original_name', 'like', '%'.$search.'%')->limit(30)->pluck('original_name', 'id'))
                        ->getOptionLabelUsing(fn ($value) => Media::query()->when(! auth()->user()->canPublish(), fn ($q) => $q->where('user_id', auth()->id()))->find($value)?->original_name)
                        ->createOptionForm([FileUpload::make('image')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(10240)->storeFiles(false)->required(), TextInput::make('alt_bn')->label('Bangla alternative text')->maxLength(250), TextInput::make('alt_en')->label('English alternative text')->maxLength(250), TextInput::make('credit')->maxLength(500)])
                        ->createOptionUsing(function (array $data) {
                            $media = app(MediaUploader::class)->upload($data['image'], auth()->user());
                            $media->update(['alt' => ['bn' => $data['alt_bn'] ?? '', 'en' => $data['alt_en'] ?? ''], 'credit' => $data['credit'] ?? null]);

                            return $media->id;
                        })->createOptionAction(fn (Action $action) => $action->label('Upload image')->modalHeading('Upload featured image')),
                    Textarea::make('editorial_notes')->label('Internal editorial notes')->maxLength(20000)->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(10240)->storeFiles(false)->required()->visibleOn('create'),
                TextInput::make('original_name')->label('Filename')->maxLength(240)->visibleOn('edit'),
                TextInput::make('alt.bn')->label('Alternative text — বাংলা')->maxLength(250),
                TextInput::make('alt.en')->label('Alternative text — English')->maxLength(250),
                TextInput::make('credit')->label('Photo credit / source')->maxLength(500),
            ]);
    }
}

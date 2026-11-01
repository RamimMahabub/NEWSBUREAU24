<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Services\MediaUploader;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $media = app(MediaUploader::class)->upload($data['image'], auth()->user());
        $media->update(Arr::only($data, ['alt', 'credit']));

        return $media;
    }
}

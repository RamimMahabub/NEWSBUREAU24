<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaUploader
{
    public function upload(UploadedFile $file, User $user): Media
    {
        validator(['image' => $file], ['image' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:10240|dimensions:max_width=12000,max_height=12000'])->validate();
        $dimensions = getimagesize($file->getRealPath());
        if (! $dimensions || $dimensions[0] * $dimensions[1] > 24000000) {
            throw ValidationException::withMessages(['image' => 'Images must contain at most 24 megapixels.']);
        }
        $hash = hash_file('sha256', $file->getRealPath());
        if ($existing = Media::where('hash', $hash)->first()) {
            if ($existing->user_id !== $user->id && ! $user->canPublish()) {
                throw ValidationException::withMessages(['image' => 'This file is already managed by another user. Ask an editor to attach it.']);
            }

            return $existing;
        }
        $source = imagecreatefromstring($file->get());
        if (! $source) {
            throw ValidationException::withMessages(['image' => 'Unable to decode image.']);
        }
        $scale = min(1, 2000 / max(imagesx($source), imagesy($source)));
        $image = imagescale($source, max(1, (int) (imagesx($source) * $scale)), max(1, (int) (imagesy($source) * $scale)));
        ob_start();
        imagewebp($image, null, 85);
        $encoded = ob_get_clean();
        imagedestroy($source);
        imagedestroy($image);
        $path = 'media/'.Str::uuid().'.webp';
        Storage::disk('local')->put($path, $encoded);
        try {
            return Media::create(['user_id' => $user->id, 'path' => $path, 'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 240),
                'mime' => 'image/webp', 'size' => strlen($encoded), 'hash' => $hash]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
    }
}

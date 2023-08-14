<?php

namespace App\Actions\File;

use Exception;
use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class UploadFileAction
{
    public function execute(Model $model, UploadedFile $uploadedFile): File
    {
        $disk = Storage::disk('public');

        $file = new File();
        $file->model()->associate($model);
        $file->name = Str::random(8);
        $file->type = $file->getClientMimeType();
        $file->path = $this->saveFile($file->name, $uploadedFile, $disk);
        $file->size = $disk->size($file->path);

        if ($file->path === false) {
            throw new Exception('Failed to upload file.');
        }

        $file->save();

        return $file;
    }

    private function saveFile(string $name, UploadedFile $uploadedFile, FilesystemAdapter $storage): string|false
    {
        $basepath = Str::uuid();
        $filename = "{$name}.{$uploadedFile->getClientOriginalExtension()}";

        return $storage->putFileAs($basepath, $uploadedFile, $filename);
    }
}

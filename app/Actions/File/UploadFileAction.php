<?php

namespace App\Actions\File;

use Exception;
use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UploadFileAction
{
    public function execute(Model $model, UploadedFile $uploadedFile): File
    {
        $file = new File();
        $file->name = Str::random(8);
        $file->type = $uploadedFile->getClientMimeType();
        $file->path = $this->saveFile($file->name, $uploadedFile);
        $file->size = Storage::disk('public')->size($file->path);

        $file->model()->associate($model);
        $file->save();

        return $file;
    }

    private function saveFile(string $name, UploadedFile $uploadedFile): string
    {
        $basepath = Str::uuid();
        $filename = "{$name}.{$uploadedFile->getClientOriginalExtension()}";

        $path = Storage::disk('public')->putFileAs($basepath, $uploadedFile, $filename);

        if ($path === false) {
            throw new Exception('Failed to upload file.');
        }

        return $path;
    }
}

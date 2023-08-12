<?php

namespace App\Actions\File;

use Exception;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UploadFileAction
{
    public function execute(Model $model, UploadedFile $file): File
    {
        try {
            $filename = $file->getBasename();
            $mimeType = $file->getClientMimeType();

            $disk = Storage::disk('public');

            $path = $disk->putFile($file->getClientOriginalName(), $file);

            if ($path === false) {
                throw new Exception('Failed to upload file.');
            }

            $size = $disk->size($path);
        } catch (Exception $th) {
            throw $th;
        }

        return File::query()->create([
            'model_id' => $model->getKey(),
            'model_type' => $model->getMorphClass(),
            'name' => $filename,
            'path' => $path,
            'size' => $size,
            'type' => $mimeType,
        ]);
    }
}

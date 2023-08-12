<?php

namespace App\Actions\File;

use App\Models\File;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UploadFileAction
{
    function execute(Model $model, UploadedFile $file): File
    {
        try {
            $filename = $file->getBasename();
            $mimeType = $file->getClientMimeType();

            $disk = Storage::disk('public');

            $path = $disk->putFile($file->getClientOriginalName(), $file);

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

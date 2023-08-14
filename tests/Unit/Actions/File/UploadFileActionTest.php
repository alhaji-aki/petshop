<?php

namespace Tests\Unit\Actions\File;

use App\Actions\File\UploadFileAction;
use App\Models\File;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(
    TestCase::class,
    LazilyRefreshDatabase::class
);

beforeEach(function () {
    Storage::fake('public');

    $this->model = User::factory()->create();
});

it('successfully uploads a file and creates a File model', function () {
    $uploadedFile = UploadedFile::fake()->image('test-image.jpg');

    $action = new UploadFileAction();

    $file = $action->execute($this->model, $uploadedFile);

    expect($file)->toBeInstanceOf(File::class);
    expect($file->model_id)->toBe($this->model->getKey());
    expect($file->model_type)->toBe($this->model->getMorphClass());
    expect($file->size)->toBe($uploadedFile->getSize());
    expect($file->type)->toBe($uploadedFile->getClientMimeType());

    $this->assertModelExists($file);

    Storage::disk('public')->assertExists($file->path);
});

it('throws an exception when file upload fails', function () {
    Storage::shouldReceive('disk->putFileAs')->andReturn(false);
    Storage::shouldReceive('disk->size')->andReturn(10);

    $uploadedFile = UploadedFile::fake()->image('test-image.jpg');

    $action = new UploadFileAction();

    $action->execute($this->model, $uploadedFile);
})->throws(Exception::class, 'Failed to upload file.');

<?php

namespace App\Exceptions;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (NotFoundHttpException $exception) {
            if ($exception->getPrevious() instanceof ModelNotFoundException) {
                $modelName = Str::of($exception->getPrevious()->getModel())
                    ->afterLast('\\')
                    ->snake(' ')
                    ->title()
                    ->trim()
                    ->toString();

                return response()->json(['message' => "{$modelName} not found."], 404);
            }

            return response()->json([
                'message' => 'Resource is not available.',
            ], 404);
        });
    }
}

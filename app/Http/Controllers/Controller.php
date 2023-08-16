<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *     description="This API has been created with the goal to test the coding skills of the candidates who are applying for a job position at [Buckhill](https://www.buckhill.co.uk/careers/work-with-us).",
 *     version="1.0.0",
 *     title="Pet Shop API - Swagger Documentation",
 * )
 * @OA\OpenApi(
 *   security={{"bearerAuth": {}}}
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer"
 * )
 * @OA\Tag(
 *     name="User",
 *     description="User API endpoint"
 * )
 * @OA\Tag(
 *     name="Payments",
 *     description="Payments API endpoint"
 * )
 * @OA\Tag(
 *     name="Orders",
 *     description="Orders API endpoint"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}

<?php

namespace App\Modules\BloodBank\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

abstract class BloodBankApiController extends Controller
{
    protected function success(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function paginated(LengthAwarePaginator $paginator, callable $transform): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'message'    => 'Results loaded.',
            'data'       => $paginator->getCollection()->map($transform)->values(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    protected function conflict(BloodBankException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 409);
    }
}

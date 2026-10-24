<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class SeederController extends Controller
{
    public function runSeeder(): JsonResponse
    {
        // Safety check: Restrict to local/development environment unless authorized
        if (! app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'Action restricted to local development environment.',
            ], 403);
        }

        try {
            // Run a specific seeder class
            Artisan::call('db:seed', [
                '--class' => 'PregnancyContentSeeder', // Replace with your target seeder class name
                '--force' => true, // Bypass production warnings if needed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Seeder executed successfully.',
                'output'  => Artisan::output(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seeder execution failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

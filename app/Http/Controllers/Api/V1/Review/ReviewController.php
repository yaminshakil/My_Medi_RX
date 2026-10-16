<?php

namespace App\Http\Controllers\Api\V1\Review;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type');
        $id = $request->get('id');

        $reviews = Review::where('reviewable_type', $type)
            ->where('reviewable_id', $id)
            ->with('user')
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewable_type' => 'required|string',
            'reviewable_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();

        $review = Review::create($validated);

        return ApiResponseService::success(
            $review,
            'Appointments booked successfully!'
        );
    }
}

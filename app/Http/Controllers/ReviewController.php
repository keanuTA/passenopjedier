<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\SittingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Review request ontvangen:', $request->all());

        try {
            $validated = $request->validate([
                'sitting_request_id' => 'required|exists:sitting_requests,id',
                'rating' => 'required|integer|min:1|max:5',
                'review_text' => 'required|string|min:3'
            ]);

            $sittingRequest = SittingRequest::findOrFail($validated['sitting_request_id']);
            
            // Debug log
            Log::info('Sitting Request Data:', [
                'id' => $sittingRequest->id,
                'sitter_profile_id' => $sittingRequest->sitter_profile_id,
                'status' => $sittingRequest->status
            ]);

            // Maak de review data klaar
            $reviewData = [
                'sitting_request_id' => $validated['sitting_request_id'],
                'user_id' => auth()->id(),
                'sitter_profile_id' => $sittingRequest->sitter_profile_id,
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text']
            ];

            Log::info('Attempting to create review with data:', $reviewData);

            // Probeer de review direct via DB te maken om te zien of dat werkt
            $review = DB::table('reviews')->insert([
                'sitting_request_id' => $validated['sitting_request_id'],
                'user_id' => auth()->id(),
                'sitter_profile_id' => $sittingRequest->sitter_profile_id,
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Review inserted via DB:', ['success' => $review]);

            return redirect()->back()->with('success', 'Review succesvol geplaatst!');

        } catch (\Exception $e) {
            Log::error('Error bij aanmaken review:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Er ging iets mis bij het plaatsen van de review. Details: ' . $e->getMessage()
            ])->withInput();
        }
    }
}
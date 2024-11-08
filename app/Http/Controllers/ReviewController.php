<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

            $review = Review::create([
                'sitting_request_id' => $validated['sitting_request_id'],
                'user_id' => auth()->id(),
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text']
            ]);

            Log::info('Review succesvol aangemaakt:', $review->toArray());

            return redirect()->back()->with('success', 'Review succesvol geplaatst!');

        } catch (\Exception $e) {
            Log::error('Error bij aanmaken review:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Er ging iets mis bij het plaatsen van de review.'
            ])->withInput();
        }
    }
}
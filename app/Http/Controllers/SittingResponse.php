<?php

namespace App\Http\Controllers;

use App\Models\SittingResponse;
use Illuminate\Http\Request;

class SittingResponseController extends Controller
{
    public function store(Request $request, $profileId)
    {
        // Valideer de input
        $validated = $request->validate([
            'bericht' => 'required|string|min:10',
        ]);

        // Maak nieuwe sitting response aan
        $sittingResponse = SittingResponse::create([
            'sitter_profile_id' => $profileId,
            'user_id' => auth()->id(),
            'bericht' => $validated['bericht'],
            'status' => 'pending' // pending, accepted, rejected
        ]);

        return redirect()->back()->with('success', 'Je bericht is verzonden!');
    }
}
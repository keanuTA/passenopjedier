<?php

namespace App\Http\Controllers;

use App\Models\PetProfile;
use App\Models\SitterProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PetProfileController extends Controller
{
    public function index()
    {
        return Inertia::render('PetProfiles/Index', [
            'petProfiles' => auth()->user()->petProfiles
        ]);
    }

    public function create()
    {
        return Inertia::render('PetProfiles/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'when_needed' => 'required|date',
            'duration' => 'required|integer',
            'hourly_rate' => 'required|numeric',
            'important_info' => 'nullable|string'
        ]);

        auth()->user()->petProfiles()->create($validated);

        return redirect()->route('pet-profiles.index')
            ->with('message', 'Huisdierprofiel succesvol aangemaakt!');
    }
}
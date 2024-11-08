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
            'important_info' => 'required|string'
        ]);

        // Zorg ervoor dat de datum correct wordt opgeslagen
        $validated['when_needed'] = date('Y-m-d H:i:s', strtotime($validated['when_needed']));

        auth()->user()->petProfiles()->create($validated);

        return redirect()->route('pet-profiles.index')
            ->with('message', 'Huisdierprofiel succesvol aangemaakt!');
    }

    public function show(PetProfile $petProfile)
    {
        return Inertia::render('PetProfiles/Show', [
            'petProfile' => $petProfile
        ]);
    }

    public function edit(PetProfile $petProfile)
    {
        return Inertia::render('PetProfiles/Edit', [
            'petProfile' => $petProfile
        ]);
    }

    public function update(Request $request, PetProfile $petProfile)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'when_needed' => 'required|date',
            'duration' => 'required|integer',
            'hourly_rate' => 'required|numeric',
            'important_info' => 'required|string'
        ]);

        $validated['when_needed'] = date('Y-m-d H:i:s', strtotime($validated['when_needed']));
        
        $petProfile->update($validated);

        return redirect()->route('pet-profiles.index')
            ->with('message', 'Huisdierprofiel succesvol bijgewerkt!');
    }

    public function destroy(PetProfile $petProfile)
    {
        $petProfile->delete();

        return redirect()->route('pet-profiles.index')
            ->with('message', 'Huisdierprofiel succesvol verwijderd!');
    }
}
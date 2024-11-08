<?php

namespace App\Http\Controllers;

use App\Models\SittingRequest;
use App\Models\SitterProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SittingRequestController extends Controller
{
    /**
     * Store een nieuw oppasverzoek
     */
    public function store(Request $request)
{
    // Debug logging
    \Log::info('Ontvangen oppasverzoek data:', [
        'request_data' => $request->all(),
        'user_id' => auth()->id()
    ]);

    try {
        // Valideer de input
        $validated = $request->validate([
            'start_datum' => 'required|date|after:now',
            'eind_datum' => 'required|date|after:start_datum',
            'extra_informatie' => 'required|string|min:10',
            'uurtarief' => 'required|numeric|min:0',
            'sitter_profile_id' => 'required|exists:sitter_profiles,id'
        ]);

        // Cast uurtarief naar float voor de database
        $validated['uurtarief'] = (float) $validated['uurtarief'];

        // Check of gebruiker een huisdier profiel heeft
        $petProfile = auth()->user()->petProfiles()->first();
        
        \Log::info('Pet profile check:', [
            'user_id' => auth()->id(),
            'pet_profile' => $petProfile
        ]);

        if (!$petProfile) {
            throw new \Exception('Je moet eerst een huisdier profiel aanmaken.');
        }

        // Debug logging voor de create data
        \Log::info('Creating sitting request with:', [
            'user_id' => auth()->id(),
            'pet_profile_id' => $petProfile->id,
            'sitter_profile_id' => $validated['sitter_profile_id'], // Controleer of deze waarde correct is
            'start_datum' => $validated['start_datum'],
            'eind_datum' => $validated['eind_datum'],
            'uurtarief' => $validated['uurtarief'],
            'extra_informatie' => $validated['extra_informatie']
        ]);

        // Maak het sitting request aan met alle vereiste velden
        $sittingRequest = SittingRequest::create([
            'user_id' => auth()->id(),
            'pet_profile_id' => $petProfile->id,
            'sitter_profile_id' => $validated['sitter_profile_id'], // Zorg dat deze waarde wordt meegegeven
            'start_datum' => $validated['start_datum'],
            'eind_datum' => $validated['eind_datum'],
            'uurtarief' => $validated['uurtarief'],
            'extra_informatie' => $validated['extra_informatie'],
            'status' => 'open'
        ]);

        // Debug logging
        \Log::info('Oppasverzoek aangemaakt:', $sittingRequest->toArray());

        return redirect()
    ->route('sitting-requests.my-requests')
    ->with('success', 'Je oppasverzoek is verstuurd!');

    } catch (\Exception $e) {
        \Log::error('Fout bij aanmaken oppasverzoek:', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id(),
            'stack_trace' => $e->getTraceAsString()
        ]);

        return redirect()->back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}

public function receivedRequests()
{
    try {
        $user = auth()->user();
        $requests = collect([]); // Lege collectie als standaard
        $hasSitterProfile = false;
        
        if ($user->sitterProfile) {
            $hasSitterProfile = true;
            $requests = SittingRequest::with(['petProfile.user', 'user'])
                ->where('sitter_profile_id', $user->sitterProfile->id)
                ->latest()
                ->get();
        }

        return Inertia::render('SittingRequests/Received', [
            'requests' => $requests,
            'hasSitterProfile' => $hasSitterProfile
        ]);
    } catch (\Exception $e) {
        \Log::error('Error loading received requests:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return redirect()->back()
            ->withErrors(['error' => 'Er ging iets mis bij het laden van de verzoeken.']);
    }
}

public function myRequests()
    {
        $user = auth()->user();
        
        $requests = SittingRequest::with(['petProfile.user', 'sitterProfile.user'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return Inertia::render('SittingRequests/Index', [
            'requests' => $requests,
            'isSitter' => false
        ]);
    }   

        /**
         * Toon alle oppasverzoeken voor een oppas
         */
        public function index()
    {
    try {
        $user = auth()->user();
        
        // Debug logging
        \Log::info('Loading sitting requests for user:', [
            'user_id' => $user->id,
            'is_sitter' => $user->sitterProfile !== null
        ]);

        // Als de gebruiker een oppas is, toon verzoeken gericht aan hun profiel
        if ($user->sitterProfile) {
            $requests = SittingRequest::with(['petProfile', 'user'])
                ->where('sitter_profile_id', $user->sitterProfile->id)
                ->where('status', 'open')
                ->latest()
                ->get();
        } else {
            // Anders toon de verzoeken die de gebruiker zelf heeft gemaakt
            $requests = SittingRequest::with(['petProfile', 'user'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        // Debug logging
        \Log::info('Found requests:', [
            'count' => $requests->count(),
            'requests' => $requests->toArray()
        ]);

        return Inertia::render('SittingRequests/Index', [
            'requests' => $requests,
            'isSitter' => $user->sitterProfile !== null
        ]);
    } catch (\Exception $e) {
        \Log::error('Error loading sitting requests:', [
            'error' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString()
        ]);

        return redirect()->back()
            ->withErrors(['error' => 'Er ging iets mis bij het laden van de oppasverzoeken.']);
    }
}

    /**
     * Update de status van een oppasverzoek
     */
    public function update(Request $request, SittingRequest $sittingRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        $sittingRequest->update([
            'status' => $validated['status']
        ]);

        return redirect()->back()->with('success', 
            $validated['status'] === 'accepted' 
                ? 'Oppasverzoek geaccepteerd!' 
                : 'Oppasverzoek afgewezen.'
        );
    }
}
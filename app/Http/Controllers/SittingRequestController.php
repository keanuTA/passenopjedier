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
    \Log::info('Ontvangen oppasverzoek data:', $request->all());

    try {
        // Uitgebreide validatie
        $validated = $request->validate([
            'start_datum' => 'required|date|after:now',
            'eind_datum' => 'required|date|after:start_datum',
            'extra_informatie' => 'required|string|min:10',
            'uurtarief' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/' // Controleert op maximaal 2 decimalen
            ],
            'sitter_profile_id' => 'required|exists:sitter_profiles,id'
        ], [
            'start_datum.after' => 'De startdatum moet in de toekomst liggen',
            'eind_datum.after' => 'De einddatum moet na de startdatum liggen',
            'extra_informatie.min' => 'Geef minimaal 10 karakters extra informatie',
            'uurtarief.regex' => 'Ongeldig uurtarief formaat'
        ]);

        // Controleer of het uurtarief overeenkomt met het profiel
        $sitterProfile = SitterProfile::findOrFail($validated['sitter_profile_id']);
        if ((float)$validated['uurtarief'] !== (float)$sitterProfile->uurtarief) {
            throw new \Exception('Het uurtarief komt niet overeen met het profiel');
        }

        // Haal het huisdier profiel op
        $petProfile = auth()->user()->petProfiles()->first();
        if (!$petProfile) {
            throw new \Exception('Je moet eerst een huisdier profiel aanmaken');
        }

        // Maak het sitting request aan
        $sittingRequest = SittingRequest::create([
            'user_id' => auth()->id(),
            'pet_profile_id' => $petProfile->id,
            'sitter_profile_id' => $validated['sitter_profile_id'],
            'start_datum' => $validated['start_datum'],
            'eind_datum' => $validated['eind_datum'],
            'uurtarief' => (float)$validated['uurtarief'],
            'extra_informatie' => $validated['extra_informatie'],
            'status' => 'open'
        ]);

        \Log::info('Oppasverzoek succesvol aangemaakt:', $sittingRequest->toArray());

        return redirect()
            ->route('sitting-requests.my-requests')
            ->with('success', 'Je oppasverzoek is verstuurd!');

    } catch (\Exception $e) {
        \Log::error('Fout bij aanmaken oppasverzoek:', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id()
        ]);

        return redirect()->back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}

 public function show(SittingRequest $sittingRequest)
 {
     try {
         // Laad alle benodigde relaties
         $sittingRequest->load([
             'petProfile.user',
             'sitterProfile.user',
             'user'
         ]);

         // Check of de ingelogde gebruiker toegang heeft tot dit verzoek
         if ($sittingRequest->user_id !== auth()->id() && 
             $sittingRequest->sitterProfile->user_id !== auth()->id()) {
             return redirect()->back()
                 ->withErrors(['error' => 'Je hebt geen toegang tot dit oppasverzoek.']);
         }

         // Bepaal of de huidige gebruiker de oppas is
         $isSitter = $sittingRequest->sitterProfile->user_id === auth()->id();

         return Inertia::render('SittingRequests/Show', [
             'request' => $sittingRequest,
             'isSitter' => $isSitter
         ]);
     } catch (\Exception $e) {
         \Log::error('Error showing sitting request:', [
             'error' => $e->getMessage(),
             'trace' => $e->getTraceAsString()
         ]);

         return redirect()->back()
             ->withErrors(['error' => 'Er ging iets mis bij het laden van het oppasverzoek.']);
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
        'status' => 'required|in:accepted,rejected,completed'
    ]);

    $sittingRequest->update([
        'status' => $validated['status']
    ]);

    // Als de status 'completed' is, redirect naar review pagina
    if ($validated['status'] === 'completed') {
        return redirect()->route('reviews.create', [
            'sitting_request_id' => $sittingRequest->id
        ])->with('success', 'Oppasverzoek afgerond! Laat een review achter.');
    }

    return redirect()->back()->with('success', 
        $validated['status'] === 'accepted' 
            ? 'Oppasverzoek geaccepteerd!' 
            : 'Oppasverzoek afgewezen.'
    );
}
}
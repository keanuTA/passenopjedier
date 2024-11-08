<?php

namespace App\Http\Controllers;

use App\Models\SitterProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SitterProfileController extends Controller
{
    public function index(Request $request)
{
    try {
        // Log binnenkomende request
        Log::info('Filter request ontvangen:', [
            'alle_parameters' => $request->all(),
            'huisdier_type' => $request->huisdier_type,
            'max_uurtarief' => $request->max_uurtarief
        ]);

        // Basisquery met debug logging
        $query = SitterProfile::with('user')->where('is_beschikbaar', true);

        // Filter op huisdier type met logging
        if ($request->filled('huisdier_type')) {
            Log::info('Huisdier type filter toegepast:', [
                'type' => $request->huisdier_type
            ]);

            // Aangepaste JSON query voor het controleren van array waarden
            $query->whereRaw(
                "JSON_CONTAINS(JSON_UNQUOTE(huisdier_voorkeuren), ?)", 
                ['"' . $request->huisdier_type . '"']
            );
            
            Log::info('Query na huisdier filter:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
        }

        // Filter op maximum uurtarief met logging
        if ($request->filled('max_uurtarief')) {
            $maxTarief = (float)$request->max_uurtarief;
            Log::info('Uurtarief filter toegepast:', [
                'max_tarief' => $maxTarief
            ]);

            $query->where('uurtarief', '<=', $maxTarief);
        }

        // Voer de query uit en log de resultaten
        $profiles = $query->paginate(12)
            ->through(function ($profile) {
                return $this->formatProfile($profile);
            });

        Log::info('Query resultaten:', [
            'aantal_resultaten' => $profiles->total(),
            'huidige_pagina' => $profiles->currentPage(),
            'resultaten_per_pagina' => $profiles->perPage()
        ]);

        return Inertia::render('SitterProfiles/Index', [
            'profiles' => $profiles,
            'filters' => $request->only(['huisdier_type', 'max_uurtarief']),
            'can' => [
                'create_profile' => auth()->check() && !auth()->user()->sitterProfile()->exists()
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Filter error:', [
            'message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        
        return Inertia::render('SitterProfiles/Index', [
            'profiles' => [],
            'error' => 'Er is een fout opgetreden bij het filteren van de profielen.'
        ]);
    }
}

    public function show(SitterProfile $profile)
    {
        try {
            Log::info('Show profiel opgevraagd:', [
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id
            ]);

            $profile->load('user', 'reviews.user');
            return Inertia::render('SitterProfiles/Show', [
                'profile' => $this->formatProfile($profile)
            ]);
        } catch (\Exception $e) {
            Log::error('Fout bij tonen profiel:', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het laden van het profiel.'
            ]);
        }
    }

    public function create()
    {
        try {
            Log::info('Create profiel pagina opgevraagd door gebruiker:', [
                'user_id' => auth()->id()
            ]);

            return Inertia::render('SitterProfiles/Create');
        } catch (\Exception $e) {
            Log::error('Fout bij laden create pagina:', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->withErrors([
                'error' => 'Er is een fout opgetreden.'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Nieuw profiel aanmaken gestart:', [
                'user_id' => auth()->id(),
                'request_data' => $request->except(['profielfoto', 'video_intro'])
            ]);

            $validated = $request->validate([
                'ervaring' => 'required|string|min:10',
                'over_mij' => 'required|string|min:10',
                'huisdier_voorkeuren' => 'required|array|min:1',
                'uurtarief' => 'required|numeric|min:0',
                'profielfoto' => 'nullable|image|max:5120',
                'video_intro' => 'nullable|mimes:mp4,mov,avi|max:51200',
                'is_beschikbaar' => 'boolean'
            ]);

            DB::beginTransaction();

            // Verwerk bestanden
            if ($request->hasFile('profielfoto')) {
                $validated['profielfoto_pad'] = $request->file('profielfoto')
                    ->store('profielfoto', 'sitter_uploads');
            }

            if ($request->hasFile('video_intro')) {
                $validated['video_intro_pad'] = $request->file('video_intro')
                    ->store('video', 'sitter_uploads');
            }

            // JSON encode de huisdier voorkeuren
            $validated['huisdier_voorkeuren'] = json_encode($validated['huisdier_voorkeuren']);

            // Maak het profiel aan
            $profile = auth()->user()->sitterProfile()->create($validated);

            DB::commit();

            Log::info('Nieuw profiel succesvol aangemaakt:', [
                'profile_id' => $profile->id
            ]);

            return redirect()->route('sitter-profiles.show', $profile)
                ->with('success', 'Je oppasprofiel is aangemaakt!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Fout bij aanmaken profiel:', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het aanmaken van je profiel.'
            ])->withInput();
        }
    }

    public function edit(SitterProfile $profile)
    {
        try {
            Log::info('Edit profiel pagina opgevraagd:', [
                'profile_id' => $profile->id,
                'user_id' => auth()->id()
            ]);

            if ($profile->user_id !== auth()->id()) {
                Log::warning('Ongeautoriseerde toegang tot edit profiel:', [
                    'profile_id' => $profile->id,
                    'requested_by' => auth()->id()
                ]);
                abort(403);
            }

            return Inertia::render('SitterProfiles/Edit', [
                'sitterProfile' => $this->formatProfile($profile),
                'huisdier_types' => [
                    'Hond',
                    'Kat',
                    'Vogel',
                    'Knaagdier',
                    'Vis',
                    'Anders'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Fout bij laden edit pagina:', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het laden van de bewerkpagina.'
            ]);
        }
    }

    public function update(Request $request, SitterProfile $profile)
    {
        try {
            Log::info('Update profiel gestart:', [
                'profile_id' => $profile->id,
                'request_data' => $request->except(['profielfoto', 'video_intro'])
            ]);

            if ($profile->user_id !== auth()->id()) {
                Log::warning('Ongeautoriseerde poging tot update profiel:', [
                    'profile_id' => $profile->id,
                    'requested_by' => auth()->id()
                ]);
                abort(403);
            }

            $validated = $request->validate([
                'ervaring' => 'required|string|min:10',
                'over_mij' => 'required|string|min:10',
                'huisdier_voorkeuren' => 'required|array|min:1',
                'uurtarief' => 'required|numeric|min:0',
                'profielfoto' => 'nullable|image|max:5120',
                'video_intro' => 'nullable|mimes:mp4,mov,avi|max:51200',
                'is_beschikbaar' => 'boolean'
            ]);

            DB::beginTransaction();

            // Verwerk nieuwe profielfoto
            if ($request->hasFile('profielfoto')) {
                if ($profile->profielfoto_pad) {
                    Storage::disk('sitter_uploads')->delete($profile->profielfoto_pad);
                }
                $validated['profielfoto_pad'] = $request->file('profielfoto')
                    ->store('profielfoto', 'sitter_uploads');
            }

            // Verwerk nieuwe video
            if ($request->hasFile('video_intro')) {
                if ($profile->video_intro_pad) {
                    Storage::disk('sitter_uploads')->delete($profile->video_intro_pad);
                }
                $validated['video_intro_pad'] = $request->file('video_intro')
                    ->store('video', 'sitter_uploads');
            }

            // JSON encode de huisdier voorkeuren
            $validated['huisdier_voorkeuren'] = json_encode($validated['huisdier_voorkeuren']);

            // Update het profiel
            $profile->update($validated);

            DB::commit();

            Log::info('Profiel succesvol bijgewerkt:', [
                'profile_id' => $profile->id
            ]);

            return redirect()->route('sitter-profiles.show', $profile)
                ->with('success', 'Je oppasprofiel is bijgewerkt!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Fout bij bijwerken profiel:', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het bijwerken van je profiel.'
            ])->withInput();
        }
    }

    public function destroy(SitterProfile $profile)
    {
        try {
            Log::info('Verwijderen profiel gestart:', [
                'profile_id' => $profile->id,
                'user_id' => auth()->id()
            ]);

            if ($profile->user_id !== auth()->id()) {
                Log::warning('Ongeautoriseerde poging tot verwijderen profiel:', [
                    'profile_id' => $profile->id,
                    'requested_by' => auth()->id()
                ]);
                abort(403);
            }

            DB::beginTransaction();

            // Verwijder bestanden
            if ($profile->profielfoto_pad) {
                Storage::disk('sitter_uploads')->delete($profile->profielfoto_pad);
            }
            if ($profile->video_intro_pad) {
                Storage::disk('sitter_uploads')->delete($profile->video_intro_pad);
            }

            // Verwijder het profiel
            $profile->delete();

            DB::commit();

            Log::info('Profiel succesvol verwijderd:', [
                'profile_id' => $profile->id
            ]);

            return redirect()->route('sitter-profiles.index')
                ->with('success', 'Je oppasprofiel is verwijderd.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Fout bij verwijderen profiel:', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het verwijderen van je profiel.'
            ]);
        }
    }

    private function formatProfile($profile)
    {
        try {
            // Foto URL toevoegen
            if ($profile->profielfoto_pad) {
                $profile->profielfoto_url = Storage::disk('sitter_uploads')->url($profile->profielfoto_pad);
            }
            
            // JSON voorkeuren decoderen - aangepaste logica voor dubbele encoding
            if (is_string($profile->huisdier_voorkeuren)) {
                // Verwijder eventuele extra quotes en escape karakters
                $cleaned = str_replace('\\', '', $profile->huisdier_voorkeuren);
                $cleaned = trim($cleaned, '"');
                $profile->huisdier_voorkeuren = json_decode($cleaned, true) ?? [];
                
                Log::info('Huisdier voorkeuren gedecodeerd:', [
                    'profile_id' => $profile->id,
                    'origineel' => $profile->huisdier_voorkeuren,
                    'cleaned' => $cleaned,
                    'gedecodeerd' => $profile->huisdier_voorkeuren
                ]);
            }
            
            // Zorg dat uurtarief altijd als float wordt teruggegeven
            $profile->uurtarief = (float)$profile->uurtarief;
            
            return $profile;
        } catch (\Exception $e) {
            Log::error('Fout bij formatteren profiel:', [
                'profile_id' => $profile->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
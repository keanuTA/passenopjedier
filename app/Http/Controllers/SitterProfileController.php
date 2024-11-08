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

                // Gebruik JSON_CONTAINS met de correcte JSON syntax
                $query->whereRaw('JSON_CONTAINS(huisdier_voorkeuren, ?)', ['"' . $request->huisdier_type . '"']);

                Log::info('Query na huisdier filter:', [
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings()
                ]);
            }

            // Filter op maximum uurtarief
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
                'resultaten_per_pagina' => $profiles->perPage(),
                'eerste_resultaat' => $profiles->first()
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
                'user_id' => $profile->user_id,
                'raw_profile' => $profile->toArray()  // Debug log toegevoegd
            ]);

            // Eager load relaties
            $profile->load(['user', 'reviews.user']);

            // Format en check het profiel
            $formattedProfile = $this->formatProfile($profile);

            Log::info('Geformatteerd profiel:', [
                'formatted_id' => $formattedProfile['id'],
                'formatted_data' => $formattedProfile
            ]);

            return Inertia::render('SitterProfiles/Show', [
                'profile' => $formattedProfile,
                'can' => [
                    'edit' => auth()->check() && auth()->id() === $profile->user_id,
                    'contact' => auth()->check() && auth()->id() !== $profile->user_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Fout bij laden oppasprofiel:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('sitter-profiles.index')
                ->with('error', 'Er is een fout opgetreden bij het laden van het profiel.');
        }
    }

    private function formatProfile($profile)
    {
        try {
            Log::info('Start formatteren profiel:', [
                'profile_id' => $profile->id
            ]);

            // Check of het profiel bestaat
            if (!$profile) {
                throw new \Exception('Geen profiel gevonden om te formatteren');
            }

            // Foto URL toevoegen
            if ($profile->profielfoto_pad) {
                $profile->profielfoto_url = Storage::disk('sitter_uploads')->url($profile->profielfoto_pad);
            }

            // Zorg dat huisdier_voorkeuren altijd een array is
            if (is_string($profile->huisdier_voorkeuren)) {
                $profile->huisdier_voorkeuren = json_decode($profile->huisdier_voorkeuren, true) ?? [];
            }

            // Zorg dat uurtarief een float is
            $profile->uurtarief = (float)$profile->uurtarief;

            Log::info('Profiel geformatteerd:', [
                'profile_id' => $profile->id,
                'voorkeuren' => $profile->huisdier_voorkeuren,
                'uurtarief' => $profile->uurtarief
            ]);

            return $profile->toArray();

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
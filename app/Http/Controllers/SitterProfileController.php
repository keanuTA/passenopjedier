<?php

namespace App\Http\Controllers;

use App\Models\SitterProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class SitterProfileController extends Controller
{

    use AuthorizesRequests;  // Voeg deze trait toe

    public function index(Request $request)
    {
        $query = SitterProfile::with('user');
        
        // Controleer eerst of de kolommen bestaan
        if (Schema::hasColumn('sitter_profiles', 'is_beschikbaar')) {
            $query->where('is_beschikbaar', true);
        }

        // Filter op huisdier type met correcte JSON query
        if ($request->filled('huisdier_type')) {
            try {
                $query->whereJsonContains('huisdier_voorkeuren', $request->huisdier_type);
            } catch (\Exception $e) {
                // Log de error maar laat de query doorgaan zonder deze filter
                \Log::error('Error filtering on huisdier_type: ' . $e->getMessage());
            }
        }

        // Filter op maximum uurtarief
        if ($request->filled('max_uurtarief') && Schema::hasColumn('sitter_profiles', 'uurtarief')) {
            $query->where('uurtarief', '<=', (float)$request->max_uurtarief);
        }

        // Voeg URL's toe voor media bestanden
        $profiles = $query->paginate(12)->through(function ($profile) {
            if ($profile->profielfoto_pad) {
                $profile->profielfoto_url = Storage::disk('sitter_uploads')->url($profile->profielfoto_pad);
            }
            return $profile;
        });

        return Inertia::render('SitterProfiles/Index', [
            'profiles' => $profiles,
            'filters' => $request->only(['huisdier_type', 'max_uurtarief']),
            'can' => [
                'create_profile' => auth()->check() && !auth()->user()->sitterProfile()->exists()
            ]
        ]);
    }

    public function create()
    {
        // Check of de gebruiker al een profiel heeft
        if (auth()->user()->sitterProfile()->exists()) {
            return redirect()->route('sitter-profiles.index')
                ->with('error', 'Je hebt al een oppasprofiel!');
        }
    
        return Inertia::render('SitterProfiles/Create', [
            'huisdier_types' => [
                'Hond',
                'Kat',
                'Konijn',
                'Vogel',
                'Vis',
                'Hamster',
                'Cavia',
                'Reptiel',
                'Overig'
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Debug: Log de binnenkomende request data
        \Log::info('Incoming request data:', $request->all());

        try {
            $validated = $request->validate([
                'ervaring' => 'required|string|min:50',
                'over_mij' => 'required|string|min:100',
                'huisdier_voorkeuren' => 'required|array',
                'uurtarief' => 'required|numeric|min:5|max:100',
                'profielfoto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'video_intro' => 'nullable|mimetypes:video/mp4,video/quicktime|max:20480',
            ]);

            // Debug: Log dat we de validatie zijn gepasseerd
            \Log::info('Validation passed');

            // Handle profielfoto upload
            if ($request->hasFile('profielfoto')) {
                $profielfoto = $request->file('profielfoto');
                $profielfotoNaam = 'profile_' . Str::uuid() . '.' . $profielfoto->extension();
                $profielfotoPad = $profielfoto->storeAs('photos', $profielfotoNaam, 'sitter_uploads');
                $validated['profielfoto_pad'] = $profielfotoPad;
                
                // Debug: Log foto upload
                \Log::info('Photo uploaded:', ['path' => $profielfotoPad]);
            }

            // Handle video upload
            if ($request->hasFile('video_intro')) {
                $video = $request->file('video_intro');
                $videoNaam = 'video_' . Str::uuid() . '.' . $video->extension();
                $videoPad = $video->storeAs('videos', $videoNaam, 'sitter_uploads');
                $validated['video_intro_pad'] = $videoPad;
                
                // Debug: Log video upload
                \Log::info('Video uploaded:', ['path' => $videoPad]);
            }

            // Zorg ervoor dat huisdier_voorkeuren correct als JSON wordt opgeslagen
            if (isset($validated['huisdier_voorkeuren'])) {
                $validated['huisdier_voorkeuren'] = json_encode($validated['huisdier_voorkeuren']);
            }

            // Debug: Log de gevalideerde data voor create
            \Log::info('Validated data before create:', $validated);

            // Check of de gebruiker bestaat en ingelogd is
            if (!auth()->check()) {
                throw new \Exception('Gebruiker is niet ingelogd');
            }

            $profile = auth()->user()->sitterProfile()->create($validated);

            // Debug: Log het aangemaakte profiel
            \Log::info('Profile created:', $profile->toArray());

            return redirect()
                ->route('sitter-profiles.show', $profile)
                ->with('success', 'Je oppasprofiel is aangemaakt!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validatie errors
            \Log::error('Validation error:', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            // Log andere errors
            \Log::error('Error creating profile:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Cleanup uploaded files if something goes wrong
            if (isset($profielfotoPad)) {
                Storage::disk('sitter_uploads')->delete($profielfotoPad);
            }
            if (isset($videoPad)) {
                Storage::disk('sitter_uploads')->delete($videoPad);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'Er is iets misgegaan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(SitterProfile $sitterProfile)
    {
        try {
            $sitterProfile->load(['user', 'reviews.user']);

            // Calculate average rating
            $averageRating = $sitterProfile->reviews->avg('rating') ?? 0;

            // Check if auth user can edit this profile
            $canEdit = auth()->check() && auth()->id() === $sitterProfile->user_id;

            return Inertia::render('SitterProfiles/Show', [
                'profile' => array_merge($sitterProfile->toArray(), [
                    'uurtarief' => number_format($sitterProfile->uurtarief, 2, '.', ''),
                    'id' => $sitterProfile->id,
                    'average_rating' => round($averageRating, 1),
                    'can_edit' => $canEdit
                ])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading profile:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->route('sitter-profiles.index')
                ->withErrors(['error' => 'Profiel kon niet worden geladen.']);
        }
    }

    public function edit(SitterProfile $sitterProfile)
    {
        // Controleer of de gebruiker dit profiel mag bewerken
        $this->authorize('edit', $sitterProfile);

        // Debug log om te zien wat we hebben
        \Log::info('SitterProfile data:', $sitterProfile->toArray());

        return Inertia::render('SitterProfiles/Edit', [
            'sitterProfile' => [
                'id' => $sitterProfile->id,
                'ervaring' => $sitterProfile->ervaring,
                'over_mij' => $sitterProfile->over_mij,
                'huisdier_voorkeuren' => json_decode($sitterProfile->huisdier_voorkeuren),
                'uurtarief' => number_format($sitterProfile->uurtarief, 2, '.', ''),
                'profielfoto_pad' => $sitterProfile->profielfoto_pad,
                'video_intro_pad' => $sitterProfile->video_intro_pad,
                'is_beschikbaar' => $sitterProfile->is_beschikbaar,
                'user_id' => $sitterProfile->user_id
            ],
            'huisdier_types' => [
                'Hond',
                'Kat',
                'Konijn',
                'Vogel',
                'Vis',
                'Hamster',
                'Cavia',
                'Reptiel',
                'Overig'
            ]
        ]);
    }

    public function update(Request $request, SitterProfile $sitterProfile)
    {
        // Voeg ook hier de autorisatie check toe
        $this->authorize('update', $sitterProfile);

        try {
            $validated = $request->validate([
                'ervaring' => 'required|string|min:50',
                'over_mij' => 'required|string|min:100',
                'huisdier_voorkeuren' => 'required|array',
                'uurtarief' => 'required|numeric|min:5|max:100',
                'profielfoto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'video_intro' => 'nullable|mimetypes:video/mp4,video/quicktime|max:20480',
                'is_beschikbaar' => 'boolean'
            ]);

            // Handle profielfoto update
            if ($request->hasFile('profielfoto')) {
                if ($sitterProfile->profielfoto_pad) {
                    Storage::disk('sitter_uploads')->delete($sitterProfile->profielfoto_pad);
                }
                
                $profielfoto = $request->file('profielfoto');
                $profielfotoNaam = 'profile_' . Str::uuid() . '.' . $profielfoto->extension();
                $profielfotoPad = $profielfoto->storeAs('photos', $profielfotoNaam, 'sitter_uploads');
                $validated['profielfoto_pad'] = $profielfotoPad;
            }

            // Handle video update
            if ($request->hasFile('video_intro')) {
                if ($sitterProfile->video_intro_pad) {
                    Storage::disk('sitter_uploads')->delete($sitterProfile->video_intro_pad);
                }
                
                $video = $request->file('video_intro');
                $videoNaam = 'video_' . Str::uuid() . '.' . $video->extension();
                $videoPad = $video->storeAs('videos', $videoNaam, 'sitter_uploads');
                $validated['video_intro_pad'] = $videoPad;
            }

            // Zorg ervoor dat huisdier_voorkeuren correct als JSON wordt opgeslagen
            if (isset($validated['huisdier_voorkeuren'])) {
                $validated['huisdier_voorkeuren'] = json_encode($validated['huisdier_voorkeuren']);
            }

            $sitterProfile->update($validated);

            return redirect()
                ->route('sitter-profiles.show', $sitterProfile)
                ->with('success', 'Je oppasprofiel is bijgewerkt!');

        } catch (\Exception $e) {
            \Log::error('Error updating profile:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Cleanup nieuwe uploads als er iets misgaat
            if (isset($profielfotoPad)) {
                Storage::disk('sitter_uploads')->delete($profielfotoPad);
            }
            if (isset($videoPad)) {
                Storage::disk('sitter_uploads')->delete($videoPad);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'Er is iets misgegaan bij het bijwerken van je profiel.'])
                ->withInput();
        }
    }
}
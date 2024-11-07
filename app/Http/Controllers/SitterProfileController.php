<?php

namespace App\Http\Controllers;

use App\Models\SitterProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SitterProfileController extends Controller
{
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
        $validated = $request->validate([
            'ervaring' => 'required|string|min:50',
            'over_mij' => 'required|string|min:100',
            'huisdier_voorkeuren' => 'required|array',
            'uurtarief' => 'required|numeric|min:5|max:100',
            'profielfoto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'video_intro' => 'nullable|mimetypes:video/mp4,video/quicktime|max:20480',
        ]);

        try {
            // Handle profielfoto upload
            if ($request->hasFile('profielfoto')) {
                $profielfoto = $request->file('profielfoto');
                $profielfotoNaam = 'profile_' . Str::uuid() . '.' . $profielfoto->extension();
                $profielfotoPad = $profielfoto->storeAs('photos', $profielfotoNaam, 'sitter_uploads');
                $validated['profielfoto_pad'] = $profielfotoPad;
            }

            // Handle video upload
            if ($request->hasFile('video_intro')) {
                $video = $request->file('video_intro');
                $videoNaam = 'video_' . Str::uuid() . '.' . $video->extension();
                $videoPad = $video->storeAs('videos', $videoNaam, 'sitter_uploads');
                $validated['video_intro_pad'] = $videoPad;
            }

            // Zorg ervoor dat huisdier_voorkeuren correct als JSON wordt opgeslagen
            if (isset($validated['huisdier_voorkeuren'])) {
                $validated['huisdier_voorkeuren'] = json_encode($validated['huisdier_voorkeuren']);
            }

            $profile = auth()->user()->sitterProfile()->create($validated);

            return redirect()
                ->route('sitter-profiles.show', $profile)
                ->with('success', 'Je oppasprofiel is aangemaakt!');

        } catch (\Exception $e) {
            // Cleanup uploaded files if something goes wrong
            if (isset($profielfotoPad)) {
                Storage::disk('sitter_uploads')->delete($profielfotoPad);
            }
            if (isset($videoPad)) {
                Storage::disk('sitter_uploads')->delete($videoPad);
            }

            throw $e;
        }
    }

    public function show(SitterProfile $profile)
    {
        $profile->load('user');
        
        // Add full URLs for the media files
        if ($profile->profielfoto_pad) {
            $profile->profielfoto_url = Storage::disk('sitter_uploads')->url($profile->profielfoto_pad);
        }
        if ($profile->video_intro_pad) {
            $profile->video_intro_url = Storage::disk('sitter_uploads')->url($profile->video_intro_pad);
        }

        return Inertia::render('SitterProfiles/Show', [
            'profile' => $profile
        ]);
    }

    public function edit(SitterProfile $profile)
    {
        return Inertia::render('SitterProfiles/Edit', [
            'profile' => $profile
        ]);
    }

    public function update(Request $request, SitterProfile $profile)
    {
        $validated = $request->validate([
            'ervaring' => 'required|string|min:50',
            'over_mij' => 'required|string|min:100',
            'huisdier_voorkeuren' => 'required|array',
            'uurtarief' => 'required|numeric|min:5|max:100',
            'profielfoto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video_intro' => 'nullable|mimetypes:video/mp4,video/quicktime|max:20480',
        ]);

        try {
            // Handle profielfoto update
            if ($request->hasFile('profielfoto')) {
                if ($profile->profielfoto_pad) {
                    Storage::disk('sitter_uploads')->delete($profile->profielfoto_pad);
                }
                
                $profielfoto = $request->file('profielfoto');
                $profielfotoNaam = 'profile_' . Str::uuid() . '.' . $profielfoto->extension();
                $profielfotoPad = $profielfoto->storeAs('photos', $profielfotoNaam, 'sitter_uploads');
                $validated['profielfoto_pad'] = $profielfotoPad;
            }

            // Handle video update
            if ($request->hasFile('video_intro')) {
                if ($profile->video_intro_pad) {
                    Storage::disk('sitter_uploads')->delete($profile->video_intro_pad);
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

            $profile->update($validated);

            return redirect()
                ->route('sitter-profiles.show', $profile)
                ->with('success', 'Je oppasprofiel is bijgewerkt!');

        } catch (\Exception $e) {
            // Cleanup nieuwe uploads als er iets misgaat
            if (isset($profielfotoPad)) {
                Storage::disk('sitter_uploads')->delete($profielfotoPad);
            }
            if (isset($videoPad)) {
                Storage::disk('sitter_uploads')->delete($videoPad);
            }

            throw $e;
        }
    }
}
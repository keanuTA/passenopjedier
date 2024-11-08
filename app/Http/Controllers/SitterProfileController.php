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

    public function show($id)
    {
        try {
            // Haal het profiel expliciet op
            $profile = SitterProfile::with(['user', 'reviews.user'])->findOrFail($id);

            // Log het ruwe profiel voor debugging
            Log::info('Raw profile data in show:', [
                'id' => $profile->id,
                'raw_uurtarief' => $profile->uurtarief,
                'raw_uurtarief_type' => gettype($profile->uurtarief),
                'user_id' => $profile->user_id,
                'all_attributes' => $profile->getAttributes()
            ]);

            $formattedProfile = $this->formatProfile($profile);

            // Log het geformatteerde profiel voor debugging
            Log::info('Formatted profile in show:', [
                'id' => $formattedProfile->id,
                'formatted_uurtarief' => $formattedProfile->uurtarief,
                'formatted_uurtarief_type' => gettype($formattedProfile->uurtarief)
            ]);

            return Inertia::render('SitterProfiles/Show', [
                'profile' => $formattedProfile
            ]);
        } catch (\Exception $e) {
            Log::error('Error in show method:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('sitter-profiles.index')->withErrors([
                'error' => 'Er is een fout opgetreden bij het laden van het profiel.'
            ]);
        }
    }

    public function create()
    {
        try {
            return Inertia::render('SitterProfiles/Create', [
                'huisdier_types' => [
                    'Hond', 'Kat', 'Vogel', 'Knaagdier', 'Vis', 'Anders'
                ]
            ]);
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

            if ($request->hasFile('profielfoto')) {
                $validated['profielfoto_pad'] = $request->file('profielfoto')
                    ->store('profielfoto', 'sitter_uploads');
            }

            if ($request->hasFile('video_intro')) {
                $validated['video_intro_pad'] = $request->file('video_intro')
                    ->store('video', 'sitter_uploads');
            }

            // Format het uurtarief voor opslag
            $validated['uurtarief'] = number_format(
                (float)str_replace(',', '.', $validated['uurtarief']),
                2,
                '.',
                ''
            );

            // JSON encode de huisdier voorkeuren
            $validated['huisdier_voorkeuren'] = json_encode($validated['huisdier_voorkeuren']);

            $profile = auth()->user()->sitterProfile()->create($validated);

            DB::commit();

            return redirect()->route('sitter-profiles.show', $profile)
                ->with('success', 'Je oppasprofiel is aangemaakt!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fout bij aanmaken profiel:', [
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
            if ($profile->user_id !== auth()->id()) {
                abort(403);
            }

            return Inertia::render('SitterProfiles/Edit', [
                'sitterProfile' => $this->formatProfile($profile),
                'huisdier_types' => [
                    'Hond', 'Kat', 'Vogel', 'Knaagdier', 'Vis', 'Anders'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Fout bij laden edit pagina:', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->withErrors([
                'error' => 'Er is een fout opgetreden.'
            ]);
        }
    }

    public function update(Request $request, SitterProfile $profile)
    {
        try {
            if ($profile->user_id !== auth()->id()) {
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

            if ($request->hasFile('profielfoto')) {
                if ($profile->profielfoto_pad) {
                    Storage::disk('sitter_uploads')->delete($profile->profielfoto_pad);
                }
                $validated['profielfoto_pad'] = $request->file('profielfoto')
                    ->store('profielfoto', 'sitter_uploads');
            }

            if ($request->hasFile('video_intro')) {
                if ($profile->video_intro_pad) {
                    Storage::disk('sitter_uploads')->delete($profile->video_intro_pad);
                }
                $validated['video_intro_pad'] = $request->file('video_intro')
                    ->store('video', 'sitter_uploads');
            }

            // Format het uurtarief voor update
            $validated['uurtarief'] = number_format(
                (float)str_replace(',', '.', $validated['uurtarief']),
                2,
                '.',
                ''
            );

            // JSON encode de huisdier voorkeuren
            $validated['huisdier_voorkeuren'] = json_encode($validated['huisdier_voorkeuren']);

            $profile->update($validated);

            DB::commit();

            return redirect()->route('sitter-profiles.show', $profile)
                ->with('success', 'Je oppasprofiel is bijgewerkt!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fout bij bijwerken profiel:', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het bijwerken van je profiel.'
            ])->withInput();
        }
    }

    public function destroy(SitterProfile $profile)
    {
        try {
            if ($profile->user_id !== auth()->id()) {
                abort(403);
            }

            DB::beginTransaction();

            if ($profile->profielfoto_pad) {
                Storage::disk('sitter_uploads')->delete($profile->profielfoto_pad);
            }
            if ($profile->video_intro_pad) {
                Storage::disk('sitter_uploads')->delete($profile->video_intro_pad);
            }

            $profile->delete();

            DB::commit();

            return redirect()->route('sitter-profiles.index')
                ->with('success', 'Je oppasprofiel is verwijderd.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fout bij verwijderen profiel:', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het verwijderen van je profiel.'
            ]);
        }
    }

    private function formatProfile($profile)
    {
        try {
            if (!$profile || !$profile->exists) {
                throw new \InvalidArgumentException('Invalid profile provided to formatProfile');
            }

            Log::info('formatProfile input:', [
                'id' => $profile->id,
                'input_uurtarief' => $profile->uurtarief,
                'input_type' => gettype($profile->uurtarief),
                'all_attributes' => $profile->getAttributes()
            ]);

            // Maak een kopie
            $formatted = clone $profile;

            // 1. Format uurtarief
            if (is_string($formatted->uurtarief)) {
                // Vervang eventuele komma's door punten
                $formatted->uurtarief = str_replace(',', '.', $formatted->uurtarief);
            }
            // Converteer naar float en rond af op 2 decimalen
            $formatted->uurtarief = is_null($formatted->uurtarief) 
                ? 0.00 
                : round((float)$formatted->uurtarief, 2);

            // 2. Format huisdier voorkeuren
            if (is_string($formatted->huisdier_voorkeuren)) {
                // Verwijder onnodige quotes en escape karakters
                $cleaned = trim($formatted->huisdier_voorkeuren, '"');
                $cleaned = str_replace('\\', '', $cleaned);
                $formatted->huisdier_voorkeuren = json_decode($cleaned, true) ?? [];
            }

            // 3. Voeg profielfoto URL toe
            if ($formatted->profielfoto_pad) {
                $formatted->profielfoto_url = Storage::disk('sitter_uploads')
                    ->url($formatted->profielfoto_pad);
            }

            Log::info('formatProfile output:', [
                'id' => $formatted->id,
                'output_uurtarief' => $formatted->uurtarief,
                'output_type' => gettype($formatted->uurtarief)
            ]);

            return $formatted;

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
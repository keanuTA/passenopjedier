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
            $query = SitterProfile::with('user')
                ->where('is_beschikbaar', true);

            // Filter op huisdier type met geoptimaliseerde JSON query
            if ($request->filled('huisdier_type')) {
                $query->where(function($q) use ($request) {
                    $q->whereRaw('JSON_CONTAINS(huisdier_voorkeuren, ?)', ['"' . $request->huisdier_type . '"'])
                      ->orWhereRaw('JSON_CONTAINS(huisdier_voorkeuren, ?)', ['"' . strtolower($request->huisdier_type) . '"'])
                      ->orWhereRaw('JSON_CONTAINS(huisdier_voorkeuren, ?)', ['"' . ucfirst($request->huisdier_type) . '"']);
                });
            }

            // Filter op maximum uurtarief
            if ($request->filled('max_uurtarief')) {
                $query->where('uurtarief', '<=', (float)$request->max_uurtarief);
            }

            // Performance optimalisatie met eager loading
            $profiles = $query->paginate(12)
                ->through(function ($profile) {
                    return $this->formatProfile($profile);
                });

            return Inertia::render('SitterProfiles/Index', [
                'profiles' => $profiles,
                'filters' => $request->only(['huisdier_type', 'max_uurtarief']),
                'can' => [
                    'create_profile' => auth()->check() && !auth()->user()->sitterProfile()->exists()
                ],
                'filterStats' => [
                    'totaalAantal' => SitterProfile::count(),
                    'gefilterdAantal' => $profiles->total()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Fout bij ophalen oppasprofielen: ' . $e->getMessage());
            return Inertia::render('SitterProfiles/Index', [
                'profiles' => [],
                'error' => 'Er is een fout opgetreden bij het ophalen van de profielen.'
            ]);
        }
    }

    private function formatProfile($profile)
    {
        // Foto URL toevoegen
        if ($profile->profielfoto_pad) {
            $profile->profielfoto_url = Storage::disk('sitter_uploads')->url($profile->profielfoto_pad);
        }
        
        // JSON voorkeuren decoderen
        if (is_string($profile->huisdier_voorkeuren)) {
            $profile->huisdier_voorkeuren = json_decode($profile->huisdier_voorkeuren, true) ?? [];
        }
        
        // Zorg dat uurtarief altijd als float wordt teruggegeven
        $profile->uurtarief = (float)$profile->uurtarief;
        
        return $profile;
    }
}
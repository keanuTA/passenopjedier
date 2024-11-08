<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixHuisdierVoorkeurenEncoding extends Migration
{
    public function up()
    {
        try {
            // Debug log start
            Log::info('Start correctie huisdier_voorkeuren encoding');

            // Haal alle profielen op
            $profiles = DB::table('sitter_profiles')->get();
            
            foreach ($profiles as $profile) {
                // Log originele waarde
                Log::info("Verwerken profiel {$profile->id}", [
                    'originele_waarde' => $profile->huisdier_voorkeuren
                ]);

                if ($profile->huisdier_voorkeuren) {
                    // Verwijder escape karakters en extra quotes
                    $cleaned = str_replace('\\', '', $profile->huisdier_voorkeuren);
                    $cleaned = trim($cleaned, '"');
                    
                    // Controleer of het een valide JSON array is
                    $decoded = json_decode($cleaned, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // Als het succesvol gedecodeerd is, update de database
                        DB::table('sitter_profiles')
                            ->where('id', $profile->id)
                            ->update(['huisdier_voorkeuren' => json_encode($decoded)]);
                        
                        // Log succesvolle update
                        Log::info("Profiel {$profile->id} bijgewerkt", [
                            'nieuwe_waarde' => json_encode($decoded)
                        ]);
                    } else {
                        // Log fout bij decoderen
                        Log::error("Fout bij decoderen profiel {$profile->id}", [
                            'json_error' => json_last_error_msg(),
                            'cleaned_value' => $cleaned
                        ]);
                    }
                }
            }

            Log::info('Correctie huisdier_voorkeuren encoding voltooid');

        } catch (\Exception $e) {
            Log::error('Fout tijdens migratie:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function down()
    {
        // Deze migratie kan niet ongedaan worden gemaakt
        // omdat we de originele staat niet kunnen herstellen
    }
}
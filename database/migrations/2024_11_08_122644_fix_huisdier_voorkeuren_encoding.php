<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        try {
            Log::info('Start migratie voor het fixen van huisdier_voorkeuren');
            
            // Log huidige waarden
            $current = DB::table('sitter_profiles')
                ->select('id', 'huisdier_voorkeuren')
                ->get();
            Log::info('Huidige waarden:', $current->toArray());

            foreach ($current as $profile) {
                if ($profile->huisdier_voorkeuren) {
                    try {
                        // Verwijder alle escape karakters en quotes
                        $cleaned = str_replace('\\', '', $profile->huisdier_voorkeuren);
                        $cleaned = trim($cleaned, '"');
                        
                        // Parse de array
                        $voorkeuren = json_decode($cleaned, true);
                        
                        if (is_array($voorkeuren)) {
                            // Direct opslaan als JSON array
                            DB::table('sitter_profiles')
                                ->where('id', $profile->id)
                                ->update([
                                    'huisdier_voorkeuren' => json_encode($voorkeuren, JSON_UNESCAPED_SLASHES)
                                ]);

                            Log::info("Profiel {$profile->id} geüpdatet", [
                                'voor' => $profile->huisdier_voorkeuren,
                                'na' => json_encode($voorkeuren, JSON_UNESCAPED_SLASHES)
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error("Fout bij profiel {$profile->id}", [
                            'error' => $e->getMessage(),
                            'waarde' => $profile->huisdier_voorkeuren
                        ]);
                    }
                }
            }

            // Controleer resultaat
            $after = DB::table('sitter_profiles')
                ->select('id', 'huisdier_voorkeuren')
                ->get();
            Log::info('Waarden na update:', $after->toArray());

        } catch (\Exception $e) {
            Log::error('Algemene fout tijdens migratie:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function down()
    {
        // Voor deze data transformatie is geen rollback nodig
    }
};
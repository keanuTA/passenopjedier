<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixHuisdierVoorkeurenEncoding extends Command
{
    protected $signature = 'app:fix-huisdier-voorkeuren';
    protected $description = 'Fix de JSON encoding van huisdier_voorkeuren in sitter_profiles';

    public function handle()
    {
        $this->info('Start fixing huisdier_voorkeuren encoding...');
        
        try {
            // Log huidige waarden
            $current = DB::table('sitter_profiles')
                ->select('id', 'huisdier_voorkeuren')
                ->get();
            
            $this->info('Gevonden profielen: ' . $current->count());

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

                            $this->info("Profiel {$profile->id} succesvol geüpdatet");
                        }
                    } catch (\Exception $e) {
                        $this->error("Fout bij profiel {$profile->id}: " . $e->getMessage());
                    }
                }
            }

            $this->info('Klaar met updaten van huisdier_voorkeuren!');

        } catch (\Exception $e) {
            $this->error('Algemene fout: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
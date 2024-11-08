<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SitterProfile;
use Illuminate\Database\Seeder;

class SitterProfileSeeder extends Seeder
{
    public function run()
    {
        // Maak wat extra users voor de oppasprofielen
        $users = User::factory(5)->create();

        foreach ($users as $user) {
            SitterProfile::create([
                'user_id' => $user->id,
                'ervaring' => 'Ik heb '.rand(2,10).' jaar ervaring met het verzorgen van huisdieren. Tijdens mijn jeugd hadden we altijd honden en katten thuis.',
                'over_mij' => 'Ik ben een enthousiaste dierenliefhebber die graag tijd doorbrengt met huisdieren. In mijn vrije tijd wandel ik graag en speel ik met dieren.',
                'huisdier_voorkeuren' => ['hond', 'kat'],
                'uurtarief' => rand(10, 25),
                'is_beschikbaar' => true
            ]);
        }
    }
}
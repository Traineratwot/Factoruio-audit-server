<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = getenv('FILAMENT_ROOT_USER');
        $password = getenv('FILAMENT_ROOT_PASSWORD');
        if (! User::where('email', $email)->exists()) {
            User::factory()->create([
                'name' => 'admin',
                'email' => $email,
                'password' => $password,
            ]);
            $user = User::where('email', $email)->first();
            echo "User: $email | password: $password\n";
        }
    }
}

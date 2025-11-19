<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador por defecto
        User::updateOrCreate(
            ['email' => 'admin@edulink.com'],
            [
                'name' => 'Administrador EduLink',
                'email' => 'admin@edulink.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuario administrador creado:');
        $this->command->info('Email: admin@edulink.com');
        $this->command->info('Password: admin123');
    }
}

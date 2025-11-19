<?php

use App\Models\User;
use App\Models\Maestro;
use Illuminate\Support\Facades\Hash;

// Crear usuario maestro
$user = User::create([
    'name' => 'Osmar Maestro',
    'email' => 'osmar@gmail.com',
    'password' => Hash::make('12345678'),
    'role' => 'maestro'
]);

$maestro = Maestro::create([
    'user_id' => $user->id,
    'nombre' => 'Osmar',
    'apellido' => 'García', 
    'correo' => 'osmar@gmail.com',
    'contrasena' => Hash::make('12345678'),
    'especialidad' => 'Desarrollo Web',
    'experiencia' => '5 años'
]);

echo "✅ Usuario maestro creado exitosamente:\n";
echo "- ID: " . $maestro->id . "\n";
echo "- Email: " . $maestro->correo . "\n";
echo "- Especialidad: " . $maestro->especialidad . "\n";
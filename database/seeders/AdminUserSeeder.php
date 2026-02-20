<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Usuario administrador por defecto.
     * Email: admin@cepromed.peru  |  Contraseña: AdminCepromed2025!
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cepromed.peru'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('AdminCepromed2025!'),
                'description' => 'Usuario administrador del sistema',
            ]
        );
    }
}

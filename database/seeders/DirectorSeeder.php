<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Las 2 directoras del sistema (rol admin, is_director = true).
 * Credenciales por variables de .env; en producción no hay valores por defecto.
 */
class DirectorSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        foreach ([1, 2] as $i) {
            $email = $this->requiredEnv("DIRECTORA_{$i}_EMAIL", "directora{$i}@bafoldanz.test");
            $password = $this->requiredEnv("DIRECTORA_{$i}_PASSWORD", 'password');
            $name = env("DIRECTORA_{$i}_NAME", "Directora {$i}");

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'is_director' => true,
                ],
            );

            $user->syncRoles(['admin']);
        }
    }

    /**
     * Lee una variable de .env; en producción exige que exista,
     * en desarrollo usa el fallback indicado.
     */
    private function requiredEnv(string $key, string $fallback): string
    {
        $value = env($key);

        if (app()->environment('production') && ($value === null || $value === '')) {
            throw new RuntimeException("Falta la variable de entorno {$key}: obligatoria en producción.");
        }

        return $value !== null && $value !== '' ? $value : $fallback;
    }
}

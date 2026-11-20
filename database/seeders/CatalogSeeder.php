<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Group;
use App\Models\GroupType;
use Illuminate\Database\Seeder;

/**
 * Catálogos base (sucursales, tipos de grupo y grupos con horario):
 * corre siempre, en desarrollo y en producción. Es idempotente.
 */
class CatalogSeeder extends Seeder
{
    /**
     * Tipos de grupo canónicos y su horario en cada sucursal.
     *
     * @var array<string, array{start: string, end: string}>
     */
    private const GROUP_TYPES = [
        'Infantil' => ['start' => '16:00:00', 'end' => '17:00:00'],
        'Juvenil Intermedio' => ['start' => '17:00:00', 'end' => '18:00:00'],
        'Juvenil Avanzado' => ['start' => '18:00:00', 'end' => '19:00:00'],
    ];

    public function run(): void
    {
        $branches = collect([
            ['name' => 'Centro', 'default_fee' => '120.00'],
            ['name' => 'Norte', 'default_fee' => '60.00'],
        ]);

        $groupTypes = collect(self::GROUP_TYPES)->map(
            fn (array $schedule, string $name) => GroupType::updateOrCreate(['name' => $name]),
        );

        foreach ($branches as $data) {
            $branch = Branch::updateOrCreate(
                ['name' => $data['name']],
                ['default_fee' => $data['default_fee']],
            );

            foreach ($groupTypes as $name => $groupType) {
                $schedule = self::GROUP_TYPES[$name];

                Group::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'group_type_id' => $groupType->id,
                        'hour_start' => $schedule['start'],
                        'hour_end' => $schedule['end'],
                    ],
                    ['is_active' => true],
                );
            }
        }
    }
}

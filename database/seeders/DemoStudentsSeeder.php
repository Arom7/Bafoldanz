<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Database\Seeder;

/**
 * Alumnos de ejemplo: SOLO desarrollo. Los alumnos reales
 * se cargan desde la UI (nunca correr en producción).
 */
class DemoStudentsSeeder extends Seeder
{
    public function run(): void
    {
        $demo = [
            [
                'name' => 'Valeria',
                'branch' => 'Centro',
                'group_type' => 'Juvenil Intermedio',
                'fee' => '120.00',
                'payment_day' => 10,
                'is_promo' => false,
            ],
            [
                'name' => 'Camila',
                'branch' => 'Centro',
                'group_type' => 'Infantil',
                'fee' => '90.00',
                'payment_day' => 15,
                'is_promo' => true,
            ],
            [
                'name' => 'Sofía',
                'branch' => 'Norte',
                'group_type' => 'Juvenil Avanzado',
                'fee' => '60.00',
                'payment_day' => 5,
                'is_promo' => false,
            ],
        ];

        foreach ($demo as $data) {
            $branch = Branch::where('name', $data['branch'])->firstOrFail();
            $groupType = GroupType::where('name', $data['group_type'])->firstOrFail();
            $group = Group::where('branch_id', $branch->id)
                ->where('group_type_id', $groupType->id)
                ->firstOrFail();

            $student = Student::updateOrCreate(
                ['name' => $data['name']],
                [
                    'fee' => $data['fee'],
                    'payment_day' => $data['payment_day'],
                    'is_promo' => $data['is_promo'],
                    'active' => true,
                    'enrolled_at' => now()->toDateString(),
                    'retired_at' => null,
                ],
            );

            Registration::updateOrCreate(
                ['student_id' => $student->id],
                ['group_id' => $group->id],
            );
        }
    }
}

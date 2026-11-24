<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Casos de uso del módulo Alumnos. La lógica de consulta vive aquí;
 * los controladores se mantienen delgados (ver docs/02-arquitectura.md).
 */
class StudentService
{
    /**
     * Tamaño fijo de página del listado (decisión v1).
     */
    public const PER_PAGE = 10;

    /**
     * Listado paginado con filtros y orden resueltos en backend.
     *
     * @param  array{search?: string|null, status?: string|null, branch_id?: int|string|null, group_type_id?: int|string|null}  $filters
     * @return LengthAwarePaginator<int, Student>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Student::query()
            ->with(['registration.group.branch', 'registration.group.groupType'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when(($filters['status'] ?? null) === 'active', function (Builder $query): void {
                $query->active();
            })
            ->when(($filters['status'] ?? null) === 'retired', function (Builder $query): void {
                $query->retired();
            })
            ->when($filters['branch_id'] ?? null, function (Builder $query, int|string $branchId): void {
                $query->whereHas('registration.group', function (Builder $group) use ($branchId): void {
                    $group->where('branch_id', $branchId);
                });
            })
            ->when($filters['group_type_id'] ?? null, function (Builder $query, int|string $groupTypeId): void {
                $query->whereHas('registration.group', function (Builder $group) use ($groupTypeId): void {
                    $group->where('group_type_id', $groupTypeId);
                });
            })
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * Catálogos usados por los filtros del listado.
     *
     * @return array{branches: Collection<int, Branch>, group_types: Collection<int, GroupType>}
     */
    public function filterOptions(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'group_types' => GroupType::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Catálogos usados por el formulario de alta/edición.
     *
     * @return array{branches: Collection<int, Branch>, groups: Collection<int, Group>}
     */
    public function formOptions(): array
    {
        return [
            'groups' => Group::query()
                ->active()
                ->with(['branch:id,name,default_fee', 'groupType:id,name'])
                ->orderBy('branch_id')
                ->orderBy('hour_start')
                ->get(['id', 'branch_id', 'group_type_id', 'hour_start', 'hour_end']),
        ];
    }

    /**
     * Alta de un alumno con su inscripción en una sola transacción.
     *
     * @param  array{name: string, branch_id: int|string, group_id: int|string, fee: int|float|string, payment_day: int|string, is_promo?: bool|int|string|null}  $data
     */
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $student = Student::create([
                'name' => $data['name'],
                'fee' => $data['fee'],
                'payment_day' => (int) $data['payment_day'],
                'is_promo' => filter_var($data['is_promo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'active' => true,
                'enrolled_at' => now()->toDateString(),
                'retired_at' => null,
            ]);

            $student->registration()->create([
                'group_id' => $data['group_id'],
            ]);

            return $student;
        });
    }
}

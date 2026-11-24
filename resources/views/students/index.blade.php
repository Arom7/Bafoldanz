@extends('layouts.app')

@section('title', 'Bailarines · BAFOLDANZ Digital')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="w-1/2">
            <h1 class="font-sans text-xl font-bold tracking-tight text-white md:text-3xl">Listado Alumnos</h1>
        </div>
        <a href="{{ route('students.create') }}" class="btn-primary">Registrar bailarín</a>
    </div>

    <form method="GET" action="{{ route('students.index') }}" class="rounded-3xl border border-border bg-surface p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="relative">
                <span class="material-symbols-outlined icon-md absolute top-1/2 left-3 -translate-y-1/2 text-muted">search</span>
                <input
                    id="search" name="search" type="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Nombre del bailarín"
                    class="field-focus-border h-12 w-full rounded-xl border border-border bg-input pl-11 pr-4 font-body text-base text-text"
                >
            </div>

            <div>
                <label for="status" class="mb-1.5 block font-sans text-xs font-600 uppercase tracking-wider text-muted">Estado</label>
                <select id="status" name="status" class="field-focus-border h-12 w-full rounded-xl border border-border bg-input px-4 font-body text-base text-text">
                    <option value="">Todos</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Activos</option>
                    <option value="retired" @selected(($filters['status'] ?? '') === 'retired')>Retirados</option>
                </select>
            </div>

            <div>
                <label for="branch_id" class="mb-1.5 block font-sans text-xs font-600 uppercase tracking-wider text-muted">Sucursal</label>
                <select id="branch_id" name="branch_id" class="field-focus-border h-12 w-full rounded-xl border border-border bg-input px-4 font-body text-base text-text">
                    <option value="">Todas</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="group_type_id" class="mb-1.5 block font-sans text-xs font-600 uppercase tracking-wider text-muted">Tipo de grupo</label>
                <select id="group_type_id" name="group_type_id" class="field-focus-border h-12 w-full rounded-xl border border-border bg-input px-4 font-body text-base text-text">
                    <option value="">Todos</option>
                    @foreach ($groupTypes as $groupType)
                        <option value="{{ $groupType->id }}" @selected((string) ($filters['group_type_id'] ?? '') === (string) $groupType->id)>{{ $groupType->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Filtrar</button>
                <a href="{{ route('students.index') }}" class="btn-secondary">Limpiar</a>
            </div>
        </div>
    </form>

    <section class="rounded-3xl border border-border bg-surface">
        <div class="border-b border-border p-4">
            <div class="flex items-center justify-between">
                <h2 class="font-sans text-[17px] font-bold text-white">Listado</h2>
                <span class="text-xs text-muted">{{ $students->total() }} {{ $students->total() === 1 ? 'bailarín' : 'bailarines' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($students as $student)
                <article class="group flex items-center justify-between rounded-2xl border border-border bg-surface-alt p-4 transition-colors hover:border-primary">
                    <div class="min-w-0">
                        <p class="truncate font-sans text-sm font-700 text-white">{{ $student->name }}</p>
                        <p class="mt-1 truncate text-xs text-muted">
                            @if ($student->group)
                                {{ $student->group->groupType?->name }} · {{ $student->group->branch?->name }}
                                · {{ substr($student->group->hour_start, 0, 5) }}–{{ substr($student->group->hour_end, 0, 5) }}
                            @else
                                Sin grupo asignado
                            @endif
                        </p>
                    </div>
                    <div class="ml-3 flex shrink-0 flex-col items-end gap-2">
                        <span class="pill-{{ $student->active ? 'ok' : 'danger' }}">{{ $student->active ? 'Activo' : 'Retirado' }}</span>
                        <span class="text-xs font-700 text-white">Bs. {{ $student->fee }}</span>
                    </div>
                </article>
            @empty
                <div class="empty-state p-6">
                    <h3 class="mb-2 font-sans text-base font-700 text-white">Sin bailarines</h3>
                    <p class="text-xs text-muted">No hay bailarines para los filtros seleccionados.</p>
                </div>
            @endforelse
        </div>

        @if ($students->hasPages())
            <div class="flex items-center justify-between border-t border-border p-4">
                <div>
                    @if ($students->onFirstPage())
                        <span class="text-xs text-muted">‹ Anterior</span>
                    @else
                        <a href="{{ $students->previousPageUrl() }}" class="text-xs font-700 text-primary">‹ Anterior</a>
                    @endif
                </div>
                <span class="text-xs text-muted">{{ $students->firstItem() }}–{{ $students->lastItem() }} de {{ $students->total() }} · Página {{ $students->currentPage() }} de {{ $students->lastPage() }}</span>
                <div>
                    @if ($students->hasMorePages())
                        <a href="{{ $students->nextPageUrl() }}" class="text-xs font-700 text-primary">Siguiente ›</a>
                    @else
                        <span class="text-xs text-muted">Siguiente ›</span>
                    @endif
                </div>
            </div>
        @endif
    </section>
@endsection

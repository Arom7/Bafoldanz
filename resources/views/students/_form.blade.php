@php
    $editing = isset($student) && $student !== null;
    $selectedBranch = old('branch_id', $editing ? $student->registration?->group?->branch_id : '');
    $selectedGroup = old('group_id', $editing ? $student->registration?->group_id : '');
    $fee = old('fee', $editing ? $student->fee : '');
    $paymentDay = old('payment_day', $editing ? $student->payment_day : '');
    $isPromo = old('is_promo', $editing ? $student->is_promo : false);
@endphp

<form method="POST" action="{{ $action }}" data-student-form class="flex flex-col gap-6 pb-15 md:pb-0">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <section class="rounded-2xl border border-border bg-card p-4 shadow-[0_0_15px_rgba(168,85,247,0.15)] md:p-5">
        <header class="mb-4 flex items-center gap-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined icon-sm">person</span>
            </span>
            <h2 class="font-sans text-lg font-semibold tracking-tight text-text">Datos personales</h2>
        </header>

        <div class="min-w-0">
            <label for="name" class="mb-2 block font-body text-sm font-semibold text-text">Nombre del bailarín <span
                    class="text-primary">*</span></label>
            <div class="relative min-w-0">
                <span
                    class="material-symbols-outlined icon-md pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted">badge</span>
                <input id="name" name="name" type="text" maxlength="120" autocomplete="name"
                    value="{{ old('name', $editing ? $student->name : '') }}"
                    placeholder="Ej. Mateo Alejandro Quispe Flores" required
                    @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                    class="field-focus-border h-12 w-full max-w-full rounded-xl border border-border bg-input pl-11 pr-4 font-body text-base text-text placeholder:text-muted/60">
            </div>
            @error('name')
                <p id="name-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="rounded-2xl border border-border bg-card p-4 shadow-[0_0_15px_rgba(168,85,247,0.15)] md:p-5">
        <header class="mb-4 flex items-center gap-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined icon-sm">groups_2</span>
            </span>
            <h2 class="font-sans text-lg font-semibold tracking-tight text-text">Asignación de elenco</h2>
        </header>

        <div class="grid grid-cols-1">
            <div class="min-w-0">
                <label for="group_id" class="mb-2 block font-body text-sm font-semibold text-text">Sucursal y Grupo <span
                        class="text-primary">*</span></label>
                <input type="hidden" id="branch_id" name="branch_id" value="{{ $selectedBranch }}">
                <div class="relative min-w-0">
                    <span
                        class="material-symbols-outlined icon-md pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted">storefront</span>
                    <select id="group_id" name="group_id" required
                        @error('group_id') aria-invalid="true" aria-describedby="group_id-error" @enderror
                        class="field-focus-border h-12 w-full max-w-full appearance-none rounded-xl border border-border bg-input pl-11 pr-10 font-body text-base text-text">
                        <option value="">Selecciona un grupo</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}" data-branch-id="{{ $group->branch_id }}"
                                data-branch-fee="{{ $group->branch->default_fee }}"
                                @selected((string) $selectedGroup === (string) $group->id)>
                                {{ $group->groupType->name }} - {{ $group->branch->name }} ({{ $group->hour_start }} -
                                {{ $group->hour_end }})
                            </option>
                        @endforeach
                    </select>
                    <span
                        class="material-symbols-outlined icon-md pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-muted">expand_more</span>
                </div>
                @error('group_id')
                    <p id="group_id-error" class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-border bg-card p-4 shadow-[0_0_15px_rgba(168,85,247,0.15)] md:p-5">
        <header class="mb-4 flex items-center gap-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined icon-sm">payments</span>
            </span>
            <h2 class="font-sans text-lg font-semibold tracking-tight text-text">Mensualidad y cobros</h2>
        </header>

        <div
            class="mt-4 flex min-h-12 items-center justify-between gap-4 rounded-xl border border-border bg-surface-alt p-4 pb-8">
            <div class="min-w-0">
                <span id="promo-label" class="block font-body text-sm font-semibold text-text">¿Aplica tarifa con
                    promoción?</span>
                <span id="promo-help" class="mt-1 block text-xs text-muted">Si está desactivado, la mensualidad toma
                    la tarifa registrada de la sucursal del grupo elegido; actívalo para ingresar una tarifa
                    promocional.</span>
            </div>
            <label class="toggle shrink-0">
                <input type="hidden" name="is_promo" value="0">
                <input id="is_promo" type="checkbox" name="is_promo" value="1" role="switch"
                    aria-labelledby="promo-label" aria-describedby="promo-help"
                    aria-checked="{{ (bool) $isPromo ? 'true' : 'false' }}" @checked((bool) $isPromo)>
                <span class="toggle-slider"></span>
            </label>
        </div>
        @error('is_promo')
            <p class="form-error">{{ $message }}</p>
        @enderror

        <div class="grid grid-cols-1 gap-4 py-4 md:grid-cols-2">
            <div class="min-w-0">
                <label for="fee" class="mb-2 block font-body text-sm font-semibold text-text">Mensualidad
                    (Bs.)</label>
                <div class="relative min-w-0">
                    <span
                        class="material-symbols-outlined icon-md pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted">payments</span>
                    <input id="fee" name="fee" type="number" step="0.01" min="0" max="9999.99"
                        inputmode="decimal" autocomplete="off" value="{{ $fee }}" required
                        @readonly(!(bool) $isPromo)
                        aria-readonly="{{ (bool) $isPromo ? 'false' : 'true' }}"
                        @error('fee') aria-invalid="true" aria-describedby="fee-error" @enderror
                        class="field-focus-border h-12 w-full max-w-full rounded-xl border border-border bg-input pl-11 pr-4 font-body text-base text-text read-only:cursor-not-allowed read-only:bg-surface-alt">
                </div>
                @error('fee')
                    <p id="fee-error" class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="min-w-0">
                <label for="payment_day" class="mb-2 block font-body text-sm font-semibold text-text">Día de cobro
                    mensual</label>
                <div class="relative min-w-0">
                    <span
                        class="material-symbols-outlined icon-md pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted">calendar_month</span>
                    <select id="payment_day" name="payment_day" required
                        @error('payment_day') aria-invalid="true" aria-describedby="payment_day-error" @enderror
                        class="field-focus-border h-12 w-full max-w-full appearance-none rounded-xl border border-border bg-input pl-11 pr-10 font-body text-base text-text">
                        @for ($day = 1; $day <= 28; $day++)
                            <option value="{{ $day }}" @selected((int) $paymentDay === $day)>Día {{ $day }}
                                de cada mes</option>
                        @endfor
                    </select>
                    <span
                        class="material-symbols-outlined icon-md pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-muted">expand_more</span>
                </div>
                @error('payment_day')
                    <p id="payment_day-error" class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <div class="grid gap-3 md:flex md:flex-wrap">
        <button type="submit" class="btn-primary w-full md:w-auto">{{ $submitLabel ?? 'Guardar bailarín' }}</button>
        <a href="{{ route('students.index') }}" class="btn-secondary w-full md:w-auto">Cancelar</a>
    </div>
</form>

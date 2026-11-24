@extends('layouts.app')

@section('title', 'Agregar bailarín · BAFOLDANZ Digital')

@section('content')
    <h1 class="font-sans text-2xl font-bold tracking-tight text-text md:text-3xl">Agregar bailarín</h1>

    @include('students._form', [
        'action' => route('students.store'),
        'method' => 'POST',
        'submitLabel' => 'Guardar bailarín',
    ])
@endsection

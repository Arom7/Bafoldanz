<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexStudentRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private readonly StudentService $students) {}

    /**
     * Listado de alumnos: filtros, orden y paginación server-side.
     */
    public function index(IndexStudentRequest $request): View
    {
        $filters = $request->validated();
        $options = $this->students->filterOptions();

        return view('students.index', [
            'students' => $this->students->paginate($filters),
            'filters' => $filters,
            'branches' => $options['branches'],
            'groupTypes' => $options['group_types'],
        ]);
    }

    /**
     * Formulario de alta de alumno.
     */
    public function create(): View
    {
        $options = $this->students->formOptions();

        return view('students.create', [
            'groups' => $options['groups'],
        ]);
    }

    /**
     * Persiste el alumno y su inscripción.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $this->students->create($request->validated());

        return redirect()
            ->route('students.index')
            ->with('success', 'Bailarín agregado');
    }
}

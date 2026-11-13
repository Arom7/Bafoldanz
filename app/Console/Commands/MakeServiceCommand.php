<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * `php artisan make:service Nombre` crea una clase de servicio en app/Services.
 * No es nativo de Laravel: stub propio agregado en la Fase 0 (ver Agents.md).
 */
class MakeServiceCommand extends GeneratorCommand
{
    protected $name = 'make:service';

    protected $description = 'Crea una clase de servicio en app/Services';

    protected $type = 'Service';

    protected function getStub(): string
    {
        return __DIR__.'/stubs/service.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Services';
    }

    /**
     * El nombre del servicio es obligatorio.
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'Nombre del servicio (soporta subdirectorios, ej. Payments/PaymentService)'],
        ];
    }
}

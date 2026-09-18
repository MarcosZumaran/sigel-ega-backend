<?php

namespace App\Exceptions;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public function __construct(string $recurso = 'Recurso', ?int $id = null)
    {
        parent::__construct($recurso . ' no encontrado' . ($id !== null ? " con id {$id}" : '') . '.');
    }
}

<?php

namespace Tests\Traits;

use Illuminate\Foundation\Testing\DatabaseTransactions;

trait TransaccionalTest
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $bd = config('database.connections.'.config('database.default').'.database');
        if (! str_contains((string) $bd, 'sigel_ega')) {
            $this->fail("⚠️ Tests apuntan a BD incorrecta: {$bd}");
        }
    }
}

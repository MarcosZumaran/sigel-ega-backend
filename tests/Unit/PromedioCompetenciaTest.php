<?php

namespace Tests\Unit;

use App\Services\PromedioCompetenciaService;
use PHPUnit\Framework\TestCase;

class PromedioCompetenciaTest extends TestCase
{
    public function test_conversion_AD_retorna_4(): void
    {
        $this->assertEquals(4.0, PromedioCompetenciaService::nivelANumero('AD'));
    }

    public function test_conversion_B_retorna_2_5(): void
    {
        $this->assertEquals(2.5, PromedioCompetenciaService::nivelANumero('B'));
    }

    public function test_numero_2_5_retorna_A(): void
    {
        $this->assertEquals('A', PromedioCompetenciaService::numeroANivel(2.5));
    }

    public function test_numero_1_0_retorna_C(): void
    {
        $this->assertEquals('C', PromedioCompetenciaService::numeroANivel(1.0));
    }
}

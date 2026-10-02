<?php

/**
 * Configuración de compatibilidad SIAGIE.
 *
 * Este archivo define:
 * - El formato del nombre de archivo (RegNotas_*.xlsx)
 * - El mapeo de columnas por tipo de import/export
 * - Las reglas CNEB por nivel/grado
 *
 * ⚠️ Si el MINEDU cambia el Anexo 1, editar SOLO este archivo.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Nombre del archivo SIAGIE
    |--------------------------------------------------------------------------
    | Estructura oficial: RegNotas_{codigo_modular}_{anio}_{codigo_nivel}_{periodo}_{seccion}.xlsx
    | Ejemplo real: RegNotas_05858020_20_B02023_T1_34331.xlsx
    */
    'nombre_archivo' => [
        'patron' => 'RegNotas_{codigo_modular}_{anio}_{codigo_nivel}_{periodo}_{seccion}.xlsx',
        'patron_regex' => '/^RegNotas_\d+_\d{2}_[A-Z]\d+_[A-Z]\d+_\d+\.(xlsx|xls|csv|txt)$/i',
        'validar_al_importar' => env('SIAGIE_VALIDAR_NOMBRE', false), // true en prod
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo de columnas para IMPORT (SIAGIE → Sistema)
    |--------------------------------------------------------------------------
    | Cada entrada define:
    | - 'columna': nombre del header en el Excel de SIAGIE
    | - 'campo': campo destino en nuestro modelo Calificacion
    | - 'requerido': si es obligatorio
    | - 'transform': función opcional de transformación
    */
    'import' => [
        'columnas' => [
            'dni' => [
                'columna' => 'DNI',
                'campo' => 'estudiante.dni',
                'requerido' => true,
                'alternativas' => ['dni', 'documento', 'nro_documento'],
            ],
            'codigo_estudiante' => [
                'columna' => 'Código',
                'campo' => 'estudiante.codigo_estudiante',
                'requerido' => false,
                'alternativas' => ['codigo', 'cod_estudiante'],
            ],
            'apellidos_nombres' => [
                'columna' => 'Apellidos y Nombres',
                'campo' => 'estudiante.nombre_completo',
                'requerido' => false,
                'alternativas' => ['apellidos y nombres', 'nombres', 'estudiante'],
            ],
            'nivel_logro' => [
                'columna' => 'Nivel de logro',
                'campo' => 'calificacion.nivel_logro',
                'requerido' => false,
                'alternativas' => ['nivel_logro', 'nivel', 'logro'],
                'validacion' => ['in:AD,A,B,C'],
            ],
            'nota' => [
                'columna' => 'Nota',
                'campo' => 'calificacion.nota',
                'requerido' => false,
                'alternativas' => ['nota', 'calificacion', 'promedio'],
                'validacion' => ['numeric', 'min:0', 'max:20'],
            ],
            'motivo_c' => [
                'columna' => 'Conclusión descriptiva',
                'campo' => 'calificacion.motivo_nota_c',
                'requerido' => false,
                'alternativas' => ['conclusion', 'motivo', 'motivo_c', 'conclusion_descriptiva'],
                'validacion' => ['string', 'min:10', 'max:350'],
            ],
            'area_id' => [
                'columna' => 'Área',
                'campo' => 'calificacion.area_id',
                'requerido' => false,
                'alternativas' => ['area', 'area_id', 'codigo_area'],
            ],
            'tipo_evaluacion_id' => [
                'columna' => 'Tipo',
                'campo' => 'calificacion.tipo_evaluacion_id',
                'requerido' => false,
                'alternativas' => ['tipo', 'tipo_evaluacion', 'tipo_evaluacion_id'],
                'default' => 1, // Bimestral
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo de columnas para EXPORT (Sistema → SIAGIE)
    |--------------------------------------------------------------------------
    */
    'export' => [
        // Los paths se resuelven desde el modelo Calificacion:
        // - 'calificacion.*' = autorreferencia (atributo propio)
        // - 'area' / 'tipoEvaluacion' = relaciones directas (nombres exactos del modelo)
        // - 'matricula.estudiante.*' = el estudiante llega vía su matrícula
        'columnas' => [
            ['campo' => 'matricula.estudiante.dni', 'header' => 'DNI', 'ancho' => 10],
            ['campo' => 'matricula.estudiante.codigo_estudiante', 'header' => 'Código', 'ancho' => 12],
            ['campo' => 'matricula.estudiante.nombre_completo', 'header' => 'Apellidos y Nombres', 'ancho' => 40],
            ['campo' => 'area.nombre', 'header' => 'Área', 'ancho' => 25],
            ['campo' => 'area.codigo_siagie', 'header' => 'Código Área', 'ancho' => 15],
            ['campo' => 'tipoEvaluacion.nombre', 'header' => 'Tipo', 'ancho' => 15],
            ['campo' => 'calificacion.nivel_logro', 'header' => 'Nivel', 'ancho' => 8],
            ['campo' => 'calificacion.nota', 'header' => 'Nota', 'ancho' => 8],
            ['campo' => 'calificacion.escala', 'header' => 'Escala', 'ancho' => 10],
            ['campo' => 'calificacion.motivo_nota_c', 'header' => 'Conclusión descriptiva', 'ancho' => 50],
        ],
        'estilos' => [
            'header_bg' => '1E3A8A',
            'header_color' => 'FFFFFF',
            'nota_c_bg' => 'FECACA',
            'nota_c_color' => '991B1B',
            'fila_alterna_bg' => 'F8FAFC',
            'border_color' => 'CBD5E1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reglas CNEB por nivel/grado
    |--------------------------------------------------------------------------
    | Define qué escala de calificación aplica a cada nivel/grado.
    */
    'reglas_cneb' => [
        'inicial' => [
            'escala' => 'literal', // AD, A, B, C
            'nota_c_permitida' => true,
            'requiere_conclusion_c' => true,
            'longitud_conclusion' => [10, 350],
        ],
        'primaria' => [
            '1' => [ // 1° grado: promoción automática
                'escala' => 'literal',
                'nota_c_permitida' => false,
                'requiere_conclusion_c' => false,
            ],
            '2-6' => [
                'escala' => 'literal',
                'nota_c_permitida' => true,
                'requiere_conclusion_c' => true,
                'longitud_conclusion' => [10, 350],
            ],
        ],
        'secundaria' => [
            '1-4' => [
                'escala' => 'literal',
                'nota_c_permitida' => false, // C no se registra en SIAGIE
                'requiere_conclusion_c' => false,
            ],
            '5' => [
                'escala' => 'vigesimal', // Numérico 0-20
                'nota_c_permitida' => false,
                'requiere_conclusion_c' => false,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */
    'validaciones' => [
        'max_filas' => 500, // Límite de filas por import
        'max_tamano_archivo_kb' => 5120, // 5 MB
        'extensiones_permitidas' => ['xlsx', 'xls', 'csv', 'txt'],
    ],
];

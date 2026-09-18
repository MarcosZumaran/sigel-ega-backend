<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ periodo: Object });

function activar() {
    if (!confirm('¿Activar este periodo? Se desactivarán los demás.')) return;
    router.post(route('periodos.activar', props.periodo.id));
}

function promocionar() {
    if (!confirm('¿Ejecutar promoción? Los estudiantes avanzan al siguiente grado de su nivel. Esta acción no se puede deshacer.')) return;
    router.post(route('periodos.promocion', props.periodo.id));
}

function eliminar() {
    if (!confirm('¿Eliminar este periodo? Solo es posible sin matrículas ni reportes.')) return;
    router.delete(route('periodos.destroy', props.periodo.id));
}
</script>

<template>
    <Head :title="`Periodo ${periodo?.nombre}`" />
    <AuthenticatedLayout :crumbs="[{ label: 'Periodos', href: route('periodos.index') }, { label: periodo?.nombre }]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-[#1E3A8A]">
                    {{ periodo?.nombre }}
                    <span v-if="periodo?.activo" class="ml-2 rounded-full bg-emerald-100 px-3 py-1 align-middle text-xs font-medium text-emerald-800">Activo</span>
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Vigencia: {{ periodo?.fecha_inicio ?? '—' }} → {{ periodo?.fecha_fin ?? '—' }} ·
                    {{ periodo?.matriculas_count ?? 0 }} matrículas
                </p>
            </div>
            <div class="flex gap-2">
                <Link :href="route('periodos.edit', periodo.id)" class="inline-flex min-h-[44px] items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Editar
                </Link>
                <button v-if="!periodo?.activo" type="button" class="min-h-[44px] rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800" @click="activar">
                    Activar
                </button>
                <button v-if="periodo?.activo" type="button" class="min-h-[44px] rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700" @click="promocionar">
                    Promocionar
                </button>
                <button type="button" class="min-h-[44px] rounded-lg bg-[#C8102E] px-4 py-2 text-sm font-medium text-white hover:bg-[#a50d26]" @click="eliminar">
                    Eliminar
                </button>
            </div>
        </div>

        <p v-if="$page.props.flash?.success" role="status" class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ $page.props.flash.success }}
        </p>
        <p v-if="$page.props.flash?.error" role="alert" class="mt-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-800">
            {{ $page.props.flash.error }}
        </p>
    </AuthenticatedLayout>
</template>

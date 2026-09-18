<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ reporte: Object });

function eliminar() {
    if (!confirm('¿Eliminar este reporte?')) return;
    router.delete(route('reportes.destroy', $props.reporte.id));
}
</script>

<template>
    <Head :title="`Reporte ${reporte?.tipo}`" />
    <AuthenticatedLayout :crumbs="[{ label: 'Reportes', href: route('reportes.index') }, { label: reporte?.tipo }]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-[#1E3A8A]">Reporte {{ reporte?.tipo }}</h1>
            <button type="button" class="min-h-[44px] rounded-lg bg-[#C8102E] px-4 py-2 text-sm font-medium text-white hover:bg-[#a50d26]" @click="eliminar">
                Eliminar
            </button>
        </div>

        <p v-if="$page.props.flash?.success" role="status" class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ $page.props.flash.success }}
        </p>

        <dl class="mt-4 grid max-w-3xl gap-3 rounded-xl bg-white p-6 text-sm shadow sm:grid-cols-2">
            <div><dt class="font-medium text-slate-500">Tipo</dt><dd class="mt-0.5">{{ reporte?.tipo }}</dd></div>
            <div><dt class="font-medium text-slate-500">Formato</dt><dd class="mt-0.5 uppercase">{{ reporte?.formato }}</dd></div>
            <div><dt class="font-medium text-slate-500">Periodo</dt><dd class="mt-0.5">{{ reporte?.periodo?.nombre ?? 'General' }}</dd></div>
            <div><dt class="font-medium text-slate-500">Sección</dt><dd class="mt-0.5">{{ reporte?.seccion ? (reporte.seccion.grado?.nombre + ' ' + reporte.seccion.nombre) : 'Todas' }}</dd></div>
            <div><dt class="font-medium text-slate-500">Estado</dt><dd class="mt-0.5">{{ reporte?.estado }}</dd></div>
            <div><dt class="font-medium text-slate-500">Generado por</dt><dd class="mt-0.5">{{ reporte?.autor?.name ?? ('Usuario #' + reporte?.generado_por) }}</dd></div>
            <div class="sm:col-span-2"><dt class="font-medium text-slate-500">Archivo</dt><dd class="mt-0.5 font-mono text-xs">{{ reporte?.ruta_archivo }}</dd></div>
            <div class="sm:col-span-2"><dt class="font-medium text-slate-500">Expira</dt><dd class="mt-0.5">{{ reporte?.expira_en?.slice(0, 10) ?? '—' }}</dd></div>
        </dl>
    </AuthenticatedLayout>
</template>

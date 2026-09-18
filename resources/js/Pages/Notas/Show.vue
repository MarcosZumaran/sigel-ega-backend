<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ nota: Object });

function eliminar(id) {
    if (confirm('¿Eliminar esta calificación?')) router.delete(route('notas.destroy', id));
}
</script>

<template>
    <Head :title="`Nota #${nota.id}`" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="titulo" class="text-xl font-semibold text-gray-800">Nota #{{ nota.id }}</h2>
                <nav aria-label="Acciones de la nota" class="flex gap-2">
                    <Link :href="route('notas.edit', nota.id)" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Editar</Link>
                    <button type="button" @click="eliminar(nota.id)" class="min-h-[44px] rounded-md bg-[#C8102E] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a50d26] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C8102E]">Eliminar</button>
                </nav>
            </div>
        </template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-2 text-green-900">{{ $page.props.flash.success }}</div>
            <dl class="grid gap-3 rounded-lg border bg-white p-6 shadow-sm sm:grid-cols-2">
                <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Estudiante</dt><dd class="font-semibold">{{ nota.matricula?.estudiante?.nombres }} {{ nota.matricula?.estudiante?.apellidos }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Área</dt><dd>{{ nota.area?.nombre ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Evaluación</dt><dd>{{ nota.tipo_evaluacion?.nombre ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Nota</dt><dd class="text-lg font-bold text-[#1E3A8A]">{{ nota.nota ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Nivel de logro</dt><dd>{{ nota.nivel_logro ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Motivo nota C</dt><dd>{{ nota.motivo_nota_c ?? '—' }}</dd></div>
            </dl>
            <Link :href="route('notas.index')" class="mt-4 inline-block text-sm font-medium text-[#1E3A8A] hover:underline">← Volver al listado</Link>
        </main>
    </AuthenticatedLayout>
</template>

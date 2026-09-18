<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ matricula: Object });

function eliminar(id) {
    if (confirm('¿Eliminar esta matrícula?')) router.delete(route('matriculas.destroy', id));
}
</script>

<template>
    <Head :title="`Matrícula #${matricula.id}`" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="titulo" class="text-xl font-semibold text-gray-800">Matrícula #{{ matricula.id }}</h2>
                <nav aria-label="Acciones de la matrícula" class="flex gap-2">
                    <Link :href="route('matriculas.edit', matricula.id)" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Editar</Link>
                    <button type="button" @click="eliminar(matricula.id)" class="min-h-[44px] rounded-md bg-[#C8102E] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a50d26] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C8102E]">Eliminar</button>
                </nav>
            </div>
        </template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-2 text-green-900">{{ $page.props.flash.success }}</div>
            <dl class="grid gap-3 rounded-lg border bg-white p-6 shadow-sm sm:grid-cols-2">
                <div><dt class="text-sm font-medium text-gray-500">Estudiante</dt><dd class="font-semibold">{{ matricula.estudiante?.nombres }} {{ matricula.estudiante?.apellidos }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Sección</dt><dd>{{ matricula.seccion?.grado?.nombre ?? '' }} {{ matricula.seccion?.nombre ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Periodo</dt><dd>{{ matricula.periodo?.nombre }} {{ matricula.periodo?.anio }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Tipo</dt><dd>{{ matricula.tipo_matricula?.nombre ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Fecha</dt><dd>{{ matricula.fecha ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Observaciones</dt><dd>{{ matricula.observaciones ?? '—' }}</dd></div>
            </dl>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <section aria-labelledby="cals" class="rounded-lg border bg-white p-4 shadow-sm">
                    <h3 id="cals" class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Calificaciones ({{ matricula.calificaciones?.length ?? 0 }})</h3>
                    <ul class="divide-y text-sm">
                        <li v-for="c in (matricula.calificaciones ?? [])" :key="c.id" class="py-1.5">
                            <Link :href="route('notas.show', c.id)" class="text-[#1E3A8A] hover:underline">{{ c.area?.nombre ?? 'Área' }}: {{ c.nota ?? c.nivel_logro ?? '—' }}</Link>
                        </li>
                        <li v-if="!(matricula.calificaciones ?? []).length" class="py-1.5 text-gray-500">Sin calificaciones.</li>
                    </ul>
                </section>
                <section aria-labelledby="asis" class="rounded-lg border bg-white p-4 shadow-sm">
                    <h3 id="asis" class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Asistencias ({{ matricula.asistencias?.length ?? 0 }})</h3>
                    <ul class="divide-y text-sm">
                        <li v-for="a in (matricula.asistencias ?? []).slice(0, 10)" :key="a.id" class="py-1.5">
                            <Link :href="route('asistencias.show', a.id)" class="text-[#1E3A8A] hover:underline">{{ a.fecha }} — {{ a.estado }}</Link>
                        </li>
                        <li v-if="!(matricula.asistencias ?? []).length" class="py-1.5 text-gray-500">Sin asistencias.</li>
                    </ul>
                </section>
            </div>
            <Link :href="route('matriculas.index')" class="mt-4 inline-block text-sm font-medium text-[#1E3A8A] hover:underline">← Volver al listado</Link>
        </main>
    </AuthenticatedLayout>
</template>

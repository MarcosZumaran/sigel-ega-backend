<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ estudiante: Object });

function eliminar(id) {
    if (confirm('¿Eliminar este estudiante?')) router.delete(route('estudiantes.destroy', id));
}
</script>

<template>
    <Head :title="`Estudiante`" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="titulo" class="text-xl font-semibold text-gray-800">{{ estudiante.nombres }} {{ estudiante.apellidos }}</h2>
                <nav aria-label="Acciones del estudiante" class="flex gap-2">
                    <Link :href="route('estudiantes.edit', estudiante.id)" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Editar</Link>
                    <button type="button" @click="eliminar(estudiante.id)" class="min-h-[44px] rounded-md bg-[#C8102E] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a50d26] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C8102E]">Eliminar</button>
                </nav>
            </div>
        </template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-2 text-green-900">{{ $page.props.flash.success }}</div>
            <dl class="grid gap-3 rounded-lg border bg-white p-6 shadow-sm sm:grid-cols-2">
                <div><dt class="text-sm font-medium text-gray-500">DNI</dt><dd class="font-semibold">{{ estudiante.dni ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Código</dt><dd>{{ estudiante.codigo_estudiante ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Nacimiento</dt><dd>{{ estudiante.fecha_nacimiento ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Sexo</dt><dd>{{ estudiante.sexo ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Nivel</dt><dd>{{ estudiante.nivel?.nombre ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Grado</dt><dd>{{ estudiante.grado?.nombre ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Apoderado (heredado del padre)</dt>
                    <dd><Link v-if="estudiante.apoderado_id" :href="route('apoderados.show', estudiante.apoderado_id)" class="font-semibold text-[#1E3A8A] hover:underline">#{{ estudiante.apoderado_id }}</Link><span v-else>—</span></dd>
                </div>
            </dl>
            <section aria-labelledby="mats" class="mt-6 rounded-lg border bg-white p-6 shadow-sm">
                <h3 id="mats" class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Matrículas ({{ estudiante.matriculas?.length ?? 0 }})</h3>
                <ul class="divide-y text-sm">
                    <li v-for="m in (estudiante.matriculas ?? [])" :key="m.id" class="py-2">
                        <Link :href="route('matriculas.show', m.id)" class="text-[#1E3A8A] hover:underline">Matrícula #{{ m.id }}</Link>
                        <span class="text-gray-500"> · {{ m.seccion?.nombre ?? '' }} · {{ m.periodo?.nombre ?? '' }} {{ m.periodo?.anio ?? '' }}</span>
                    </li>
                    <li v-if="!(estudiante.matriculas ?? []).length" class="py-2 text-gray-500">Sin matrículas.</li>
                </ul>
            </section>
            <Link :href="route('estudiantes.index')" class="mt-4 inline-block text-sm font-medium text-[#1E3A8A] hover:underline">← Volver al listado</Link>
        </main>
    </AuthenticatedLayout>
</template>

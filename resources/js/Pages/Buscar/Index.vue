<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    q: { type: String, default: '' },
    estudiantes: { type: Array, default: () => [] },
    padres: { type: Array, default: () => [] },
    matriculas: { type: Array, default: () => [] },
    documentos: { type: Array, default: () => [] },
});

const total = computed(() => props.estudiantes.length + props.padres.length + props.matriculas.length + props.documentos.length);
const crumbs = computed(() => [{ label: `Búsqueda: ${props.q}` }]);
</script>

<template>
    <Head :title="`Búsqueda: ${q}`" />

    <AuthenticatedLayout :crumbs="crumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Resultados para “{{ q }}” ({{ total }})</h2>
        </template>

        <div class="mx-auto grid max-w-7xl gap-6 md:grid-cols-2">
            <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg" aria-label="Estudiantes encontrados">
                <h3 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">Estudiantes ({{ estudiantes.length }})</h3>
                <ul class="divide-y">
                    <li v-for="e in estudiantes" :key="e.id" class="px-4 py-2 text-sm">
                        <Link :href="route('estudiantes.show', e.id)" class="font-medium text-[#1E3A8A] hover:underline">{{ e.nombres }} {{ e.apellidos }}</Link>
                        <span class="text-gray-500"> · {{ e.dni }}</span>
                    </li>
                    <li v-if="!estudiantes.length" class="px-4 py-3 text-sm text-gray-500">Sin coincidencias.</li>
                </ul>
            </section>

            <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg" aria-label="Padres encontrados">
                <h3 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">Padres ({{ padres.length }})</h3>
                <ul class="divide-y">
                    <li v-for="p in padres" :key="p.id" class="px-4 py-2 text-sm">
                        <Link :href="route('padres.show', p.id)" class="font-medium text-[#1E3A8A] hover:underline">{{ p.nombres }} {{ p.apellidos }}</Link>
                        <span class="text-gray-500"> · {{ p.dni }}</span>
                    </li>
                    <li v-if="!padres.length" class="px-4 py-3 text-sm text-gray-500">Sin coincidencias.</li>
                </ul>
            </section>

            <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg" aria-label="Matrículas encontradas">
                <h3 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">Matrículas ({{ matriculas.length }})</h3>
                <ul class="divide-y">
                    <li v-for="m in matriculas" :key="m.id" class="px-4 py-2 text-sm">
                        <Link :href="route('matriculas.show', m.id)" class="font-medium text-[#1E3A8A] hover:underline">#{{ m.id }} · {{ m.estudiante }}</Link>
                        <span class="text-gray-500"> · {{ m.seccion }} · {{ m.periodo }}</span>
                    </li>
                    <li v-if="!matriculas.length" class="px-4 py-3 text-sm text-gray-500">Sin coincidencias.</li>
                </ul>
            </section>

            <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg" aria-label="Documentos encontrados">
                <h3 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">Documentos ({{ documentos.length }})</h3>
                <ul class="divide-y">
                    <li v-for="d in documentos" :key="d.id" class="px-4 py-2 text-sm">
                        <span class="font-medium">{{ d.numero || ('#' + d.id) }}</span>
                        <span class="text-gray-500"> · {{ d.asunto }}</span>
                    </li>
                    <li v-if="!documentos.length" class="px-4 py-3 text-sm text-gray-500">Sin coincidencias.</li>
                </ul>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

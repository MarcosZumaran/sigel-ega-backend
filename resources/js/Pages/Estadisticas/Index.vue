<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    periodo_activo: Object,
    totales: { type: Object, default: () => ({}) },
    matriculas_por_nivel: { type: Array, default: () => [] },
    asistencia_hoy: { type: Object, default: () => ({}) },
    logros: { type: Object, default: () => ({}) },
    secciones: { type: Array, default: () => [] },
});

const crumbs = [{ label: 'Estadísticas' }];

const totalMatriculas = computed(() =>
    (props.matriculas_por_nivel ?? []).reduce((s, n) => s + (n.matriculas_count ?? 0), 0),
);

const totalAsistencia = computed(() =>
    ['presente', 'ausente', 'tardia', 'justificado'].reduce(
        (s, k) => s + (props.asistencia_hoy?.[k] ?? 0), 0,
    ),
);

function pct(n, total) {
    if (!total) return 0;
    return Math.round((n / total) * 100);
}

const logroOrden = ['AD', 'A', 'B', 'C'];
const logroColor = { AD: 'bg-emerald-600', A: 'bg-blue-700', B: 'bg-amber-500', C: 'bg-red-700' };

const asistenciaItems = [
    { key: 'presente', label: 'Presentes', dot: 'bg-emerald-600' },
    { key: 'tardia', label: 'Tardanzas', dot: 'bg-amber-500' },
    { key: 'ausente', label: 'Ausentes', dot: 'bg-red-700' },
    { key: 'justificado', label: 'Justificados', dot: 'bg-sky-600' },
];
</script>

<template>
    <Head title="Estadísticas" />

    <AuthenticatedLayout :crumbs="crumbs">
        <template #header>
            <h1 class="text-xl font-semibold leading-tight text-gray-800">Estadísticas</h1>
            <p class="mt-1 text-sm text-gray-500">
                Periodo activo: {{ periodo_activo ? `${periodo_activo.nombre} (${periodo_activo.anio})` : 'sin periodo activo' }}
            </p>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Totales -->
                <section aria-label="Totales generales" class="grid grid-cols-2 gap-4 md:grid-cols-3">
                    <div class="rounded-xl bg-[#1E3A8A] px-4 py-5 text-white shadow">
                        <p class="text-3xl font-bold">{{ totales?.estudiantes ?? 0 }}</p>
                        <p class="mt-1 text-xs uppercase tracking-wide opacity-80">Estudiantes</p>
                    </div>
                    <div class="rounded-xl bg-[#0E6B3A] px-4 py-5 text-white shadow">
                        <p class="text-3xl font-bold">{{ totales?.matriculas ?? 0 }}</p>
                        <p class="mt-1 text-xs uppercase tracking-wide opacity-80">Matrículas (periodo)</p>
                    </div>
                    <div class="rounded-xl bg-slate-700 px-4 py-5 text-white shadow">
                        <p class="text-3xl font-bold">{{ totales?.secciones ?? 0 }}</p>
                        <p class="mt-1 text-xs uppercase tracking-wide opacity-80">Secciones</p>
                    </div>
                </section>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Matrícula por nivel -->
                    <section aria-label="Matrícula por nivel" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <h2 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">
                            Matrícula por nivel
                        </h2>
                        <ul class="divide-y">
                            <li v-for="n in (matriculas_por_nivel ?? [])" :key="n.id" class="px-4 py-3">
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium">{{ n.nombre }}</span>
                                    <span class="font-bold text-[#1E3A8A]">{{ n.matriculas_count ?? 0 }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded bg-gray-200" role="img" :aria-label="`${n.nombre}: ${n.matriculas_count ?? 0} matrículas`">
                                    <div class="h-2 rounded bg-[#1E3A8A]" :style="{ width: pct(n.matriculas_count ?? 0, totalMatriculas || 1) + '%' }" />
                                </div>
                            </li>
                            <li v-if="!(matriculas_por_nivel ?? []).length" class="px-4 py-4 text-sm text-gray-500">
                                Sin datos de matrícula.
                            </li>
                        </ul>
                    </section>

                    <!-- Logros CNEB -->
                    <section aria-label="Distribución de logros" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <h2 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">
                            Logros CNEB (AD / A / B / C)
                        </h2>
                        <ul class="space-y-3 px-4 py-4">
                            <li v-for="k in logroOrden" :key="k">
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full font-bold text-white" :class="logroColor[k]">{{ k }}</span>
                                    <span class="font-bold">{{ logros?.[k] ?? 0 }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded bg-gray-200" role="img" :aria-label="`Nivel ${k}: ${logros?.[k] ?? 0}`">
                                    <div class="h-2 rounded" :class="logroColor[k]" :style="{ width: pct(logros?.[k] ?? 0, (logros?.AD ?? 0) + (logros?.A ?? 0) + (logros?.B ?? 0) + (logros?.C ?? 0) || 1) + '%' }" />
                                </div>
                            </li>
                        </ul>
                    </section>

                    <!-- Asistencia hoy -->
                    <section aria-label="Asistencia de hoy" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <h2 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">
                            Asistencia de hoy ({{ totalAsistencia }} registros)
                        </h2>
                        <ul class="divide-y">
                            <li v-for="a in asistenciaItems" :key="a.key" class="flex items-center justify-between px-4 py-2.5 text-sm">
                                <span class="flex items-center gap-2">
                                    <span class="inline-block h-3 w-3 rounded-full" :class="a.dot" aria-hidden="true" />
                                    {{ a.label }}
                                </span>
                                <span class="font-bold">{{ asistencia_hoy?.[a.key] ?? 0 }}</span>
                            </li>
                        </ul>
                    </section>

                    <!-- Ocupación por sección -->
                    <section aria-label="Ocupación por sección" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <h2 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">
                            Ocupación por sección
                        </h2>
                        <div class="max-h-72 overflow-y-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="sticky top-0 bg-gray-50">
                                    <tr class="border-b text-xs uppercase text-gray-500">
                                        <th scope="col" class="px-4 py-2">Sección</th>
                                        <th scope="col" class="px-4 py-2 text-center">Ocupadas</th>
                                        <th scope="col" class="px-4 py-2 text-center">Vacantes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="s in (secciones ?? [])" :key="s.id" class="border-b">
                                        <td class="px-4 py-2">{{ s.grado?.nombre ?? '—' }} “{{ s.nombre }}”</td>
                                        <td class="px-4 py-2 text-center font-bold">{{ s.matriculas_count ?? 0 }}</td>
                                        <td class="px-4 py-2 text-center">{{ s.vacantes ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-if="!(secciones ?? []).length" class="px-4 py-4 text-sm text-gray-500">
                                Sin secciones.
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

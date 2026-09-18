<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    kpis: { type: Object, default: () => ({}) },
    periodo_activo: { type: Object, default: null },
    matriculas_hoy: { type: Number, default: 0 },
});

const cards = [
    { label: 'Estudiantes', key: 'estudiantes', bg: 'bg-blue-800', href: 'estudiantes.index' },
    { label: 'Padres', key: 'padres', bg: 'bg-sky-700', href: 'padres.index' },
    { label: 'Apoderados (hub)', key: 'apoderados', bg: 'bg-indigo-700', href: 'apoderados.index' },
    { label: 'Matrículas', key: 'matriculas', bg: 'bg-emerald-700', href: 'matriculas.index' },
    { label: 'Docentes', key: 'docentes', bg: 'bg-amber-600', href: null },
    { label: 'Asistencias hoy', key: 'asistencias_hoy', bg: 'bg-slate-700', href: 'asistencias.index' },
];

const crumbs = [{ label: 'Panel' }];
</script>

<template>
    <Head title="Panel principal" />

    <AuthenticatedLayout :crumbs="crumbs">
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Panel principal</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Periodo activo:
                    <strong v-if="periodo_activo">{{ periodo_activo.nombre }} ({{ periodo_activo.anio }})</strong>
                    <span v-else class="text-amber-700">sin periodo activo — actívelo en Periodos</span>
                    · Matrículas registradas hoy: <strong>{{ matriculas_hoy }}</strong>
                </p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <p class="mb-4 text-gray-700">
                    Bienvenido, <strong>{{ $page.props.auth.user?.name }}</strong>
                    ({{ $page.props.auth.user?.email }})
                </p>

                <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6" role="list" aria-label="Indicadores">
                    <component
                        :is="c.href ? Link : 'div'"
                        v-for="c in cards"
                        :key="c.key"
                        :href="c.href ? route(c.href) : undefined"
                        role="listitem"
                        :class="['block rounded-xl px-4 py-5 text-white shadow no-underline', c.bg]"
                    >
                        <p class="text-3xl font-bold">{{ kpis?.[c.key] ?? 0 }}</p>
                        <p class="mt-1 text-xs uppercase tracking-wide opacity-80">{{ c.label }}</p>
                    </component>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg" aria-labelledby="t-rapido">
                        <div class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white" id="t-rapido">
                            Acceso rápido
                        </div>
                        <nav class="flex flex-wrap gap-3 p-4" aria-label="Accesos directos">
                            <Link :href="route('matriculas.create')" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white no-underline hover:bg-emerald-800 min-h-[44px] inline-flex items-center">
                                Nueva matrícula
                            </Link>
                            <Link :href="route('apoderados.index')" class="rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm font-medium text-white no-underline hover:bg-blue-900 min-h-[44px] inline-flex items-center">
                                Apoderados (hub)
                            </Link>
                            <Link :href="route('estadisticas')" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white no-underline hover:bg-slate-800 min-h-[44px] inline-flex items-center">
                                Estadísticas
                            </Link>
                            <Link :href="route('buscar')" class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-[#1E3A8A] no-underline ring-1 ring-inset ring-[#1E3A8A] hover:bg-blue-50 min-h-[44px] inline-flex items-center">
                                Búsqueda global
                            </Link>
                        </nav>
                    </section>

                    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg" aria-labelledby="t-pend">
                        <div class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white" id="t-pend">
                            Pendientes del periodo
                        </div>
                        <ul class="divide-y text-sm">
                            <li class="flex items-center justify-between px-4 py-3">
                                <span>Activar el periodo lectivo vigente</span>
                                <Link :href="route('periodos.index')" class="font-medium text-[#1E3A8A] hover:underline">Ir a Periodos</Link>
                            </li>
                            <li class="flex items-center justify-between px-4 py-3">
                                <span>Revisar grados y secciones con vacantes</span>
                                <Link :href="route('secciones.index')" class="font-medium text-[#1E3A8A] hover:underline">Ir a Secciones</Link>
                            </li>
                            <li class="flex items-center justify-between px-4 py-3">
                                <span>Generar reportes del periodo</span>
                                <Link :href="route('reportes.index')" class="font-medium text-[#1E3A8A] hover:underline">Ir a Reportes</Link>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

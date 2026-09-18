<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({ notas: Object, filtros: Object, areas: Array });
const f = reactive({ buscar: props.filtros?.buscar ?? '', area_id: props.filtros?.area_id ?? '' });

function buscar() {
    router.get(route('notas.index'), { buscar: f.buscar || undefined, area_id: f.area_id || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Notas" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="titulo" class="text-xl font-semibold text-gray-800">Notas</h2>
                <Link :href="route('notas.create')" class="inline-flex min-h-[44px] items-center rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Nueva nota</Link>
            </div>
        </template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-2 text-green-900">{{ $page.props.flash.success }}</div>
            <form @submit.prevent="buscar" role="search" aria-label="Buscar y filtrar notas" class="mb-4 flex flex-wrap gap-2">
                <label for="buscar" class="sr-only">Buscar estudiante por DNI o nombres</label>
                <input id="buscar" v-model="f.buscar" type="search" placeholder="DNI o nombres…" class="min-h-[44px] w-full max-w-xs rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                <label for="area_id" class="sr-only">Filtrar por área</label>
                <select id="area_id" v-model="f.area_id" class="min-h-[44px] rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                    <option value="">Todas las áreas</option>
                    <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                </select>
                <button type="submit" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Filtrar</button>
            </form>
            <div class="overflow-x-auto rounded-lg border bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Listado de calificaciones</caption>
                    <thead class="bg-[#1E3A8A] text-white">
                        <tr><th scope="col" class="px-4 py-3">Estudiante</th><th scope="col" class="px-4 py-3">Área</th><th scope="col" class="px-4 py-3">Evaluación</th><th scope="col" class="px-4 py-3">Nota / Nivel</th><th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="n in notas.data" :key="n.id" class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">{{ n.matricula?.estudiante?.nombres }} {{ n.matricula?.estudiante?.apellidos }}</td>
                            <td class="px-4 py-3">{{ n.area?.nombre ?? '—' }}</td>
                            <td class="px-4 py-3">{{ n.tipo_evaluacion?.nombre ?? '—' }}</td>
                            <td class="px-4 py-3 font-semibold">{{ n.nota ?? n.nivel_logro ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('notas.show', n.id)" class="mr-3 font-medium text-[#1E3A8A] underline-offset-2 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#1E3A8A]">Ver</Link>
                                <Link :href="route('notas.edit', n.id)" class="font-medium text-[#1E3A8A] underline-offset-2 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#1E3A8A]">Editar</Link>
                            </td>
                        </tr>
                        <tr v-if="!notas.data.length"><td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin resultados.</td></tr>
                    </tbody>
                </table>
            </div>
            <nav v-if="notas.last_page > 1" aria-label="Paginación" class="mt-4 flex items-center gap-3 text-sm">
                <Link v-if="notas.prev_page_url" :href="notas.prev_page_url" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-[#1E3A8A] hover:bg-gray-50">Anterior</Link>
                <span aria-current="page">Página {{ notas.current_page }} de {{ notas.last_page }}</span>
                <Link v-if="notas.next_page_url" :href="notas.next_page_url" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-[#1E3A8A] hover:bg-gray-50">Siguiente</Link>
            </nav>
        </main>
    </AuthenticatedLayout>
</template>

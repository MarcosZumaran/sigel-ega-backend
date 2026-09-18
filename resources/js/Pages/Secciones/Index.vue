<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({ secciones: Object, grados: Array, filtros: Object, crumbs: Array });

const f = reactive({ grado_id: props.filtros?.grado_id ?? '', turno: props.filtros?.turno ?? '' });

function filtrar() {
    router.get(route('secciones.index'), { grado_id: f.grado_id || undefined, turno: f.turno || undefined }, { preserveState: true });
}

function limpiar() {
    f.grado_id = '';
    f.turno = '';
    router.get(route('secciones.index'));
}
</script>

<template>
    <Head title="Secciones" />
    <AuthenticatedLayout :crumbs="crumbs">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:bg-white focus:p-2 focus:text-blue-900">
            Saltar al contenido
        </a>
        <div id="contenido" class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-[#1E3A8A]">Secciones</h2>
                    <Link :href="route('secciones.create')" class="min-h-[44px] inline-flex items-center rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                        + Nueva sección
                    </Link>
                </div>
                <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <form class="mb-4 flex flex-wrap items-end gap-3 bg-white p-4 shadow-sm sm:rounded-lg" @submit.prevent="filtrar" role="search" aria-label="Filtrar secciones">
                    <div>
                        <label for="f-grado" class="mb-1 block text-sm font-medium text-gray-700">Grado</label>
                        <select id="f-grado" v-model="f.grado_id" class="rounded-lg border-gray-300 text-sm">
                            <option value="">Todos</option>
                            <option v-for="g in (grados ?? [])" :key="g.id" :value="g.id">{{ g.nivel?.nombre }} — {{ g.nombre }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="f-turno" class="mb-1 block text-sm font-medium text-gray-700">Turno</label>
                        <select id="f-turno" v-model="f.turno" class="rounded-lg border-gray-300 text-sm">
                            <option value="">Todos</option>
                            <option value="manana">Mañana</option>
                            <option value="tarde">Tarde</option>
                        </select>
                    </div>
                    <button type="submit" class="min-h-[44px] rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm text-white hover:bg-blue-900">Filtrar</button>
                    <button type="button" class="min-h-[44px] rounded-lg bg-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-300" @click="limpiar">Limpiar</button>
                </form>
                <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
                    <table class="w-full text-left text-sm">
                        <caption class="sr-only">Listado de secciones con vacantes y docente</caption>
                        <thead>
                            <tr class="bg-[#1E3A8A] text-white">
                                <th scope="col" class="px-4 py-3">Grado</th>
                                <th scope="col" class="px-4 py-3">Sección</th>
                                <th scope="col" class="px-4 py-3">Turno</th>
                                <th scope="col" class="px-4 py-3 text-center">Vacantes</th>
                                <th scope="col" class="px-4 py-3">Docente</th>
                                <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in (secciones?.data ?? [])" :key="s.id" class="border-b hover:bg-slate-50">
                                <td class="px-4 py-2 text-gray-600">{{ s.grado?.nivel?.nombre }} — {{ s.grado?.nombre }}</td>
                                <td class="px-4 py-2 font-medium">“{{ s.nombre }}”</td>
                                <td class="px-4 py-2">{{ s.turno === 'manana' ? 'Mañana' : 'Tarde' }}</td>
                                <td class="px-4 py-2 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="(s.vacantes - (s.matriculas_count ?? 0)) > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'">
                                        {{ (s.matriculas_count ?? 0) }}/{{ s.vacantes }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-gray-600">{{ s.docente ? [s.docente.nombres, s.docente.apellidos].filter(Boolean).join(' ') : 'Sin asignar' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <Link :href="route('secciones.show', s.id)" class="text-sm font-medium text-[#1E3A8A] hover:underline">Ver</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!(secciones?.data ?? []).length" class="px-4 py-6 text-center text-sm text-gray-500">Sin secciones.</p>
                </div>
                <nav v-if="secciones?.links?.length > 3" class="mt-4 flex gap-2" aria-label="Paginación">
                    <Link v-for="l in secciones.links" :key="l.label" :href="l.url ?? '#'" v-html="l.label"
                        :class="['rounded-lg px-3 py-2 text-sm', l.active ? 'bg-[#1E3A8A] text-white' : 'bg-white text-gray-700 hover:bg-gray-100']" />
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

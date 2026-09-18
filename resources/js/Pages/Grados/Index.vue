<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ grados: Object, crumbs: Array });

function nivelNombre(g) {
    return g.nivel?.nombre ?? '—';
}
</script>

<template>
    <Head title="Grados" />
    <AuthenticatedLayout :crumbs="crumbs">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:bg-white focus:p-2 focus:text-blue-900">
            Saltar al contenido
        </a>
        <div id="contenido" class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-[#1E3A8A]">Grados</h2>
                    <Link :href="route('grados.create')" class="min-h-[44px] inline-flex items-center rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                        + Nuevo grado
                    </Link>
                </div>
                <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.errors?.grado" role="alert" class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">
                    {{ $page.props.errors.grado }}
                </div>
                <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
                    <table class="w-full text-left text-sm">
                        <caption class="sr-only">Listado de grados por nivel</caption>
                        <thead>
                            <tr class="bg-[#1E3A8A] text-white">
                                <th scope="col" class="px-4 py-3">Nivel</th>
                                <th scope="col" class="px-4 py-3">Grado</th>
                                <th scope="col" class="px-4 py-3 text-center">Secciones</th>
                                <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="g in (grados?.data ?? [])" :key="g.id" class="border-b hover:bg-slate-50">
                                <td class="px-4 py-2 text-gray-600">{{ nivelNombre(g) }}</td>
                                <td class="px-4 py-2 font-medium">{{ g.nombre }}</td>
                                <td class="px-4 py-2 text-center">
                                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">{{ g.secciones_count ?? 0 }}</span>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <Link :href="route('grados.show', g.id)" class="text-sm font-medium text-[#1E3A8A] hover:underline">Ver</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="!(grados?.data ?? []).length" class="px-4 py-6 text-center text-sm text-gray-500">Sin grados registrados.</p>
                </div>
                <nav v-if="grados?.links?.length > 3" class="mt-4 flex gap-2" aria-label="Paginación">
                    <Link v-for="l in grados.links" :key="l.label" :href="l.url ?? '#'" v-html="l.label"
                        :class="['rounded-lg px-3 py-2 text-sm', l.active ? 'bg-[#1E3A8A] text-white' : 'bg-white text-gray-700 hover:bg-gray-100']" />
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

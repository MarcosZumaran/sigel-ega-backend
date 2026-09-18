<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({ padres: Object, filtros: Object });

const f = reactive({ buscar: props.filtros?.buscar ?? '' });

function buscar() {
    router.get(route('padres.index'), { buscar: f.buscar || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Padres" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="titulo" class="text-xl font-semibold text-gray-800">Padres</h2>
                <Link :href="route('padres.create')" class="inline-flex min-h-[44px] items-center rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Nuevo padre</Link>
            </div>
        </template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-2 text-green-900">{{ $page.props.flash.success }}</div>
            <form @submit.prevent="buscar" role="search" aria-label="Buscar padres" class="mb-4 flex flex-wrap gap-2">
                <label for="buscar" class="sr-only">Buscar por DNI, nombres o apellidos</label>
                <input id="buscar" v-model="f.buscar" type="search" placeholder="DNI, nombres o apellidos…" class="min-h-[44px] w-full max-w-sm rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                <button type="submit" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Buscar</button>
            </form>
            <div class="overflow-x-auto rounded-lg border bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Listado de padres con su apoderado asignado</caption>
                    <thead class="bg-[#1E3A8A] text-white">
                        <tr><th scope="col" class="px-4 py-3">DNI</th><th scope="col" class="px-4 py-3">Nombres</th><th scope="col" class="px-4 py-3">Apellidos</th><th scope="col" class="px-4 py-3">Apoderado</th><th scope="col" class="px-4 py-3"><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in padres.data" :key="p.id" class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">{{ p.dni }}</td>
                            <td class="px-4 py-3">{{ p.nombres }}</td>
                            <td class="px-4 py-3">{{ p.apellidos }}</td>
                            <td class="px-4 py-3">{{ p.apoderado_id ? '#' + p.apoderado_id : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('padres.show', p.id)" class="mr-3 font-medium text-[#1E3A8A] underline-offset-2 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#1E3A8A]">Ver</Link>
                                <Link :href="route('padres.edit', p.id)" class="font-medium text-[#1E3A8A] underline-offset-2 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#1E3A8A]">Editar</Link>
                            </td>
                        </tr>
                        <tr v-if="!padres.data.length"><td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin resultados.</td></tr>
                    </tbody>
                </table>
            </div>
            <nav v-if="padres.last_page > 1" aria-label="Paginación" class="mt-4 flex items-center gap-3 text-sm">
                <Link v-if="padres.prev_page_url" :href="padres.prev_page_url" class="min-h-[44px] inline-flex items-center rounded-md border px-4 py-2 text-[#1E3A8A] hover:bg-gray-50">Anterior</Link>
                <span aria-current="page">Página {{ padres.current_page }} de {{ padres.last_page }}</span>
                <Link v-if="padres.next_page_url" :href="padres.next_page_url" class="min-h-[44px] inline-flex items-center rounded-md border px-4 py-2 text-[#1E3A8A] hover:bg-gray-50">Siguiente</Link>
            </nav>
        </main>
    </AuthenticatedLayout>
</template>

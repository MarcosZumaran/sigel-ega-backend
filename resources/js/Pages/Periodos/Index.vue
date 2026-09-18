<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ periodos: Object });

function activar(id) {
    if (!confirm('¿Activar este periodo? Se desactivarán los demás.')) return;
    router.post(route('periodos.activar', id));
}

function promocionar(id) {
    if (!confirm('¿Ejecutar promoción? Los estudiantes avanzan al siguiente grado de su nivel. Esta acción no se puede deshacer.')) return;
    router.post(route('periodos.promocion', id));
}
</script>

<template>
    <Head title="Periodos" />
    <AuthenticatedLayout :crumbs="[{ label: 'Periodos' }]">
        <h1 class="text-2xl font-bold text-[#1E3A8A]">Periodos académicos</h1>
        <p class="mt-1 text-sm text-slate-600">
            Gestiona los años lectivos, el periodo activo y la promoción de estudiantes.
        </p>

        <p v-if="$page.props.flash?.success" role="status" class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ $page.props.flash.success }}
        </p>

        <div class="mt-4">
            <Link :href="route('periodos.create')" class="inline-flex min-h-[44px] items-center rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm font-medium text-white hover:bg-[#162c6b]">
                + Nuevo periodo
            </Link>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-[#1E3A8A] text-white">
                        <th scope="col" class="px-4 py-3">Nombre</th>
                        <th scope="col" class="px-4 py-3">Año</th>
                        <th scope="col" class="px-4 py-3">Vigencia</th>
                        <th scope="col" class="px-4 py-3 text-center">Estado</th>
                        <th scope="col" class="px-4 py-3 text-center">Matrículas</th>
                        <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in (periodos?.data ?? [])" :key="p.id" class="border-b last:border-0 hover:bg-slate-50">
                        <td class="px-4 py-2 font-medium">{{ p.nombre }}</td>
                        <td class="px-4 py-2">{{ p.anio }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ p.fecha_inicio ?? '—' }} → {{ p.fecha_fin ?? '—' }}</td>
                        <td class="px-4 py-2 text-center">
                            <span v-if="p.activo" class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Activo</span>
                            <span v-else class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Inactivo</span>
                        </td>
                        <td class="px-4 py-2 text-center">{{ p.matriculas_count ?? 0 }}</td>
                        <td class="px-4 py-2 text-right">
                            <Link v-if="p?.id" :href="route('periodos.show', { periodo: p.id })" class="mr-3 font-medium text-[#1E3A8A] hover:underline">Ver</Link>
                            <Link :href="route('periodos.edit', { periodo: p.id })" class="mr-3 font-medium text-[#1E3A8A] hover:underline">Editar</Link>
                            <button v-if="!p.activo" type="button" class="mr-3 font-medium text-emerald-700 hover:underline" @click="activar(p.id)">Activar</button>
                            <button v-if="p.activo" type="button" class="font-medium text-amber-700 hover:underline" @click="promocionar(p.id)">Promocionar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!(periodos?.data ?? []).length" class="px-4 py-6 text-center text-sm text-slate-500">Sin periodos registrados.</p>
        </div>

        <nav v-if="periodos?.links?.length > 3" class="mt-4 flex gap-2" aria-label="Paginación">
            <Link v-for="l in periodos.links" :key="l.label" :href="l.url ?? '#'" v-html="l.label"
                class="min-h-[44px] rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-[#1E3A8A] hover:bg-slate-100"
                :class="{ 'pointer-events-none opacity-50': !l.url }" />
        </nav>
    </AuthenticatedLayout>
</template>

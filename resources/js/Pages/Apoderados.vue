<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    apoderados: Array,
    padres: Array,
    estudiantes: Array,
});

const msg = ref('');
const err = ref('');

function eliminar(id) {
    if (!confirm('¿Eliminar el apoderado #' + id + '? Solo es posible si no tiene padres ni estudiantes.')) return;
    err.value = '';
    msg.value = '';
    router.delete(route('apoderados.destroy', id), {
        onSuccess: () => {
            msg.value = 'Apoderado eliminado';
        },
        onError: () => {
            err.value = 'No se puede eliminar: tiene padres o estudiantes asociados.';
        },
    });
}

function nombreCompleto(p) {
    return [p.nombres, p.apellidos].filter(Boolean).join(' ');
}
</script>

<template>
    <Head title="Apoderados" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Apoderados — Hub de relaciones</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Se crean automáticamente al registrar un padre. Sin datos personales: los datos viven en Padres y Estudiantes.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <p v-if="err" class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">{{ err }}</p>
                <p v-if="$page.props.flash?.success || msg" class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700">
                    {{ $page.props.flash?.success ?? msg }}
                </p>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b bg-slate-800 text-white">
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">UUID</th>
                                <th class="px-4 py-3 text-center">Padres</th>
                                <th class="px-4 py-3 text-center">Estudiantes</th>
                                <th class="px-4 py-3">Creado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in (apoderados ?? [])" :key="a.id" class="border-b hover:bg-slate-50">
                                <td class="px-4 py-2 font-medium">#{{ a.id }}</td>
                                <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ (a.uuid ?? '—').slice(0, 8) }}</td>
                                <td class="px-4 py-2 text-center">
                                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                        {{ a.padres_count ?? (a.padres?.length ?? 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                        {{ a.estudiantes_count ?? (a.estudiantes?.length ?? 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-gray-500">{{ a.created_at?.slice(0, 10) ?? '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <Link :href="route('apoderados.show', a.id)" class="mr-3 text-sm font-medium text-blue-700 hover:underline">
                                        Ver
                                    </Link>
                                    <button type="button" class="text-sm font-medium text-red-600 hover:underline" @click="eliminar(a.id)">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p v-if="!(apoderados ?? []).length" class="px-4 py-6 text-center text-sm text-gray-500">
                        Sin apoderados — crea el primero con el botón superior.
                    </p>
                </div>

                <p class="mt-4 text-xs text-gray-400">
                    Padres registrados: {{ (padres ?? []).length }} · Estudiantes registrados: {{ (estudiantes ?? []).length }}
                    <span v-if="(padres ?? []).length" class="ml-2">
                        Ej.: {{ nombreCompleto(padres[0]) }}
                    </span>
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

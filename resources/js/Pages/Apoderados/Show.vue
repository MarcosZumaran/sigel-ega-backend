<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    apoderado: Object,
    padres: Array,
    estudiantes: Array,
});

const msg = ref('');
const err = ref('');
const padreForm = useForm({ padre_id: '' });
const estudianteForm = useForm({ estudiante_id: '' });

const hubId = computed(() => props.apoderado?.id);

const padresLibres = computed(() => (props.padres ?? []).filter((p) => !p.apoderado_id));
const estudiantesLibres = computed(() => (props.estudiantes ?? []).filter((e) => !e.apoderado_id));

function nombreCompleto(p) {
    return [p.nombres, p.apellidos].filter(Boolean).join(' ');
}

function asignarPadre() {
    err.value = '';
    padreForm.post(route('apoderados.padres.attach', hubId.value), {
        onSuccess: () => {
            padreForm.reset();
            msg.value = 'Padre asignado';
        },
        onError: (e) => {
            err.value = Object.values(e).join(' | ');
        },
    });
}

function asignarEstudiante() {
    err.value = '';
    estudianteForm.post(route('apoderados.estudiantes.attach', hubId.value), {
        onSuccess: () => {
            estudianteForm.reset();
            msg.value = 'Estudiante asignado';
        },
        onError: (e) => {
            err.value = Object.values(e).join(' | ');
        },
    });
}

function desvincularPadre(padreId) {
    router.delete(route('apoderados.padres.detach', [hubId.value, padreId]), {
        onSuccess: () => {
            msg.value = 'Padre desvinculado';
        },
    });
}

function desvincularEstudiante(estudianteId) {
    router.delete(route('apoderados.estudiantes.detach', [hubId.value, estudianteId]), {
        onSuccess: () => {
            msg.value = 'Estudiante desvinculado';
        },
    });
}
</script>

<template>
    <Head :title="`Apoderado #${apoderado?.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Apoderado #{{ apoderado?.id }}
                    </h2>
                    <p class="mt-1 font-mono text-xs text-gray-500">{{ apoderado?.uuid }}</p>
                </div>
                <Link :href="route('apoderados.index')" class="text-sm font-medium text-blue-700 hover:underline">
                    ← Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 md:grid-cols-2 lg:px-8">
                <p v-if="err" class="rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700 md:col-span-2">{{ err }}</p>
                <p v-if="$page.props.flash?.success || msg" class="rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700 md:col-span-2">
                    {{ $page.props.flash?.success ?? msg }}
                </p>

                <!-- Padres -->
                <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b bg-slate-800 px-4 py-3 text-sm font-semibold text-white">
                        Padres vinculados ({{ apoderado?.padres?.length ?? 0 }})
                    </div>
                    <ul class="divide-y">
                        <li v-for="p in (apoderado?.padres ?? [])" :key="p.id" class="flex items-center justify-between px-4 py-2 text-sm">
                            <span>{{ nombreCompleto(p) }} <span class="text-gray-400">· {{ p.dni }}</span></span>
                            <button type="button" class="text-xs font-medium text-red-600 hover:underline" @click="desvincularPadre(p.id)">
                                Desvincular
                            </button>
                        </li>
                        <li v-if="!(apoderado?.padres ?? []).length" class="px-4 py-4 text-sm text-gray-500">
                            Sin padres vinculados.
                        </li>
                    </ul>
                    <form class="flex gap-2 border-t bg-gray-50 px-4 py-3" @submit.prevent="asignarPadre">
                        <select v-model="padreForm.padre_id" required class="flex-1 rounded-lg border-gray-300 text-sm">
                            <option value="" disabled>Seleccionar padre libre…</option>
                            <option v-for="p in padresLibres" :key="p.id" :value="p.id">
                                {{ nombreCompleto(p) }} ({{ p.dni }})
                            </option>
                        </select>
                        <button type="submit" :disabled="padreForm.processing" class="rounded-lg bg-blue-800 px-3 py-2 text-sm text-white hover:bg-blue-900 disabled:opacity-50">
                            Asignar
                        </button>
                    </form>
                </section>

                <!-- Estudiantes -->
                <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b bg-slate-800 px-4 py-3 text-sm font-semibold text-white">
                        Estudiantes vinculados ({{ apoderado?.estudiantes?.length ?? 0 }})
                    </div>
                    <ul class="divide-y">
                        <li v-for="e in (apoderado?.estudiantes ?? [])" :key="e.id" class="flex items-center justify-between px-4 py-2 text-sm">
                            <span>{{ nombreCompleto(e) }} <span class="text-gray-400">· {{ e.dni }}</span></span>
                            <button type="button" class="text-xs font-medium text-red-600 hover:underline" @click="desvincularEstudiante(e.id)">
                                Desvincular
                            </button>
                        </li>
                        <li v-if="!(apoderado?.estudiantes ?? []).length" class="px-4 py-4 text-sm text-gray-500">
                            Sin estudiantes vinculados.
                        </li>
                    </ul>
                    <form class="flex gap-2 border-t bg-gray-50 px-4 py-3" @submit.prevent="asignarEstudiante">
                        <select v-model="estudianteForm.estudiante_id" required class="flex-1 rounded-lg border-gray-300 text-sm">
                            <option value="" disabled>Seleccionar estudiante libre…</option>
                            <option v-for="e in estudiantesLibres" :key="e.id" :value="e.id">
                                {{ nombreCompleto(e) }} ({{ e.dni }})
                            </option>
                        </select>
                        <button type="submit" :disabled="estudianteForm.processing" class="rounded-lg bg-emerald-700 px-3 py-2 text-sm text-white hover:bg-emerald-800 disabled:opacity-50">
                            Asignar
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

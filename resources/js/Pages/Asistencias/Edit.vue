<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ asistencia: Object });
const form = useForm({ matricula_id: props.asistencia.matricula_id ?? '', fecha: props.asistencia.fecha ?? '', estado: props.asistencia.estado ?? 'presente' });

function guardar() {
    form.put(route('asistencias.update', props.asistencia.id));
}
</script>

<template>
    <Head title="Editar asistencia" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header><h2 id="titulo" class="text-xl font-semibold text-gray-800">Editar asistencia #{{ asistencia.id }}</h2></template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <form @submit.prevent="guardar" novalidate class="space-y-4 rounded-lg border bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="fecha" class="mb-1 block text-sm font-medium">Fecha</label>
                        <input id="fecha" v-model="form.fecha" type="date" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.fecha" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.fecha }}</p>
                    </div>
                    <div>
                        <label for="estado" class="mb-1 block text-sm font-medium">Estado</label>
                        <select id="estado" v-model="form.estado" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="presente">Presente</option><option value="ausente">Ausente</option><option value="tardia">Tardía</option><option value="justificado">Justificado</option>
                        </select>
                        <p v-if="form.errors.estado" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.estado }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Guardar cambios</button>
                    <Link :href="route('asistencias.show', asistencia.id)" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</Link>
                </div>
            </form>
        </main>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ matriculas: Array });
const form = useForm({ matricula_id: '', fecha: '', estado: 'presente' });

function guardar() {
    form.post(route('asistencias.store'));
}
</script>

<template>
    <Head title="Nueva asistencia" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header><h2 id="titulo" class="text-xl font-semibold text-gray-800">Nueva asistencia</h2></template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <form @submit.prevent="guardar" novalidate class="space-y-4 rounded-lg border bg-white p-6 shadow-sm">
                <div>
                    <label for="matricula_id" class="mb-1 block text-sm font-medium">Matrícula <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                    <select id="matricula_id" v-model="form.matricula_id" required class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                        <option value="" disabled>Seleccionar…</option>
                        <option v-for="m in matriculas" :key="m.id" :value="m.id">#{{ m.id }} — {{ m.estudiante?.nombres }} {{ m.estudiante?.apellidos }} ({{ m.estudiante?.dni }})</option>
                    </select>
                    <p v-if="form.errors.matricula_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.matricula_id }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="fecha" class="mb-1 block text-sm font-medium">Fecha <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                        <input id="fecha" v-model="form.fecha" type="date" required class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.fecha" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.fecha }}</p>
                    </div>
                    <div>
                        <label for="estado" class="mb-1 block text-sm font-medium">Estado <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                        <select id="estado" v-model="form.estado" required class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="presente">Presente</option><option value="ausente">Ausente</option><option value="tardia">Tardía</option><option value="justificado">Justificado</option>
                        </select>
                        <p v-if="form.errors.estado" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.estado }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Guardar asistencia</button>
                    <Link :href="route('asistencias.index')" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</Link>
                </div>
            </form>
        </main>
    </AuthenticatedLayout>
</template>

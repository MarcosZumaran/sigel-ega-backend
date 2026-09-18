<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ matricula: Object, estudiantes: Array, secciones: Array, periodos: Array, tipos: Array });
const form = useForm({ estudiante_id: props.matricula.estudiante_id ?? '', seccion_id: props.matricula.seccion_id ?? '', periodo_id: props.matricula.periodo_id ?? '', tipo_matricula_id: props.matricula.tipo_matricula_id ?? '', fecha: props.matricula.fecha ?? '', estado_id: props.matricula.estado_id ?? '', observaciones: props.matricula.observaciones ?? '' });

function guardar() {
    form.put(route('matriculas.update', props.matricula.id));
}
</script>

<template>
    <Head title="Editar matrícula" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header><h2 id="titulo" class="text-xl font-semibold text-gray-800">Editar matrícula #{{ matricula.id }}</h2></template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <form @submit.prevent="guardar" novalidate class="space-y-4 rounded-lg border bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="seccion_id" class="mb-1 block text-sm font-medium">Sección</label>
                        <select id="seccion_id" v-model="form.seccion_id" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="">—</option>
                            <option v-for="s in secciones" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                        </select>
                        <p v-if="form.errors.seccion_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.seccion_id }}</p>
                    </div>
                    <div>
                        <label for="periodo_id" class="mb-1 block text-sm font-medium">Periodo</label>
                        <select id="periodo_id" v-model="form.periodo_id" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="">—</option>
                            <option v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre }} {{ p.anio }}</option>
                        </select>
                        <p v-if="form.errors.periodo_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.periodo_id }}</p>
                    </div>
                    <div>
                        <label for="fecha" class="mb-1 block text-sm font-medium">Fecha</label>
                        <input id="fecha" v-model="form.fecha" type="date" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.fecha" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.fecha }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="observaciones" class="mb-1 block text-sm font-medium">Observaciones</label>
                        <textarea id="observaciones" v-model="form.observaciones" rows="3" class="w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]"></textarea>
                        <p v-if="form.errors.observaciones" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.observaciones }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Guardar cambios</button>
                    <Link :href="route('matriculas.show', matricula.id)" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</Link>
                </div>
            </form>
        </main>
    </AuthenticatedLayout>
</template>

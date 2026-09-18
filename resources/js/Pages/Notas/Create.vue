<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ matriculas: Array, areas: Array, tipos: Array });
const form = useForm({ matricula_id: '', area_id: '', tipo_evaluacion_id: '', nota: '', nivel_logro: '', escala: '', es_nota_c: false, motivo_nota_c: '' });

function guardar() {
    form.post(route('notas.store'));
}
</script>

<template>
    <Head title="Nueva nota" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header><h2 id="titulo" class="text-xl font-semibold text-gray-800">Nueva nota</h2></template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <form @submit.prevent="guardar" novalidate class="space-y-4 rounded-lg border bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="matricula_id" class="mb-1 block text-sm font-medium">Matrícula <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                        <select id="matricula_id" v-model="form.matricula_id" required class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="" disabled>Seleccionar…</option>
                            <option v-for="m in matriculas" :key="m.id" :value="m.id">#{{ m.id }} — {{ m.estudiante?.nombres }} {{ m.estudiante?.apellidos }} ({{ m.estudiante?.dni }})</option>
                        </select>
                        <p v-if="form.errors.matricula_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.matricula_id }}</p>
                    </div>
                    <div>
                        <label for="area_id" class="mb-1 block text-sm font-medium">Área <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                        <select id="area_id" v-model="form.area_id" required class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="" disabled>Seleccionar…</option>
                            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                        </select>
                        <p v-if="form.errors.area_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.area_id }}</p>
                    </div>
                    <div>
                        <label for="tipo_evaluacion_id" class="mb-1 block text-sm font-medium">Tipo de evaluación <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                        <select id="tipo_evaluacion_id" v-model="form.tipo_evaluacion_id" required class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="" disabled>Seleccionar…</option>
                            <option v-for="t in tipos" :key="t.id" :value="t.id">{{ t.nombre }}</option>
                        </select>
                        <p v-if="form.errors.tipo_evaluacion_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.tipo_evaluacion_id }}</p>
                    </div>
                    <div>
                        <label for="nota" class="mb-1 block text-sm font-medium">Nota (0–20)</label>
                        <input id="nota" v-model="form.nota" type="number" min="0" max="20" step="0.5" inputmode="decimal" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.nota" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.nota }}</p>
                    </div>
                    <div>
                        <label for="nivel_logro" class="mb-1 block text-sm font-medium">Nivel de logro</label>
                        <select id="nivel_logro" v-model="form.nivel_logro" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="">—</option><option value="AD">AD</option><option value="A">A</option><option value="B">B</option><option value="C">C</option>
                        </select>
                        <p v-if="form.errors.nivel_logro" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.nivel_logro }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="motivo_nota_c" class="mb-1 block text-sm font-medium">Motivo (obligatorio si nivel C)</label>
                        <input id="motivo_nota_c" v-model="form.motivo_nota_c" maxlength="255" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.motivo_nota_c" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.motivo_nota_c }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Guardar nota</button>
                    <Link :href="route('notas.index')" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</Link>
                </div>
            </form>
        </main>
    </AuthenticatedLayout>
</template>

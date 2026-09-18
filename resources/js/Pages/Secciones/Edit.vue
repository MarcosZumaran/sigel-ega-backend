<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ seccion: Object, grados: Array, docentes: Array, crumbs: Array });

const form = useForm({
    grado_id: props.seccion?.grado_id ?? '',
    nombre: props.seccion?.nombre ?? '',
    turno: props.seccion?.turno ?? 'manana',
    vacantes: props.seccion?.vacantes ?? 0,
    docente_id: props.seccion?.docente_id ?? '',
});

function guardar() {
    form.transform((d) => ({ ...d, docente_id: d.docente_id || null }))
        .put(route('secciones.update', props.seccion.id));
}
</script>

<template>
    <Head title="Editar sección" />
    <AuthenticatedLayout :crumbs="crumbs">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:bg-white focus:p-2 focus:text-blue-900">
            Saltar al contenido
        </a>
        <div id="contenido" class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <h2 class="mb-4 text-xl font-bold text-[#1E3A8A]">Editar sección</h2>
                <form class="bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="guardar" novalidate>
                    <div class="mb-4">
                        <label for="grado" class="mb-1 block text-sm font-medium text-gray-700">Grado *</label>
                        <select id="grado" v-model="form.grado_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-800 focus:ring-blue-800">
                            <option value="" disabled>Seleccionar grado…</option>
                            <option v-for="g in (grados ?? [])" :key="g.id" :value="g.id">{{ g.nivel?.nombre }} — {{ g.nombre }}</option>
                        </select>
                        <p v-if="form.errors.grado_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.grado_id }}</p>
                    </div>
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="mb-1 block text-sm font-medium text-gray-700">Nombre *</label>
                            <input id="nombre" v-model="form.nombre" type="text" required maxlength="10"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-800 focus:ring-blue-800" />
                            <p v-if="form.errors.nombre" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.nombre }}</p>
                        </div>
                        <div>
                            <label for="turno" class="mb-1 block text-sm font-medium text-gray-700">Turno *</label>
                            <select id="turno" v-model="form.turno" required class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-800 focus:ring-blue-800">
                                <option value="manana">Mañana</option>
                                <option value="tarde">Tarde</option>
                            </select>
                            <p v-if="form.errors.turno" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.turno }}</p>
                        </div>
                    </div>
                    <div class="mb-6 grid grid-cols-2 gap-4">
                        <div>
                            <label for="vacantes" class="mb-1 block text-sm font-medium text-gray-700">Vacantes *</label>
                            <input id="vacantes" v-model.number="form.vacantes" type="number" required min="0"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-800 focus:ring-blue-800" />
                            <p v-if="form.errors.vacantes" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.vacantes }}</p>
                        </div>
                        <div>
                            <label for="docente" class="mb-1 block text-sm font-medium text-gray-700">Docente tutor</label>
                            <select id="docente" v-model="form.docente_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-800 focus:ring-blue-800">
                                <option value="">Sin asignar</option>
                                <option v-for="d in (docentes ?? [])" :key="d.id" :value="d.id">{{ d.apellidos }}, {{ d.nombres }}</option>
                            </select>
                            <p v-if="form.errors.docente_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.docente_id }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 disabled:opacity-50">
                            Guardar
                        </button>
                        <Link :href="route('secciones.show', seccion.id)" class="min-h-[44px] inline-flex items-center rounded-lg bg-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-300">
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

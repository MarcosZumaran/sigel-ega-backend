<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ niveles: Array, crumbs: Array });

const form = useForm({ nivel_id: '', nombre: '' });

function guardar() {
    form.post(route('grados.store'));
}
</script>

<template>
    <Head title="Nuevo grado" />
    <AuthenticatedLayout :crumbs="crumbs">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:bg-white focus:p-2 focus:text-blue-900">
            Saltar al contenido
        </a>
        <div id="contenido" class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <h2 class="mb-4 text-xl font-bold text-[#1E3A8A]">Nuevo grado</h2>
                <form class="bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="guardar" novalidate>
                    <div class="mb-4">
                        <label for="nivel" class="mb-1 block text-sm font-medium text-gray-700">Nivel *</label>
                        <select id="nivel" v-model="form.nivel_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-800 focus:ring-blue-800">
                            <option value="" disabled>Seleccionar nivel…</option>
                            <option v-for="n in (niveles ?? [])" :key="n.id" :value="n.id">{{ n.nombre }}</option>
                        </select>
                        <p v-if="form.errors.nivel_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.nivel_id }}</p>
                    </div>
                    <div class="mb-6">
                        <label for="nombre" class="mb-1 block text-sm font-medium text-gray-700">Nombre del grado *</label>
                        <input id="nombre" v-model="form.nombre" type="text" required maxlength="100" placeholder="Ej.: 1ro de Secundaria"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-800 focus:ring-blue-800" />
                        <p v-if="form.errors.nombre" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.nombre }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 disabled:opacity-50">
                            Guardar
                        </button>
                        <Link :href="route('grados.index')" class="min-h-[44px] inline-flex items-center rounded-lg bg-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-300">
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ periodo: Object });
const form = useForm({
    nombre: props.periodo?.nombre ?? '',
    anio: props.periodo?.anio ?? '',
    fecha_inicio: props.periodo?.fecha_inicio ?? '',
    fecha_fin: props.periodo?.fecha_fin ?? '',
});

function guardar() {
    form.put(route('periodos.update', props.periodo.id));
}
</script>

<template>
    <Head title="Editar periodo" />
    <AuthenticatedLayout :crumbs="[{ label: 'Periodos', href: route('periodos.index') }, { label: 'Editar' }]">
        <h1 class="text-2xl font-bold text-[#1E3A8A]">Editar periodo — {{ periodo?.nombre }}</h1>

        <form class="mt-4 max-w-xl rounded-xl bg-white p-6 shadow" novalidate @submit.prevent="guardar">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="f-nombre" class="mb-1 block text-sm font-medium text-slate-700">Nombre *</label>
                    <input id="f-nombre" v-model="form.nombre" type="text" required maxlength="50"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                    <p v-if="form.errors.nombre" role="alert" class="mt-1 text-xs text-[#C8102E]">{{ form.errors.nombre }}</p>
                </div>
                <div>
                    <label for="f-anio" class="mb-1 block text-sm font-medium text-slate-700">Año *</label>
                    <input id="f-anio" v-model="form.anio" type="number" required min="2000" max="2100"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                    <p v-if="form.errors.anio" role="alert" class="mt-1 text-xs text-[#C8102E]">{{ form.errors.anio }}</p>
                </div>
                <div>
                    <label for="f-ini" class="mb-1 block text-sm font-medium text-slate-700">Fecha inicio</label>
                    <input id="f-ini" v-model="form.fecha_inicio" type="date"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                    <p v-if="form.errors.fecha_inicio" role="alert" class="mt-1 text-xs text-[#C8102E]">{{ form.errors.fecha_inicio }}</p>
                </div>
                <div>
                    <label for="f-fin" class="mb-1 block text-sm font-medium text-slate-700">Fecha fin</label>
                    <input id="f-fin" v-model="form.fecha_fin" type="date"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                    <p v-if="form.errors.fecha_fin" role="alert" class="mt-1 text-xs text-[#C8102E]">{{ form.errors.fecha_fin }}</p>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-lg bg-[#1E3A8A] px-5 py-2 text-sm font-medium text-white hover:bg-[#162c6b] disabled:opacity-50">
                    Actualizar
                </button>
                <Link :href="route('periodos.index')" class="inline-flex min-h-[44px] items-center rounded-lg border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancelar
                </Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

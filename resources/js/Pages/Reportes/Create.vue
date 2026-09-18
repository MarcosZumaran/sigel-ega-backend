<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ tipos: Array, periodos: Array, secciones: Array });

const form = useForm({ tipo: 'matriculas', periodo_id: '', seccion_id: '', formato: 'pdf' });

function generar() {
    form.post(route('reportes.store'));
}
</script>

<template>
    <Head title="Generar reporte" />
    <AuthenticatedLayout :crumbs="[{ label: 'Reportes', href: route('reportes.index') }, { label: 'Generar' }]">
        <h1 class="text-2xl font-bold text-[#1E3A8A]">Generar reporte</h1>
        <p class="mt-1 text-sm text-slate-600">El archivo se genera en el periodo y la sección indicados.</p>

        <form class="mt-4 max-w-xl rounded-xl bg-white p-6 shadow" novalidate @submit.prevent="generar">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="f-tipo" class="mb-1 block text-sm font-medium text-slate-700">Tipo *</label>
                    <select id="f-tipo" v-model="form.tipo" required class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                        <option v-for="t in (tipos ?? [])" :key="t" :value="t">{{ t }}</option>
                    </select>
                    <p v-if="form.errors.tipo" role="alert" class="mt-1 text-xs text-[#C8102E]">{{ form.errors.tipo }}</p>
                </div>
                <div>
                    <label for="f-for" class="mb-1 block text-sm font-medium text-slate-700">Formato</label>
                    <select id="f-for" v-model="form.formato" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                    <p v-if="form.errors.formato" role="alert" class="mt-1 text-xs text-[#C8102E]">{{ form.errors.formato }}</p>
                </div>
                <div>
                    <label for="f-per" class="mb-1 block text-sm font-medium text-slate-700">Periodo</label>
                    <select id="f-per" v-model="form.periodo_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                        <option value="">General</option>
                        <option v-for="p in (periodos ?? [])" :key="p.id" :value="p.id">{{ p.nombre }}{{ p.activo ? ' (activo)' : '' }}</option>
                    </select>
                    <p v-if="form.errors.periodo_id" role="alert" class="mt-1 text-xs text-[#C8102E]">{{ form.errors.periodo_id }}</p>
                </div>
                <div>
                    <label for="f-sec" class="mb-1 block text-sm font-medium text-slate-700">Sección</label>
                    <select id="f-sec" v-model="form.seccion_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                        <option value="">Todas</option>
                        <option v-for="s in (secciones ?? [])" :key="s.id" :value="s.id">{{ s.grado?.nombre }} {{ s.nombre }}</option>
                    </select>
                    <p v-if="form.errors.seccion_id" role="alert" class="mt-1 text-xs text-[#C8102E]">{{ form.errors.seccion_id }}</p>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-lg bg-[#1E3A8A] px-5 py-2 text-sm font-medium text-white hover:bg-[#162c6b] disabled:opacity-50">
                    Generar
                </button>
                <Link :href="route('reportes.index')" class="inline-flex min-h-[44px] items-center rounded-lg border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancelar
                </Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

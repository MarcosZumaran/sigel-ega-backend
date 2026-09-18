<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ grado: Object, crumbs: Array });

function eliminar() {
    if (!confirm('¿Eliminar este grado? Solo es posible si no tiene secciones.')) return;
    router.delete(route('grados.destroy', props.grado.id));
}

function nombreDocente(d) {
    return d ? [d.nombres, d.apellidos].filter(Boolean).join(' ') : 'Sin asignar';
}
</script>

<template>
    <Head :title="`Grado ${grado?.nombre}`" />
    <AuthenticatedLayout :crumbs="crumbs">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:bg-white focus:p-2 focus:text-blue-900">
            Saltar al contenido
        </a>
        <div id="contenido" class="py-8">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-[#1E3A8A]">{{ grado?.nombre }}</h2>
                        <p class="text-sm text-gray-500">Nivel: {{ grado?.nivel?.nombre ?? '—' }} · {{ grado?.estudiantes_count ?? 0 }} estudiantes</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('grados.index')" class="min-h-[44px] inline-flex items-center text-sm font-medium text-[#1E3A8A] hover:underline">← Volver</Link>
                        <button type="button" class="min-h-[44px] rounded-lg bg-[#C8102E] px-4 py-2 text-sm text-white hover:bg-red-800" @click="eliminar">
                            Eliminar
                        </button>
                    </div>
                </div>
                <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.errors?.grado" role="alert" class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">
                    {{ $page.props.errors.grado }}
                </div>
                <section class="bg-white shadow-sm sm:rounded-lg" aria-label="Secciones del grado">
                    <h3 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">
                        Secciones ({{ grado?.secciones?.length ?? 0 }})
                    </h3>
                    <ul class="divide-y">
                        <li v-for="s in (grado?.secciones ?? [])" :key="s.id" class="flex items-center justify-between px-4 py-2 text-sm">
                            <span>Sección “{{ s.nombre }}” · Turno {{ s.turno === 'manana' ? 'mañana' : 'tarde' }} · {{ nombreDocente(s.docente) }}</span>
                            <Link :href="route('secciones.show', s.id)" class="font-medium text-[#1E3A8A] hover:underline">Ver</Link>
                        </li>
                        <li v-if="!(grado?.secciones ?? []).length" class="px-4 py-4 text-sm text-gray-500">Sin secciones.</li>
                    </ul>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ seccion: Object, ocupadas: Number, matriculas: Object, crumbs: Array });

function eliminar() {
    if (!confirm('¿Eliminar esta sección? Solo es posible si no tiene matrículas.')) return;
    router.delete(route('secciones.destroy', props.seccion.id));
}

function nombreCompleto(e) {
    return e ? [e.nombres, e.apellidos].filter(Boolean).join(' ') : '—';
}
</script>

<template>
    <Head title="Detalle de sección" />
    <AuthenticatedLayout :crumbs="crumbs">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:bg-white focus:p-2 focus:text-blue-900">
            Saltar al contenido
        </a>
        <div id="contenido" class="py-8">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-[#1E3A8A]">
                            {{ seccion?.grado?.nivel?.nombre }} — {{ seccion?.grado?.nombre }} “{{ seccion?.nombre }}”
                        </h2>
                        <p class="text-sm text-gray-500">
                            Turno {{ seccion?.turno === 'manana' ? 'mañana' : 'tarde' }} ·
                            {{ ocupadas ?? 0 }}/{{ seccion?.vacantes ?? 0 }} ocupadas ·
                            Tutor: {{ seccion?.docente ? nombreCompleto(seccion.docente) : 'Sin asignar' }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('secciones.edit', seccion.id)" class="min-h-[44px] inline-flex items-center rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm text-white hover:bg-blue-900">Editar</Link>
                        <button type="button" class="min-h-[44px] rounded-lg bg-[#C8102E] px-4 py-2 text-sm text-white hover:bg-red-800" @click="eliminar">Eliminar</button>
                    </div>
                </div>
                <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.errors?.seccion" role="alert" class="mb-4 rounded-lg bg-red-50 px-4 py-2 text-sm text-red-700">
                    {{ $page.props.errors.seccion }}
                </div>
                <section class="bg-white shadow-sm sm:rounded-lg" aria-label="Matrículas de la sección">
                    <h3 class="border-b bg-[#1E3A8A] px-4 py-3 text-sm font-semibold text-white">Matriculados</h3>
                    <ul class="divide-y">
                        <li v-for="m in (matriculas?.data ?? [])" :key="m.id" class="flex items-center justify-between px-4 py-2 text-sm">
                            <span>{{ nombreCompleto(m.estudiante) }} <span class="text-gray-400">· {{ m.estudiante?.dni }}</span></span>
                            <Link :href="route('matriculas.show', m.id)" class="font-medium text-[#1E3A8A] hover:underline">Ver matrícula</Link>
                        </li>
                        <li v-if="!(matriculas?.data ?? []).length" class="px-4 py-4 text-sm text-gray-500">Sin matrículas.</li>
                    </ul>
                </section>
                <nav v-if="matriculas?.links?.length > 3" class="mt-4 flex gap-2" aria-label="Paginación">
                    <Link v-for="l in matriculas.links" :key="l.label" :href="l.url ?? '#'" v-html="l.label"
                        :class="['rounded-lg px-3 py-2 text-sm', l.active ? 'bg-[#1E3A8A] text-white' : 'bg-white text-gray-700 hover:bg-gray-100']" />
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

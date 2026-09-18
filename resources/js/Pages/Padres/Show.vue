<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ padre: Object });

function eliminar(id) {
    if (confirm('¿Eliminar este padre?')) router.delete(route('padres.destroy', id));
}
</script>

<template>
    <Head :title="`Padre ${padre.dni}`" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="titulo" class="text-xl font-semibold text-gray-800">{{ padre.nombres }} {{ padre.apellidos }}</h2>
                <nav aria-label="Acciones del padre" class="flex gap-2">
                    <Link :href="route('padres.edit', padre.id)" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Editar</Link>
                    <button type="button" @click="eliminar(padre.id)" class="min-h-[44px] rounded-md bg-[#C8102E] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a50d26] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C8102E]">Eliminar</button>
                </nav>
            </div>
        </template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <div v-if="$page.props.flash?.success" role="status" class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-2 text-green-900">{{ $page.props.flash.success }}</div>
            <dl class="grid gap-3 rounded-lg border bg-white p-6 shadow-sm sm:grid-cols-2">
                <div><dt class="text-sm font-medium text-gray-500">DNI</dt><dd class="font-semibold">{{ padre.dni }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Teléfono</dt><dd>{{ padre.telefono ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Correo</dt><dd>{{ padre.email ?? '—' }}</dd></div>
                <div><dt class="text-sm font-medium text-gray-500">Ocupación</dt><dd>{{ padre.ocupacion ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Dirección</dt><dd>{{ padre.direccion ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500">Apoderado (hub auto-creado)</dt>
                    <dd><Link v-if="padre.apoderado_id" :href="route('apoderados.show', padre.apoderado_id)" class="font-semibold text-[#1E3A8A] hover:underline">#{{ padre.apoderado_id }}</Link><span v-else>—</span></dd>
                </div>
            </dl>
            <Link :href="route('padres.index')" class="mt-4 inline-block text-sm font-medium text-[#1E3A8A] hover:underline">← Volver al listado</Link>
        </main>
    </AuthenticatedLayout>
</template>

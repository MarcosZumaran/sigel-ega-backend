<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ padre: Object });
const form = useForm({ dni: props.padre.dni ?? '', nombres: props.padre.nombres ?? '', apellidos: props.padre.apellidos ?? '', telefono: props.padre.telefono ?? '', email: props.padre.email ?? '', direccion: props.padre.direccion ?? '', ocupacion: props.padre.ocupacion ?? '', apoderado_id: props.padre.apoderado_id ?? '' });

function guardar() {
    form.put(route('padres.update', props.padre.id));
}
</script>

<template>
    <Head :title="`Editar padre ${padre.dni}`" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header><h2 id="titulo" class="text-xl font-semibold text-gray-800">Editar padre</h2></template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <form @submit.prevent="guardar" novalidate class="space-y-4 rounded-lg border bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="dni" class="mb-1 block text-sm font-medium">DNI</label>
                        <input id="dni" v-model="form.dni" minlength="8" maxlength="8" inputmode="numeric" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.dni" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.dni }}</p>
                    </div>
                    <div>
                        <label for="ocupacion" class="mb-1 block text-sm font-medium">Ocupación</label>
                        <input id="ocupacion" v-model="form.ocupacion" maxlength="100" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.ocupacion" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.ocupacion }}</p>
                    </div>
                    <div>
                        <label for="nombres" class="mb-1 block text-sm font-medium">Nombres</label>
                        <input id="nombres" v-model="form.nombres" maxlength="150" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.nombres" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.nombres }}</p>
                    </div>
                    <div>
                        <label for="apellidos" class="mb-1 block text-sm font-medium">Apellidos</label>
                        <input id="apellidos" v-model="form.apellidos" maxlength="150" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.apellidos" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.apellidos }}</p>
                    </div>
                    <div>
                        <label for="telefono" class="mb-1 block text-sm font-medium">Teléfono</label>
                        <input id="telefono" v-model="form.telefono" type="tel" maxlength="20" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.telefono" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.telefono }}</p>
                    </div>
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium">Correo</label>
                        <input id="email" v-model="form.email" type="email" maxlength="100" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.email" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.email }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="direccion" class="mb-1 block text-sm font-medium">Dirección</label>
                        <input id="direccion" v-model="form.direccion" maxlength="200" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.direccion" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.direccion }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Guardar cambios</button>
                    <Link :href="route('padres.show', padre.id)" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</Link>
                </div>
            </form>
        </main>
    </AuthenticatedLayout>
</template>

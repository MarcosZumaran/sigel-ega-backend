<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ dni: '', nombres: '', apellidos: '', telefono: '', email: '', direccion: '', ocupacion: '', estado_id: '' });

function guardar() {
    form.post(route('padres.store'));
}
</script>

<template>
    <Head title="Nuevo padre" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header>
            <h2 id="titulo" class="text-xl font-semibold text-gray-800">Nuevo padre</h2>
        </template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <p class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-[#1E3A8A]">Al guardar se crea automáticamente su apoderado (hub) y se asigna.</p>
            <form @submit.prevent="guardar" novalidate class="space-y-4 rounded-lg border bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="dni" class="mb-1 block text-sm font-medium">DNI <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                        <input id="dni" v-model="form.dni" required minlength="8" maxlength="8" inputmode="numeric" autocomplete="off" aria-describedby="err-dni" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.dni" id="err-dni" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.dni }}</p>
                    </div>
                    <div>
                        <label for="ocupacion" class="mb-1 block text-sm font-medium">Ocupación</label>
                        <input id="ocupacion" v-model="form.ocupacion" maxlength="100" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.ocupacion" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.ocupacion }}</p>
                    </div>
                    <div>
                        <label for="nombres" class="mb-1 block text-sm font-medium">Nombres <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                        <input id="nombres" v-model="form.nombres" required maxlength="150" autocomplete="given-name" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.nombres" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.nombres }}</p>
                    </div>
                    <div>
                        <label for="apellidos" class="mb-1 block text-sm font-medium">Apellidos <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                        <input id="apellidos" v-model="form.apellidos" required maxlength="150" autocomplete="family-name" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.apellidos" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.apellidos }}</p>
                    </div>
                    <div>
                        <label for="telefono" class="mb-1 block text-sm font-medium">Teléfono</label>
                        <input id="telefono" v-model="form.telefono" type="tel" maxlength="20" autocomplete="tel" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.telefono" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.telefono }}</p>
                    </div>
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium">Correo</label>
                        <input id="email" v-model="form.email" type="email" maxlength="100" autocomplete="email" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.email" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.email }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="direccion" class="mb-1 block text-sm font-medium">Dirección</label>
                        <input id="direccion" v-model="form.direccion" maxlength="200" autocomplete="street-address" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.direccion" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.direccion }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Guardar padre</button>
                    <Link :href="route('padres.index')" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</Link>
                </div>
            </form>
        </main>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ padres: Array, niveles: Array });
const form = useForm({ codigo_estudiante: '', dni: '', nombres: '', apellidos: '', fecha_nacimiento: '', sexo: '', direccion: '', telefono: '', email: '', nivel_id: '', grado_id: '', estado_id: '', padre_id: '', apoderado_id: '' });

function guardar() {
    form.post(route('estudiantes.store'));
}
</script>

<template>
    <Head title="Nuevo estudiante" />
    <AuthenticatedLayout>
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>
        <template #header><h2 id="titulo" class="text-xl font-semibold text-gray-800">Nuevo estudiante</h2></template>
        <main id="contenido" aria-labelledby="titulo" class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <p class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-[#1E3A8A]">Selecciona el padre y el estudiante heredará automáticamente su apoderado.</p>
            <form @submit.prevent="guardar" novalidate class="space-y-4 rounded-lg border bg-white p-6 shadow-sm">
                <div>
                    <label for="padre_id" class="mb-1 block text-sm font-medium">Padre (hereda su apoderado) <span aria-hidden="true" class="text-[#C8102E]">*</span></label>
                    <select id="padre_id" v-model="form.padre_id" required class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                        <option value="" disabled>Seleccionar padre…</option>
                        <option v-for="p in padres" :key="p.id" :value="p.id">{{ p.nombres }} {{ p.apellidos }} — {{ p.dni }} (apod. #{{ p.apoderado_id ?? '—' }})</option>
                    </select>
                    <p v-if="form.errors.padre_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.padre_id }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="codigo_estudiante" class="mb-1 block text-sm font-medium">Código</label>
                        <input id="codigo_estudiante" v-model="form.codigo_estudiante" maxlength="30" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.codigo_estudiante" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.codigo_estudiante }}</p>
                    </div>
                    <div>
                        <label for="dni" class="mb-1 block text-sm font-medium">DNI</label>
                        <input id="dni" v-model="form.dni" minlength="8" maxlength="8" inputmode="numeric" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.dni" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.dni }}</p>
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
                        <label for="fecha_nacimiento" class="mb-1 block text-sm font-medium">Fecha de nacimiento</label>
                        <input id="fecha_nacimiento" v-model="form.fecha_nacimiento" type="date" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
                        <p v-if="form.errors.fecha_nacimiento" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.fecha_nacimiento }}</p>
                    </div>
                    <div>
                        <label for="sexo" class="mb-1 block text-sm font-medium">Sexo</label>
                        <select id="sexo" v-model="form.sexo" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="">—</option><option value="M">Masculino</option><option value="F">Femenino</option>
                        </select>
                        <p v-if="form.errors.sexo" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.sexo }}</p>
                    </div>
                    <div>
                        <label for="nivel_id" class="mb-1 block text-sm font-medium">Nivel</label>
                        <select id="nivel_id" v-model="form.nivel_id" class="min-h-[44px] w-full rounded-md border-gray-300 focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <option value="">—</option>
                            <option v-for="n in niveles" :key="n.id" :value="n.id">{{ n.nombre }}</option>
                        </select>
                        <p v-if="form.errors.nivel_id" role="alert" class="mt-1 text-sm text-[#C8102E]">{{ form.errors.nivel_id }}</p>
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
                    <button type="submit" :disabled="form.processing" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">Guardar estudiante</button>
                    <Link :href="route('estudiantes.index')" class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</Link>
                </div>
            </form>
        </main>
    </AuthenticatedLayout>
</template>

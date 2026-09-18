<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PersonSearch from '@/Components/PersonSearch.vue';
import VacancyIndicator from '@/Components/VacancyIndicator.vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    secciones: { type: Array, default: () => [] },
    periodos: { type: Array, default: () => [] },
    periodo_activo: { type: Object, default: null },
    tipos: { type: Array, default: () => [] },
    niveles: { type: Array, default: () => [] },
    grados: { type: Array, default: () => [] },
});

const crumbs = [{ label: 'Matrícula', href: route('matriculas.index') }, { label: 'Registro único' }];
const paso = ref(1);
const error = ref('');

// Paso 1: estudiante
const modoEst = ref('existente'); // existente | nuevo
const estExistente = ref(null);
const estNuevo = ref({ dni: '', nombres: '', apellidos: '', fecha_nacimiento: '', sexo: '', nivel_id: '', grado_id: '' });

// Paso 2: padre
const modoPadre = ref('existente'); // existente | nuevo | ninguno
const padreExistente = ref(null);
const padreNuevo = ref({ dni: '', nombres: '', apellidos: '', telefono: '' });

// Paso 3: matrícula
const mat = ref({
    periodo_id: props.periodo_activo?.id ?? '',
    seccion_id: '',
    tipo_matricula_id: '',
    fecha: new Date().toISOString().slice(0, 10),
    observaciones: '',
});

const form = useForm({});

const resumenEst = computed(() => modoEst.value === 'existente' ? estExistente.value : { ...estNuevo.value });
const resumenPadre = computed(() => modoPadre.value === 'existente' ? padreExistente.value : modoPadre.value === 'nuevo' ? { ...padreNuevo.value } : null);
const seccionElegida = computed(() => props.secciones.find((s) => String(s.id) === String(mat.value.seccion_id)));

function puedeAvanzar1() {
    if (modoEst.value === 'existente') return !!estExistente.value;
    return estNuevo.value.nombres.trim() !== '' && estNuevo.value.apellidos.trim() !== '';
}

function puedeAvanzar2() {
    if (modoPadre.value === 'existente') return !!padreExistente.value;
    if (modoPadre.value === 'nuevo') return padreNuevo.value.nombres.trim() !== '' && padreNuevo.value.apellidos.trim() !== '';
    return true;
}

function puedeRegistrar() {
    return mat.value.periodo_id && mat.value.seccion_id && mat.value.tipo_matricula_id;
}

function registrar() {
    error.value = '';
    const payload = {
        estudiante: modoEst.value === 'existente'
            ? { modo: 'existente', id: estExistente.value.id }
            : { modo: 'nuevo', ...estNuevo.value },
        padre: modoPadre.value === 'ninguno'
            ? { modo: 'ninguno' }
            : modoPadre.value === 'existente'
                ? { modo: 'existente', id: padreExistente.value.id }
                : { modo: 'nuevo', ...padreNuevo.value },
        matricula: { ...mat.value },
    };
    form.transform(() => payload).post(route('matriculas.registro'), {
        onError: (e) => {
            error.value = Object.values(e).flat().join(' | ');
            paso.value = 4;
        },
    });
}

const pasos = ['Estudiante', 'Padre / Apoderado', 'Matrícula', 'Confirmación'];
const stepError = ref('');

// Un paso futuro solo es visitable si todos los anteriores están completos.
function puedeVerPaso(n) {
    if (n <= 1) return true;
    if (!puedeAvanzar1()) return false;
    if (n >= 3 && !puedeAvanzar2()) return false;
    if (n >= 4 && !puedeRegistrar()) return false;
    return true;
}

function motivoBloqueo(n) {
    if (!puedeAvanzar1()) return 'Complete el paso 1 (Estudiante) antes de avanzar al paso ' + n + '.';
    if (n >= 3 && !puedeAvanzar2()) return 'Complete el paso 2 (Padre / Apoderado) antes de avanzar al paso ' + n + '.';
    if (n >= 4 && !puedeRegistrar()) return 'Complete el paso 3 (Periodo, Tipo y Sección) antes de ir a Confirmación.';
    return '';
}

function irAPaso(n) {
    stepError.value = '';
    if (n <= paso.value || puedeVerPaso(n)) {
        paso.value = n;
        return;
    }
    // Evento step-error: aviso claro y foco en el mensaje para lector de pantalla.
    stepError.value = motivoBloqueo(n);
}
</script>

<template>
    <Head title="Registro único de matrícula" />

    <AuthenticatedLayout :crumbs="crumbs">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Registro único de matrícula</h2>
                <Link :href="route('matriculas.index')" class="text-sm font-medium text-[#1E3A8A] hover:underline">← Volver al listado</Link>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
            <!-- Stepper -->
            <ol class="mb-6 flex items-center gap-1" aria-label="Progreso del registro">
                <li v-for="(p, i) in pasos" :key="p" class="flex flex-1 items-center">
                    <button
                        type="button"
                        class="flex min-h-[44px] flex-1 items-center justify-center gap-2 rounded-md px-2 text-xs font-semibold sm:text-sm"
                        :class="paso === i + 1 ? 'bg-[#1E3A8A] text-white' : paso > i + 1 ? 'bg-green-100 text-green-800' : 'bg-white text-gray-500'"
                        :aria-current="paso === i + 1 ? 'step' : undefined"
                        :aria-disabled="!puedeVerPaso(i + 1) && paso < i + 1 ? 'true' : undefined"
                        :title="!puedeVerPaso(i + 1) && paso < i + 1 ? motivoBloqueo(i + 1) : p"
                        @click="irAPaso(i + 1)"
                    >
                        <CheckIcon v-if="paso > i + 1" class="h-4 w-4" aria-hidden="true" />
                        <span v-else aria-hidden="true">{{ i + 1 }}</span> {{ p }}
                    </button>
                </li>
            </ol>

            <p v-if="stepError" class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-900" role="alert" tabindex="-1">{{ stepError }}</p>

            <p v-if="error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800" role="alert">{{ error }}</p>

            <!-- PASO 1 -->
            <section v-if="paso === 1" class="rounded-lg bg-white p-6 shadow-sm" aria-label="Paso 1: estudiante">
                <h3 class="mb-4 text-base font-semibold">1. Estudiante</h3>
                <div class="mb-4 flex gap-4" role="radiogroup" aria-label="Modo de estudiante">
                    <label class="flex min-h-[44px] items-center gap-2 text-sm"><input v-model="modoEst" type="radio" value="existente" class="h-5 w-5 accent-[#1E3A8A]" /> Existente</label>
                    <label class="flex min-h-[44px] items-center gap-2 text-sm"><input v-model="modoEst" type="radio" value="nuevo" class="h-5 w-5 accent-[#1E3A8A]" /> Nuevo</label>
                </div>
                <PersonSearch v-if="modoEst === 'existente'" tipo="estudiante" v-model="estExistente" />
                <div v-else class="grid gap-3 sm:grid-cols-2">
                    <div><label for="e-dni" class="mb-1 block text-sm font-medium">DNI</label><input id="e-dni" v-model="estNuevo.dni" inputmode="numeric" maxlength="8" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                    <div><label for="e-nac" class="mb-1 block text-sm font-medium">Fecha nacimiento</label><input id="e-nac" v-model="estNuevo.fecha_nacimiento" type="date" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                    <div><label for="e-nom" class="mb-1 block text-sm font-medium">Nombres *</label><input id="e-nom" v-model="estNuevo.nombres" required class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                    <div><label for="e-ape" class="mb-1 block text-sm font-medium">Apellidos *</label><input id="e-ape" v-model="estNuevo.apellidos" required class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                    <div><label for="e-sexo" class="mb-1 block text-sm font-medium">Sexo</label><select id="e-sexo" v-model="estNuevo.sexo" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm"><option value="">—</option><option value="M">Masculino</option><option value="F">Femenino</option></select></div>
                    <div><label for="e-niv" class="mb-1 block text-sm font-medium">Nivel</label><select id="e-niv" v-model="estNuevo.nivel_id" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm"><option value="">—</option><option v-for="n in niveles" :key="n.id" :value="n.id">{{ n.nombre }}</option></select></div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" :disabled="!puedeAvanzar1()" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-6 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-40" @click="paso = 2">Siguiente →</button>
                </div>
            </section>

            <!-- PASO 2 -->
            <section v-if="paso === 2" class="rounded-lg bg-white p-6 shadow-sm" aria-label="Paso 2: padre o apoderado">
                <h3 class="mb-1 text-base font-semibold">2. Padre / Apoderado</h3>
                <p class="mb-4 text-xs text-gray-500">Al registrar un padre nuevo se crea automáticamente su apoderado (hub) y se vincula al estudiante.</p>
                <div class="mb-4 flex flex-wrap gap-4" role="radiogroup" aria-label="Modo de padre">
                    <label class="flex min-h-[44px] items-center gap-2 text-sm"><input v-model="modoPadre" type="radio" value="existente" class="h-5 w-5 accent-[#1E3A8A]" /> Existente</label>
                    <label class="flex min-h-[44px] items-center gap-2 text-sm"><input v-model="modoPadre" type="radio" value="nuevo" class="h-5 w-5 accent-[#1E3A8A]" /> Nuevo</label>
                    <label class="flex min-h-[44px] items-center gap-2 text-sm"><input v-model="modoPadre" type="radio" value="ninguno" class="h-5 w-5 accent-[#1E3A8A]" /> Omitir</label>
                </div>
                <PersonSearch v-if="modoPadre === 'existente'" tipo="padre" v-model="padreExistente" />
                <div v-else-if="modoPadre === 'nuevo'" class="grid gap-3 sm:grid-cols-2">
                    <div><label for="p-dni" class="mb-1 block text-sm font-medium">DNI</label><input id="p-dni" v-model="padreNuevo.dni" inputmode="numeric" maxlength="8" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                    <div><label for="p-tel" class="mb-1 block text-sm font-medium">Teléfono</label><input id="p-tel" v-model="padreNuevo.telefono" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                    <div><label for="p-nom" class="mb-1 block text-sm font-medium">Nombres *</label><input id="p-nom" v-model="padreNuevo.nombres" required class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                    <div><label for="p-ape" class="mb-1 block text-sm font-medium">Apellidos *</label><input id="p-ape" v-model="padreNuevo.apellidos" required class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                </div>
                <div class="mt-6 flex justify-between">
                    <button type="button" class="min-h-[44px] rounded-md border px-6 py-2 text-sm" @click="paso = 1">← Atrás</button>
                    <button type="button" :disabled="!puedeAvanzar2()" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-6 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-40" @click="paso = 3">Siguiente →</button>
                </div>
            </section>

            <!-- PASO 3 -->
            <section v-if="paso === 3" class="rounded-lg bg-white p-6 shadow-sm" aria-label="Paso 3: datos de matrícula">
                <h3 class="mb-4 text-base font-semibold">3. Datos de matrícula</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div><label for="m-per" class="mb-1 block text-sm font-medium">Periodo *</label><select id="m-per" v-model="mat.periodo_id" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm"><option value="" disabled>Seleccionar…</option><option v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre }} ({{ p.anio }}){{ p.activo ? ' · activo' : '' }}</option></select></div>
                    <div><label for="m-tipo" class="mb-1 block text-sm font-medium">Tipo de matrícula *</label><select id="m-tipo" v-model="mat.tipo_matricula_id" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm"><option value="" disabled>Seleccionar…</option><option v-for="t in tipos" :key="t.id" :value="t.id">{{ t.nombre }}</option></select></div>
                    <div class="sm:col-span-2"><label for="m-sec" class="mb-1 block text-sm font-medium">Sección *</label><select id="m-sec" v-model="mat.seccion_id" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm"><option value="" disabled>Seleccionar grado — sección…</option><option v-for="s in secciones" :key="s.id" :value="s.id">{{ s.grado?.nivel?.nombre ?? '' }} · {{ s.grado?.nombre }} — {{ s.nombre }} ({{ s.turno }})</option></select></div>
                </div>
                <div class="mt-2"><VacancyIndicator :seccion-id="mat.seccion_id" /></div>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div><label for="m-fecha" class="mb-1 block text-sm font-medium">Fecha</label><input id="m-fecha" v-model="mat.fecha" type="date" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                    <div><label for="m-obs" class="mb-1 block text-sm font-medium">Observaciones</label><input id="m-obs" v-model="mat.observaciones" class="min-h-[44px] w-full rounded-md border-gray-300 text-sm" /></div>
                </div>
                <div class="mt-6 flex justify-between">
                    <button type="button" class="min-h-[44px] rounded-md border px-6 py-2 text-sm" @click="paso = 2">← Atrás</button>
                    <button type="button" :disabled="!puedeRegistrar()" class="min-h-[44px] rounded-md bg-[#1E3A8A] px-6 py-2 text-sm font-semibold text-white hover:bg-[#162c6b] disabled:opacity-40" @click="paso = 4">Revisar →</button>
                </div>
            </section>

            <!-- PASO 4 -->
            <section v-if="paso === 4" class="rounded-lg bg-white p-6 shadow-sm" aria-label="Paso 4: confirmación">
                <h3 class="mb-4 text-base font-semibold">4. Confirmación</h3>
                <dl class="divide-y rounded-md border text-sm">
                    <div class="flex justify-between px-4 py-2"><dt class="text-gray-500">Estudiante</dt><dd class="font-medium">{{ resumenEst?.nombres }} {{ resumenEst?.apellidos }} ({{ resumenEst?.dni || 'nuevo' }})</dd></div>
                    <div class="flex justify-between px-4 py-2"><dt class="text-gray-500">Padre</dt><dd class="font-medium">{{ resumenPadre ? `${resumenPadre.nombres} ${resumenPadre.apellidos}` : '—' }}</dd></div>
                    <div class="flex justify-between px-4 py-2"><dt class="text-gray-500">Sección</dt><dd class="font-medium">{{ seccionElegida?.grado?.nombre }} — {{ seccionElegida?.nombre }}</dd></div>
                </dl>
                <div class="mt-6 flex justify-between">
                    <button type="button" class="min-h-[44px] rounded-md border px-6 py-2 text-sm" @click="paso = 3">← Atrás</button>
                    <button type="button" :disabled="form.processing" class="inline-flex min-h-[44px] items-center gap-2 rounded-md bg-green-700 px-6 py-2 text-sm font-semibold text-white hover:bg-green-800 disabled:opacity-50" @click="registrar"><CheckIcon class="h-5 w-5" aria-hidden="true" /> Registrar matrícula</button>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

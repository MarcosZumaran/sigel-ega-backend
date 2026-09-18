<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    tipo: { type: String, required: true, validator: (v) => ['estudiante', 'padre'].includes(v) },
    modelValue: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue']);

const q = ref('');
const resultados = ref([]);
const buscando = ref(false);
let timer = null;

watch(q, (val) => {
    clearTimeout(timer);
    if (!val || val.trim().length < 2) {
        resultados.value = [];
        return;
    }
    timer = setTimeout(async () => {
        buscando.value = true;
        try {
            const url = props.tipo === 'estudiante' ? route('buscar.estudiantes') : route('buscar.padres');
            const { data } = await axios.get(url, { params: { q: val.trim() } });
            resultados.value = data;
        } catch {
            resultados.value = [];
        } finally {
            buscando.value = false;
        }
    }, 300);
});

function elegir(item) {
    emit('update:modelValue', item);
    resultados.value = [];
    q.value = '';
}

function limpiar() {
    emit('update:modelValue', null);
}
</script>

<template>
    <div>
        <div v-if="!modelValue">
            <label :for="`buscar-${tipo}`" class="mb-1 block text-sm font-medium text-gray-700">
                Buscar {{ tipo === 'estudiante' ? 'estudiante' : 'padre' }} por DNI o nombre
            </label>
            <input
                :id="`buscar-${tipo}`"
                v-model="q"
                type="search"
                autocomplete="off"
                placeholder="Mínimo 2 caracteres…"
                class="min-h-[44px] w-full rounded-md border-gray-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]"
                role="combobox"
                aria-expanded="false"
                aria-autocomplete="list"
            />
            <p v-if="buscando" class="mt-1 text-xs text-gray-500" role="status">Buscando…</p>
            <ul v-if="resultados.length" class="mt-1 max-h-48 divide-y overflow-y-auto rounded-md border border-gray-200 bg-white" role="listbox" aria-label="Resultados de búsqueda">
                <li v-for="r in resultados" :key="r.id" role="option" :aria-selected="false">
                    <button
                        type="button"
                        class="flex min-h-[44px] w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-blue-50 focus-visible:bg-blue-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#1E3A8A]"
                        @click="elegir(r)"
                    >
                        <span class="font-medium">{{ r.nombres }} {{ r.apellidos }}</span>
                        <span class="text-xs text-gray-500">DNI {{ r.dni }}</span>
                    </button>
                </li>
            </ul>
            <p v-else-if="q.trim().length >= 2 && !buscando" class="mt-1 text-xs text-gray-500">Sin resultados — puede registrarlo como nuevo.</p>
        </div>
        <div v-else class="flex items-center justify-between rounded-md border border-green-200 bg-green-50 px-3 py-2">
            <p class="text-sm"><strong>{{ modelValue.nombres }} {{ modelValue.apellidos }}</strong> <span class="text-gray-600">· DNI {{ modelValue.dni }}</span></p>
            <button type="button" class="min-h-[44px] px-2 text-sm font-medium text-red-700 hover:underline" @click="limpiar">Cambiar</button>
        </div>
    </div>
</template>

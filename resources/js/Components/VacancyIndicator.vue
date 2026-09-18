<script setup>
import { TicketIcon } from '@heroicons/vue/24/outline';
import { computed, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    seccionId: { type: [Number, String], default: null },
});

const info = ref(null);
const cargando = ref(false);

async function cargar(id) {
    if (!id) {
        info.value = null;
        return;
    }
    cargando.value = true;
    try {
        const { data } = await axios.get(route('secciones.vacantes', id));
        info.value = data;
    } catch {
        info.value = null;
    } finally {
        cargando.value = false;
    }
}

watch(() => props.seccionId, cargar, { immediate: true });

const estado = computed(() => {
    if (!info.value) return null;
    if (info.value.disponibles > 0) return { texto: `${info.value.disponibles} vacantes disponibles (de ${info.value.vacantes})`, clase: 'border-green-200 bg-green-50 text-green-800' };
    return { texto: `Sección llena (${info.value.ocupadas}/${info.value.vacantes})`, clase: 'border-red-200 bg-red-50 text-red-800' };
});
</script>

<template>
    <div aria-live="polite">
        <p v-if="cargando" class="text-xs text-gray-500" role="status">Consultando vacantes…</p>
        <p v-else-if="estado" class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm" :class="estado.clase" role="status">
            <TicketIcon class="h-5 w-5 shrink-0" aria-hidden="true" /> {{ estado.texto }}
        </p>
    </div>
</template>

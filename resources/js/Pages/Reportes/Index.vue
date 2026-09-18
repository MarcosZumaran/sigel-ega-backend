<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({ reportes: Object, filtros: Object, periodos: Array, secciones: Array });

const f = reactive({
    tipo: props.filtros?.tipo ?? '',
    periodo_id: props.filtros?.periodo_id ?? '',
    seccion_id: props.filtros?.seccion_id ?? '',
    formato: props.filtros?.formato ?? '',
});

let t = null;
watch(f, () => {
    clearTimeout(t);
    t = setTimeout(() => {
        const q = Object.fromEntries(Object.entries(f).filter(([, v]) => v !== ''));
        router.get(route('reportes.index'), q, { preserveState: true, replace: true });
    }, 400);
});

function eliminar(id) {
    if (!confirm('¿Eliminar este reporte?')) return;
    router.delete(route('reportes.destroy', id));
}
</script>

<template>
    <Head title="Reportes" />
    <AuthenticatedLayout :crumbs="[{ label: 'Reportes' }]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-[#1E3A8A]">Reportes</h1>
                <p class="mt-1 text-sm text-slate-600">Genera y consulta reportes del sistema (SIAGIE, asistencia, notas).</p>
            </div>
            <Link :href="route('reportes.create')" class="inline-flex min-h-[44px] items-center rounded-lg bg-[#1E3A8A] px-4 py-2 text-sm font-medium text-white hover:bg-[#162c6b]">
                + Generar reporte
            </Link>
        </div>

        <p v-if="$page.props.flash?.success" role="status" class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ $page.props.flash.success }}
        </p>

        <form class="mt-4 grid gap-3 rounded-xl bg-white p-4 shadow sm:grid-cols-4" role="search" aria-label="Filtros de reportes" @submit.prevent>
            <div>
                <label for="f-tipo" class="mb-1 block text-sm font-medium text-slate-700">Tipo</label>
                <input id="f-tipo" v-model="f.tipo" type="search" placeholder="matriculas, asistencia…" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]" />
            </div>
            <div>
                <label for="f-per" class="mb-1 block text-sm font-medium text-slate-700">Periodo</label>
                <select id="f-per" v-model="f.periodo_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                    <option value="">Todos</option>
                    <option v-for="p in (periodos ?? [])" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                </select>
            </div>
            <div>
                <label for="f-sec" class="mb-1 block text-sm font-medium text-slate-700">Sección</label>
                <select id="f-sec" v-model="f.seccion_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                    <option value="">Todas</option>
                    <option v-for="s in (secciones ?? [])" :key="s.id" :value="s.id">{{ s.grado?.nombre }} {{ s.nombre }}</option>
                </select>
            </div>
            <div>
                <label for="f-for" class="mb-1 block text-sm font-medium text-slate-700">Formato</label>
                <select id="f-for" v-model="f.formato" class="w-full rounded-lg border-slate-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]">
                    <option value="">Todos</option>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
        </form>

        <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-[#1E3A8A] text-white">
                        <th scope="col" class="px-4 py-3">Tipo</th>
                        <th scope="col" class="px-4 py-3">Periodo</th>
                        <th scope="col" class="px-4 py-3">Sección</th>
                        <th scope="col" class="px-4 py-3">Formato</th>
                        <th scope="col" class="px-4 py-3">Estado</th>
                        <th scope="col" class="px-4 py-3">Generado</th>
                        <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in (reportes?.data ?? [])" :key="r.id" class="border-b last:border-0 hover:bg-slate-50">
                        <td class="px-4 py-2 font-medium">{{ r.tipo }}</td>
                        <td class="px-4 py-2">{{ r.periodo?.nombre ?? '—' }}</td>
                        <td class="px-4 py-2">{{ r.seccion ? (r.seccion.grado?.nombre + ' ' + r.seccion.nombre) : 'Todas' }}</td>
                        <td class="px-4 py-2 uppercase">{{ r.formato }}</td>
                        <td class="px-4 py-2">{{ r.estado }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ r.created_at?.slice(0, 10) ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            <Link :href="route('reportes.show', r.id)" class="mr-3 font-medium text-[#1E3A8A] hover:underline">Ver</Link>
                            <button type="button" class="font-medium text-[#C8102E] hover:underline" @click="eliminar(r.id)">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!(reportes?.data ?? []).length" class="px-4 py-6 text-center text-sm text-slate-500">Sin reportes con esos filtros.</p>
        </div>

        <nav v-if="reportes && reportes.last_page > 1" class="mt-4 flex items-center gap-3 text-sm" aria-label="Paginación">
            <Link v-if="reportes.prev_page_url" :href="reportes.prev_page_url" class="min-h-[44px] rounded-lg border border-slate-300 px-4 py-2 hover:bg-slate-50">← Anterior</Link>
            <span role="status">Página {{ reportes.current_page }} de {{ reportes.last_page }}</span>
            <Link v-if="reportes.next_page_url" :href="reportes.next_page_url" class="min-h-[44px] rounded-lg border border-slate-300 px-4 py-2 hover:bg-slate-50">Siguiente →</Link>
        </nav>
    </AuthenticatedLayout>
</template>

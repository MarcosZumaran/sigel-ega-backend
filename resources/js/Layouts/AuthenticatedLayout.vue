<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    AcademicCapIcon,
    BookOpenIcon,
    BuildingLibraryIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    ChartPieIcon,
    CheckCircleIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    ClipboardDocumentCheckIcon,
    DocumentTextIcon,
    FolderIcon,
    LinkIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    PresentationChartLineIcon,
    Squares2X2Icon,
    UserCircleIcon,
    UserGroupIcon,
    UsersIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    crumbs: { type: Array, default: () => [] },
});

const page = usePage();
const sidebarOpen = ref(false);
const searchForm = useForm({ q: '' });

const user = computed(() => page.props.auth?.user);

function buscarGlobal() {
    const q = (searchForm.q || '').trim();
    if (q.length >= 2) router.get(route('buscar'), { q });
}

const grupos = computed(() => {
    const cur = (p) => route().current(p);
    const is = (...patterns) => patterns.some((p) => cur(p));
    return [
        {
            titulo: 'Principal',
            items: [
                { label: 'Dashboard', href: 'dashboard', active: is('dashboard'), icon: ChartBarIcon },
            ],
        },
        {
            titulo: 'Alumnado y Padres',
            icon: UsersIcon,
            open: is('padres.*', 'estudiantes.*', 'apoderados.*'),
            items: [
                { label: 'Padres de Familia', href: 'padres.index', active: is('padres.*'), icon: UserGroupIcon },
                { label: 'Estudiantes', href: 'estudiantes.index', active: is('estudiantes.*'), icon: AcademicCapIcon },
                { label: 'Apoderados', href: 'apoderados.index', active: is('apoderados.*'), icon: LinkIcon },
            ],
        },
        {
            titulo: 'Gestión Académica',
            icon: BookOpenIcon,
            open: is('matriculas.*', 'notas.*', 'asistencias.*'),
            items: [
                { label: 'Matrícula', href: 'matriculas.index', active: is('matriculas.*'), icon: PencilSquareIcon },
                { label: 'Notas y Evaluación', href: 'notas.index', active: is('notas.*'), icon: ChartBarIcon },
                { label: 'Asistencia', href: 'asistencias.index', active: is('asistencias.*'), icon: ClipboardDocumentCheckIcon },
            ],
        },
        {
            titulo: 'Infraestructura y Personal',
            icon: BuildingLibraryIcon,
            open: is('grados.*', 'secciones.*', 'periodos.*'),
            items: [
                { label: 'Grados', href: 'grados.index', active: is('grados.*'), icon: FolderIcon },
                { label: 'Secciones', href: 'secciones.index', active: is('secciones.*'), icon: Squares2X2Icon },
                { label: 'Periodos', href: 'periodos.index', active: is('periodos.*'), icon: CalendarDaysIcon },
            ],
        },
        {
            titulo: 'Reportes y Estadísticas',
            icon: PresentationChartLineIcon,
            open: is('reportes.*', 'estadisticas'),
            items: [
                { label: 'Reportes', href: 'reportes.index', active: is('reportes.*'), icon: DocumentTextIcon },
                { label: 'Estadísticas', href: 'estadisticas', active: is('estadisticas'), icon: ChartPieIcon },
            ],
        },
        {
            titulo: 'Mi Cuenta',
            items: [
                { label: 'Perfil', href: 'profile.edit', active: is('profile.*'), icon: UserCircleIcon },
            ],
        },
    ];
});

const abiertos = ref({});
grupos.value.forEach((g, i) => {
    abiertos.value[i] = g.open || i === 0;
});

function toggleGrupo(i) {
    abiertos.value[i] = !abiertos.value[i];
}

const toast = ref(null);
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success || flash?.error) {
            toast.value = { tipo: flash.success ? 'exito' : 'error', texto: flash.success ?? flash.error };
            setTimeout(() => {
                toast.value = null;
            }, 5000);
        }
    },
    { immediate: true, deep: true },
);

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route('logout'));
}
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-[100] focus:rounded focus:bg-white focus:p-2 focus:text-[#1E3A8A]">Saltar al contenido</a>

        <!-- Overlay móvil -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-[#1E3A8A] text-white transition-transform lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
            aria-label="Navegación principal"
        >
            <div class="flex h-16 items-center gap-2 border-b border-white/10 px-4">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-lg font-bold text-[#1E3A8A]" aria-hidden="true">E</span>
                <div>
                    <p class="text-sm font-bold leading-tight">SIGEL-EGA</p>
                    <p class="text-[11px] leading-tight text-blue-200">I.E. Pública EGA</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-2 py-3" aria-label="Secciones del sistema">
                <div v-for="(g, gi) in grupos" :key="gi" class="mb-1">
                    <button
                        v-if="g.items.length > 1"
                        type="button"
                        class="flex min-h-[44px] w-full items-center justify-between rounded-md px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-blue-200 hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                        :aria-expanded="abiertos[gi] ? 'true' : 'false'"
                        @click="toggleGrupo(gi)"
                    >
                        <span class="flex items-center gap-2"><component :is="g.icon" v-if="g.icon" class="h-4 w-4" aria-hidden="true" />{{ g.titulo }}</span>
                        <component :is="abiertos[gi] ? ChevronDownIcon : ChevronRightIcon" class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <p v-else class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-blue-200">{{ g.titulo }}</p>
                    <ul v-show="abiertos[gi]" class="space-y-0.5" :class="{ 'ml-3 border-l-2 border-white/20 pl-2': g.items.length > 1 }">
                        <li v-for="item in g.items" :key="item.label">
                            <Link
                                :href="route(item.href)"
                                class="flex min-h-[44px] items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                                :class="{ 'bg-white font-semibold text-[#1E3A8A] hover:bg-white': item.active }"
                                :aria-current="item.active ? 'page' : undefined"
                                @click="sidebarOpen = false"
                            >
                                <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" /> {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="border-t border-white/10 p-3 text-xs text-blue-200">
                <p class="font-semibold text-white">{{ user?.name }}</p>
                <p class="truncate">{{ user?.email }}</p>
            </div>
        </aside>

        <!-- Columna principal -->
        <div class="lg:pl-64">
            <!-- Header -->
            <header class="sticky top-0 z-20 flex h-16 items-center gap-2 border-b border-gray-200 bg-white px-4">
                <button
                    type="button"
                    class="rounded-md p-2 text-gray-600 hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#1E3A8A] lg:hidden"
                    aria-label="Abrir menú de navegación"
                    :aria-expanded="sidebarOpen ? 'true' : 'false'"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <form @submit.prevent="buscarGlobal" role="search" aria-label="Búsqueda global" class="flex max-w-md flex-1 items-center gap-2">
                    <label for="busqueda-global" class="sr-only">Buscar estudiantes, padres o documentos</label>
                    <input
                        id="busqueda-global"
                        v-model="searchForm.q"
                        type="search"
                        minlength="2"
                        placeholder="Buscar estudiante, padre, documento…"
                        class="min-h-[44px] w-full rounded-md border-gray-300 text-sm focus:border-[#1E3A8A] focus:ring-[#1E3A8A]"
                    />
                    <button type="submit" class="flex min-h-[44px] items-center rounded-md bg-[#1E3A8A] px-3 text-sm font-semibold text-white hover:bg-[#162c6b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]" aria-label="Buscar">
                        <MagnifyingGlassIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </form>
                <div class="ml-auto flex items-center gap-2">
                    <Link :href="route('profile.edit')" class="hidden min-h-[44px] items-center gap-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 sm:inline-flex"><UserCircleIcon class="h-5 w-5" aria-hidden="true" /> Mi cuenta</Link>
                    <button type="button" :disabled="logoutForm.processing" class="inline-flex min-h-[44px] items-center rounded-md border px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50" @click="logout">Salir</button>
                </div>
            </header>

            <!-- Breadcrumbs + título -->
            <div class="border-b border-gray-200 bg-white px-4 py-3 sm:px-6" v-if="$slots.header || crumbs.length">
                <nav v-if="crumbs.length" aria-label="Migas de pan">
                    <ol class="flex flex-wrap items-center gap-1 text-xs text-gray-500">
                        <li><Link :href="route('dashboard')" class="hover:text-[#1E3A8A] hover:underline">Inicio</Link></li>
                        <li v-for="(c, i) in crumbs" :key="i" class="flex items-center gap-1">
                            <span aria-hidden="true">/</span>
                            <Link v-if="c.href && i < crumbs.length - 1" :href="c.href" class="hover:text-[#1E3A8A] hover:underline">{{ c.label }}</Link>
                            <span v-else aria-current="page" class="font-medium text-gray-800">{{ c.label }}</span>
                        </li>
                    </ol>
                </nav>
                <slot name="header" />
            </div>

            <!-- Toast -->
            <div v-if="toast" :role="toast.tipo === 'error' ? 'alert' : 'status'" class="mx-4 mt-4 flex items-start gap-2 rounded-lg border p-3 text-sm shadow-sm sm:mx-6" :class="toast.tipo === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'">
                <component :is="toast.tipo === 'error' ? XCircleIcon : CheckCircleIcon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                <p>{{ toast.texto }}</p>
                <button type="button" class="ml-auto font-bold" aria-label="Cerrar aviso" @click="toast = null">×</button>
            </div>

            <!-- Contenido -->
            <main id="contenido" class="p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

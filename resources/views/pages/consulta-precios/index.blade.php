@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Consulta de Precios', 'url' => null]]" />

{{-- Todo el módulo dentro del mismo x-data para que el modal tenga acceso --}}
<div class="space-y-5" x-data="consultaPrecios()" x-init="init()" data-module="consulta-precios">

    {{-- Cabecera --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Fuente: <span class="font-medium">DIGEMID</span> — Sistema Nacional de Información de Precios de Productos Farmacéuticos (SNIPPF)
        </p>
        <a href="https://opm-digemid.minsa.gob.pe/#/consulta-producto" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Ver en DIGEMID
        </a>
    </div>

    {{-- ── FORMULARIO DE BÚSQUEDA ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
        <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Filtros de búsqueda</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Producto con autocomplete --}}
            <div class="lg:col-span-2 relative" @click.outside="showSuggestions = false; sinResultadosProducto = false">
                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                    Producto / Principio activo <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text"
                        x-model="productoQuery"
                        @input.debounce.400ms="buscarProducto()"
                        @focus="if(sugerencias.length) showSuggestions = true"
                        @keydown.escape="showSuggestions = false; sinResultadosProducto = false"
                        @keydown.arrow-down.prevent="navegarSugerencias(1)"
                        @keydown.arrow-up.prevent="navegarSugerencias(-1)"
                        @keydown.enter.prevent="seleccionarSugerenciaActiva()"
                        placeholder="Ej: PARACETAMOL, IBUPROFENO, AMOXICILINA..."
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" />
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <svg x-show="!loadingProducto" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg x-show="loadingProducto" class="h-4 w-4 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Dropdown sugerencias --}}
                <div x-show="showSuggestions && sugerencias.length > 0"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800"
                    style="max-height:280px;overflow-y:auto">
                    <template x-for="(item, idx) in sugerencias" :key="idx">
                        <button type="button" @click="seleccionarProducto(item)"
                            :class="idx === sugerenciaActiva ? 'bg-brand-50 dark:bg-brand-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                            class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left transition">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.nombreProducto + ' ' + item.concent"></span>
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400" x-text="item.nombreFormaFarmaceutica"></span>
                            </div>
                            <svg class="h-4 w-4 flex-shrink-0 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </template>
                </div>

                {{-- Dropdown sin resultados --}}
                <div x-show="sinResultadosProducto"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute z-50 mt-1 w-full rounded-xl border border-amber-200 bg-amber-50 shadow-xl dark:border-amber-700/50 dark:bg-gray-800">
                    <div class="flex items-start gap-3 px-4 py-4">
                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                                No se encontró "<span class="italic" x-text="productoQuery"></span>"
                            </p>
                            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                Intente con el nombre genérico o una variación del nombre.
                            </p>
                            <div class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-amber-400 bg-amber-100 px-2.5 py-1 dark:border-amber-600 dark:bg-amber-900/40">
                                <svg class="h-3.5 w-3.5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                                    Prueba solo con las iniciales:
                                    <span class="font-mono">PARA</span>, <span class="font-mono">IBUP</span>, <span class="font-mono">AMOX</span>…
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-amber-500 dark:text-amber-500">
                                Nombre completo: <span class="font-medium">PARACETAMOL</span>, <span class="font-medium">IBUPROFENO</span>, <span class="font-medium">AMOXICILINA</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Badge producto seleccionado --}}
                <div x-show="productoSeleccionado" class="mt-2 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-lg bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-900/20 dark:text-brand-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="productoSeleccionado ? productoSeleccionado.nombreProducto + ' ' + productoSeleccionado.concent : ''"></span>
                    </span>
                    <button @click="limpiarProducto()" type="button" class="text-xs text-gray-400 hover:text-red-500">Cambiar</button>
                </div>
            </div>

            {{-- Tipo establecimiento --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Tipo de establecimiento</label>
                <div class="relative">
                    <select x-model="filtros.codTipoEstablecimiento"
                        class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">Todos</option>
                        <option value="1">Privado</option>
                        <option value="2">Público</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Departamento --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Departamento</label>
                <div class="relative">
                    <select x-model="filtros.codigoDepartamento" @change="onDepartamentoChange()"
                        class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">— Seleccionar —</option>
                        @foreach($departamentos as $codigo => $nombre)
                            <option value="{{ $codigo }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Provincia --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Provincia</label>
                <div class="relative">
                    <select x-model="filtros.codigoProvincia" @change="onProvinciaChange()"
                        :disabled="!filtros.codigoDepartamento || loadingProvincias"
                        class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">— Seleccionar —</option>
                        <template x-for="p in provincias" :key="p.codigo">
                            <option :value="p.codigo" x-text="p.descripcion"></option>
                        </template>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Distrito --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Distrito</label>
                <div class="relative">
                    <select x-model="filtros.codigoUbigeo"
                        :disabled="!filtros.codigoProvincia || loadingDistritos"
                        class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">— Todos —</option>
                        <template x-for="d in distritos" :key="d.codigo">
                            <option :value="d.codigo" x-text="d.descripcion"></option>
                        </template>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Laboratorio --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Laboratorio</label>
                <input type="text" x-model="filtros.nombreLaboratorio" placeholder="Nombre del laboratorio..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" />
            </div>

            {{-- Farmacia --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Farmacia / Botica</label>
                <input type="text" x-model="filtros.nombreEstablecimiento" placeholder="Nombre del establecimiento..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" />
            </div>
        </div>

        {{-- Botones --}}
        <div class="mt-5 flex flex-wrap items-center gap-3">
            <button @click="buscarPrecios()" :disabled="!puedeConsultar || loadingPrecios"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                <svg x-show="!loadingPrecios" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <svg x-show="loadingPrecios" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loadingPrecios ? 'Consultando...' : 'Consultar precios'"></span>
            </button>
            <button @click="limpiarFiltros()" type="button"
                class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                Limpiar
            </button>
        </div>
        <p x-show="!puedeConsultar" class="mt-2 text-xs text-amber-600 dark:text-amber-400">
            Seleccione un producto del autocomplete para consultar precios.
        </p>
    </div>

    {{-- Alerta error --}}
    <div x-show="errorMsg" x-transition
        class="rounded-xl border border-red-200 bg-red-50/80 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="font-medium" x-text="errorMsg"></p>
        </div>
    </div>

    {{-- ── RESULTADOS ── --}}
    <div x-show="resultados.length > 0 || (buscado && resultados.length === 0)" x-transition>

        {{-- Stats cards --}}
        <div x-show="resultados.length > 0" class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
                <p class="text-xs text-gray-500 dark:text-gray-400">Total registros</p>
                <p class="mt-0.5 text-xl font-bold text-gray-900 dark:text-white" x-text="resultados.length"></p>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-3 shadow-sm dark:border-green-800 dark:bg-green-900/10">
                <p class="text-xs text-green-600 dark:text-green-400">Precio mínimo</p>
                <p class="mt-0.5 text-xl font-bold text-green-700 dark:text-green-300" x-text="'S/ ' + precioMinimo.toFixed(2)"></p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-3 shadow-sm dark:border-red-800 dark:bg-red-900/10">
                <p class="text-xs text-red-600 dark:text-red-400">Precio máximo</p>
                <p class="mt-0.5 text-xl font-bold text-red-700 dark:text-red-300" x-text="'S/ ' + precioMaximo.toFixed(2)"></p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 shadow-sm dark:border-blue-800 dark:bg-blue-900/10">
                <p class="text-xs text-blue-600 dark:text-blue-400">Precio promedio</p>
                <p class="mt-0.5 text-xl font-bold text-blue-700 dark:text-blue-300" x-text="'S/ ' + precioPromedio.toFixed(2)"></p>
            </div>
        </div>

        {{-- Barra de herramientas de la tabla --}}
        <div x-show="resultados.length > 0"
            class="mb-3 flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">

            {{-- Búsqueda dentro de resultados --}}
            <div class="relative w-full sm:flex-1 sm:min-w-[180px]">
                <svg class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="busquedaTabla" @input="paginaActual = 1" placeholder="Filtrar por farmacia o laboratorio..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-1.5 pl-8 pr-3 text-xs text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" />
            </div>

            {{-- Filtro tipo --}}
            <div class="relative">
                <select x-model="filtroTabla" @change="paginaActual = 1"
                    class="appearance-none rounded-lg border border-gray-300 bg-white py-1.5 pl-3 pr-8 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Todos los tipos</option>
                    <option value="Privado">Privado</option>
                    <option value="Público">Público</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Ordenar por precio --}}
            <button @click="toggleOrden()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                <span x-text="ordenPrecio === 'asc' ? 'Precio ↑' : ordenPrecio === 'desc' ? 'Precio ↓' : 'Ordenar'"></span>
            </button>

            {{-- Registros por página --}}
            <div class="relative">
                <select x-model="porPagina" @change="paginaActual = 1"
                    class="appearance-none rounded-lg border border-gray-300 bg-white py-1.5 pl-3 pr-8 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <option value="10">10 por página</option>
                    <option value="20">20 por página</option>
                    <option value="50">50 por página</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Exportar XLSX --}}
            <button @click="exportarXLSX()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/30">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exportar Excel
            </button>

            {{-- Contador --}}
            <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="resultadosFiltrados.length"></span>
                de <span x-text="resultados.length"></span> resultados
            </span>
        </div>

        {{-- Sin resultados --}}
        <div x-show="buscado && resultados.length === 0"
            class="rounded-2xl border border-dashed border-amber-300 bg-amber-50/50 p-8 text-center dark:border-amber-700 dark:bg-amber-900/10">
            <svg class="mx-auto h-10 w-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mt-3 text-sm font-medium text-amber-800 dark:text-amber-300">No se encontraron precios con los filtros seleccionados.</p>
            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Intente con menos filtros o un departamento diferente.</p>
        </div>

        {{-- Sin resultados del filtro interno --}}
        <div x-show="resultados.length > 0 && resultadosFiltrados.length === 0"
            class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-800/30">
            <p class="text-sm text-gray-500 dark:text-gray-400">Ningún resultado coincide con "<span class="font-medium" x-text="busquedaTabla"></span>".</p>
        </div>

        {{-- Tabla --}}
        <div x-show="paginados.length > 0" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03]">
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Farmacia / Botica</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Laboratorio</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ubicación</th>
                            <th class="cursor-pointer px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                @click="toggleOrden()">
                                P. Unitario
                                <span x-show="ordenPrecio === 'asc'"> ↑</span>
                                <span x-show="ordenPrecio === 'desc'"> ↓</span>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">P. Pack</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actualizado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        <template x-for="(item, idx) in paginados" :key="idx">
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <span :class="item.setcodigo === 'Privado'
                                        ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'
                                        : 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300'"
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        x-text="item.setcodigo">
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="item.nombreComercial ?? '—'"></p>
                                    <p x-show="item.telefono" class="mt-0.5 text-xs text-gray-400"
                                        x-text="item.telefono !== 'NO REGISTRADO' ? 'Tel: ' + item.telefono : ''"></p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="item.nombreProducto ?? item.nombreSustancia"></p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"
                                        x-text="item.concent + (item.nombreFormaFarmaceutica ? ' · ' + item.nombreFormaFarmaceutica : '') + (item.fracciones ? ' x ' + item.fracciones + ' un.' : '')"></p>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400" x-text="item.nombreLaboratorio ?? '—'"></td>
                                <td class="px-4 py-3">
                                    <p class="text-xs text-gray-700 dark:text-gray-300"
                                        x-text="[item.distrito, item.provincia].filter(Boolean).join(', ') || '—'"></p>
                                    <p x-show="item.direccion" class="mt-0.5 text-xs text-gray-400 dark:text-gray-500" x-text="item.direccion"></p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span x-show="item.precio2"
                                        :class="Number(item.precio2) === precioMinimo ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-lg px-2 py-0.5' : 'text-gray-900 dark:text-white'"
                                        class="text-sm font-semibold"
                                        x-text="'S/ ' + Number(item.precio2).toFixed(2)"></span>
                                    <span x-show="!item.precio2" class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span x-show="item.precio1" class="text-sm font-semibold text-gray-700 dark:text-gray-300"
                                        x-text="'S/ ' + Number(item.precio1).toFixed(2)"></span>
                                    <span x-show="!item.precio1" class="text-xs text-gray-400">—</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400" x-text="item.fecha ?? '—'"></td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="verDetalle(item)"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm transition hover:border-brand-400 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:bg-brand-900/20">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- ── PAGINACIÓN ── --}}
            <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-white/[0.02] sm:flex-row">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Mostrando
                    <span class="font-semibold text-gray-900 dark:text-white" x-text="(paginaActual - 1) * Number(porPagina) + 1"></span>
                    –
                    <span class="font-semibold text-gray-900 dark:text-white" x-text="Math.min(paginaActual * Number(porPagina), resultadosFiltrados.length)"></span>
                    de
                    <span class="font-semibold text-gray-900 dark:text-white" x-text="resultadosFiltrados.length"></span>
                    resultados
                </p>

                <div class="flex items-center gap-1">
                    {{-- Primera --}}
                    <button @click="paginaActual = 1" :disabled="paginaActual === 1"
                        class="rounded-lg border border-gray-300 bg-white p-1.5 text-gray-500 disabled:opacity-40 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                    </button>
                    {{-- Anterior --}}
                    <button @click="paginaActual--" :disabled="paginaActual === 1"
                        class="rounded-lg border border-gray-300 bg-white p-1.5 text-gray-500 disabled:opacity-40 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    {{-- Páginas --}}
                    <template x-for="p in paginasVisibles" :key="p">
                        <button @click="p !== '...' && (paginaActual = p)"
                            :class="p === paginaActual
                                ? 'bg-brand-500 border-brand-500 text-white'
                                : p === '...' ? 'cursor-default border-transparent bg-transparent text-gray-400'
                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                            class="min-w-[32px] rounded-lg border px-2 py-1.5 text-xs font-medium"
                            x-text="p">
                        </button>
                    </template>

                    {{-- Siguiente --}}
                    <button @click="paginaActual++" :disabled="paginaActual === totalPaginas"
                        class="rounded-lg border border-gray-300 bg-white p-1.5 text-gray-500 disabled:opacity-40 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    {{-- Última --}}
                    <button @click="paginaActual = totalPaginas" :disabled="paginaActual === totalPaginas"
                        class="rounded-lg border border-gray-300 bg-white p-1.5 text-gray-500 disabled:opacity-40 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado vacío inicial --}}
    <div x-show="!buscado && !loadingPrecios"
        class="rounded-2xl border border-dashed border-gray-300 bg-gray-50/50 p-12 text-center dark:border-gray-700 dark:bg-white/[0.01]">
        <svg class="mx-auto h-14 w-14 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="mt-4 text-sm font-medium text-gray-500 dark:text-gray-400">Busca un medicamento para ver precios en farmacias y boticas del Perú</p>
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Los precios son registrados por los establecimientos en tiempo real.</p>
    </div>

    {{-- ── MODAL DETALLE (dentro del mismo x-data) ── --}}
    <div x-show="modalDetalle"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="modalDetalle = false"
        class="fixed inset-0 z-[100000] flex items-end justify-center p-4 sm:items-center">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modalDetalle = false"></div>

        {{-- Panel --}}
        <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-t-2xl sm:rounded-2xl bg-white shadow-2xl dark:bg-gray-900"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            {{-- Header --}}
            <div class="flex items-start justify-between border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/60">
                <div class="flex-1 min-w-0 pr-4">
                    <h3 class="truncate text-base font-semibold text-gray-900 dark:text-white"
                        x-text="detalle?.nombreComercial ?? 'Detalle del establecimiento'"></h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"
                        x-text="[detalle?.direccion, detalle?.distrito, detalle?.provincia, detalle?.departamento].filter(Boolean).join(' · ')"></p>
                </div>
                <button @click="modalDetalle = false"
                    class="flex-shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Loading --}}
            <div x-show="loadingDetalle" class="flex flex-col items-center justify-center gap-3 py-16">
                <svg class="h-8 w-8 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="text-sm text-gray-500 dark:text-gray-400">Cargando detalle...</p>
            </div>

            {{-- Error --}}
            <div x-show="!loadingDetalle && errorDetalle" class="px-6 py-10 text-center">
                <svg class="mx-auto h-10 w-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="mt-3 text-sm font-medium text-red-600 dark:text-red-400" x-text="errorDetalle"></p>
            </div>

            {{-- Contenido --}}
            <div x-show="!loadingDetalle && detalle" class="max-h-[60vh] sm:max-h-[65vh] overflow-y-auto overscroll-contain">
                <div class="space-y-4 p-6">

                    {{-- Precios destacados --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-800/50">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Precio por pack</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white"
                                x-text="detalle?.precio1 ? 'S/ ' + Number(detalle.precio1).toFixed(2) : '—'"></p>
                        </div>
                        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center dark:border-green-800 dark:bg-green-900/20">
                            <p class="text-xs font-medium text-green-600 dark:text-green-400">Precio unitario</p>
                            <p class="mt-1 text-3xl font-bold text-green-700 dark:text-green-300"
                                x-text="detalle?.precio2 ? 'S/ ' + Number(detalle.precio2).toFixed(2) : '—'"></p>
                        </div>
                    </div>

                    {{-- Datos de contacto rápido --}}
                    <div class="flex flex-wrap gap-2">
                        <template x-if="detalle?.telefono && detalle.telefono !== 'NO REGISTRADO'">
                            <a :href="'tel:' + detalle.telefono"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span x-text="detalle.telefono"></span>
                            </a>
                        </template>
                        <template x-if="detalle?.email">
                            <a :href="'mailto:' + detalle.email"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <svg class="h-3.5 w-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="max-w-[180px] truncate lowercase" x-text="detalle.email"></span>
                            </a>
                        </template>
                        <template x-if="detalle?.ruc">
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                RUC: <span class="font-medium" x-text="detalle.ruc"></span>
                            </span>
                        </template>
                    </div>

                    {{-- Horario --}}
                    <div x-show="detalle?.horarioAtencion" class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 p-3 dark:border-blue-900/30 dark:bg-blue-900/10">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-blue-700 dark:text-blue-400">Horario de atención</p>
                            <p class="mt-0.5 text-xs text-blue-600 dark:text-blue-300" x-text="detalle?.horarioAtencion"></p>
                        </div>
                    </div>

                    {{-- Condición de venta --}}
                    <div x-show="detalle?.condicionVenta"
                        :class="detalle?.condicionVenta?.toLowerCase().includes('receta') ? 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/10' : 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/10'"
                        class="flex items-center gap-2 rounded-xl border p-3">
                        <svg class="h-4 w-4 flex-shrink-0"
                            :class="detalle?.condicionVenta?.toLowerCase().includes('receta') ? 'text-amber-500' : 'text-green-500'"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-xs font-medium"
                            :class="detalle?.condicionVenta?.toLowerCase().includes('receta') ? 'text-amber-700 dark:text-amber-400' : 'text-green-700 dark:text-green-400'"
                            x-text="detalle?.condicionVenta"></span>
                    </div>

                    {{-- Info del producto --}}
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Información del producto</p>
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Nombre</dt>
                                <dd class="font-medium text-gray-900 dark:text-white" x-text="detalle?.nombreProducto ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Presentación</dt>
                                <dd class="font-medium text-gray-900 dark:text-white" x-text="detalle?.presentacion ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Reg. sanitario</dt>
                                <dd class="font-mono text-xs font-medium text-gray-900 dark:text-white" x-text="detalle?.registroSanitario ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Tipo de producto</dt>
                                <dd class="font-medium text-gray-900 dark:text-white" x-text="detalle?.tipoProducto ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Laboratorio</dt>
                                <dd class="font-medium text-gray-900 dark:text-white" x-text="detalle?.laboratorio ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">País de fabricación</dt>
                                <dd class="font-medium text-gray-900 dark:text-white" x-text="detalle?.paisFabricacion ?? '—'"></dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Titular</dt>
                                <dd class="font-medium text-gray-900 dark:text-white" x-text="detalle?.nombreTitular ?? '—'"></dd>
                            </div>
                            <div x-show="detalle?.directorTecnico" class="col-span-2">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Director técnico</dt>
                                <dd class="font-medium text-gray-900 dark:text-white" x-text="detalle?.directorTecnico"></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end border-t border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                <button @click="modalDetalle = false"
                    class="rounded-xl border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

</div>{{-- fin x-data --}}

@push('scripts')
{{-- reCAPTCHA v3 - necesario para autenticar las peticiones a la API DIGEMID --}}
<script src="https://www.google.com/recaptcha/api.js?render={{ env('VITE_RECAPTCHA_DIGEMID_SITE_KEY', '6LfTRbUZAAAAAC4PZ6QF3Oz_IElVRZpnW9zzA7JD') }}" async defer></script>
<script>
const RECAPTCHA_SITE_KEY = '{{ env('VITE_RECAPTCHA_DIGEMID_SITE_KEY', '6LfTRbUZAAAAAC4PZ6QF3Oz_IElVRZpnW9zzA7JD') }}';

async function getRecaptchaToken(action = 'ciudadano') {
    return new Promise((resolve) => {
        if (typeof grecaptcha === 'undefined') { resolve(''); return; }
        grecaptcha.ready(() => {
            grecaptcha.execute(RECAPTCHA_SITE_KEY, { action })
                .then(token => resolve(token))
                .catch(() => resolve(''));
        });
    });
}

function consultaPrecios() {
    return {
        // Producto
        productoQuery: '',
        sugerencias: [],
        productoSeleccionado: null,
        showSuggestions: false,
        sinResultadosProducto: false,
        sugerenciaActiva: -1,
        loadingProducto: false,

        // Ubicación cascada
        provincias: [],
        distritos: [],
        loadingProvincias: false,
        loadingDistritos: false,

        // Filtros de búsqueda
        filtros: {
            codigoDepartamento: '',
            codigoProvincia: '',
            codigoUbigeo: '',
            codTipoEstablecimiento: '',
            nombreEstablecimiento: null,
            nombreLaboratorio: null,
        },

        // Resultados
        resultados: [],
        loadingPrecios: false,
        buscado: false,
        errorMsg: '',

        // Filtros de tabla
        filtroTabla: '',
        busquedaTabla: '',
        ordenPrecio: '',   // '' | 'asc' | 'desc'

        // Paginación
        paginaActual: 1,
        porPagina: 10,

        // Modal detalle
        modalDetalle: false,
        detalle: null,
        loadingDetalle: false,
        errorDetalle: '',

        init() {},

        // ── Computed ──

        get puedeConsultar() { return !!this.productoSeleccionado; },

        get precioMinimo() {
            const p = this.resultados.map(r => parseFloat(r.precio2 ?? r.precio1 ?? 0)).filter(v => v > 0);
            return p.length ? Math.min(...p) : 0;
        },
        get precioMaximo() {
            const p = this.resultados.map(r => parseFloat(r.precio2 ?? r.precio1 ?? 0)).filter(v => v > 0);
            return p.length ? Math.max(...p) : 0;
        },
        get precioPromedio() {
            const p = this.resultados.map(r => parseFloat(r.precio2 ?? r.precio1 ?? 0)).filter(v => v > 0);
            return p.length ? p.reduce((a, b) => a + b, 0) / p.length : 0;
        },

        get resultadosFiltrados() {
            let items = this.resultados;
            if (this.filtroTabla)
                items = items.filter(r => r.setcodigo === this.filtroTabla);
            if (this.busquedaTabla.trim()) {
                const q = this.busquedaTabla.toLowerCase();
                items = items.filter(r =>
                    (r.nombreComercial ?? '').toLowerCase().includes(q) ||
                    (r.nombreLaboratorio ?? '').toLowerCase().includes(q) ||
                    (r.direccion ?? '').toLowerCase().includes(q)
                );
            }
            if (this.ordenPrecio === 'asc')
                items = [...items].sort((a, b) => parseFloat(a.precio2 ?? a.precio1 ?? 0) - parseFloat(b.precio2 ?? b.precio1 ?? 0));
            if (this.ordenPrecio === 'desc')
                items = [...items].sort((a, b) => parseFloat(b.precio2 ?? b.precio1 ?? 0) - parseFloat(a.precio2 ?? a.precio1 ?? 0));
            return items;
        },

        get totalPaginas() {
            return Math.max(1, Math.ceil(this.resultadosFiltrados.length / Number(this.porPagina)));
        },

        get paginados() {
            const pp = Number(this.porPagina);
            const start = (this.paginaActual - 1) * pp;
            return this.resultadosFiltrados.slice(start, start + pp);
        },

        get paginasVisibles() {
            const total = this.totalPaginas;
            const cur = this.paginaActual;
            if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
            const pages = [];
            if (cur <= 4) {
                pages.push(1, 2, 3, 4, 5, '...', total);
            } else if (cur >= total - 3) {
                pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total);
            } else {
                pages.push(1, '...', cur - 1, cur, cur + 1, '...', total);
            }
            return pages;
        },

        // ── Métodos ──

        toggleOrden() {
            this.ordenPrecio = this.ordenPrecio === '' ? 'asc' : this.ordenPrecio === 'asc' ? 'desc' : '';
            this.paginaActual = 1;
        },

        async buscarProducto() {
            const q = this.productoQuery.trim();
            if (q.length < 2) {
                this.sugerencias = [];
                this.showSuggestions = false;
                this.sinResultadosProducto = false;
                return;
            }
            this.loadingProducto = true;
            this.sinResultadosProducto = false;
            try {
                const r = await fetch(`{{ route('consulta-precios.autocomplete') }}?query=${encodeURIComponent(q)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await r.json();
                this.sugerencias = Array.isArray(data) ? data.slice(0, 12) : [];
                this.showSuggestions = this.sugerencias.length > 0;
                this.sinResultadosProducto = this.sugerencias.length === 0;
                this.sugerenciaActiva = -1;
            } catch (e) {
                this.sugerencias = [];
                this.sinResultadosProducto = true;
            } finally {
                this.loadingProducto = false;
            }
        },

        navegarSugerencias(dir) {
            if (!this.sugerencias.length) return;
            this.sugerenciaActiva = Math.max(-1, Math.min(this.sugerencias.length - 1, this.sugerenciaActiva + dir));
        },

        seleccionarSugerenciaActiva() {
            if (this.sugerenciaActiva >= 0 && this.sugerencias[this.sugerenciaActiva])
                this.seleccionarProducto(this.sugerencias[this.sugerenciaActiva]);
        },

        seleccionarProducto(item) {
            this.productoSeleccionado = item;
            this.productoQuery = item.nombreProducto + ' ' + item.concent;
            this.sugerencias = [];
            this.showSuggestions = false;
            this.sinResultadosProducto = false;
            this.sugerenciaActiva = -1;
        },

        limpiarProducto() {
            this.productoSeleccionado = null;
            this.productoQuery = '';
            this.sugerencias = [];
            this.sinResultadosProducto = false;
        },

        async onDepartamentoChange() {
            this.filtros.codigoProvincia = '';
            this.filtros.codigoUbigeo = '';
            this.provincias = [];
            this.distritos = [];
            if (!this.filtros.codigoDepartamento) return;
            this.loadingProvincias = true;
            try {
                const r = await fetch(`{{ route('consulta-precios.provincias') }}?codigoDepartamento=${this.filtros.codigoDepartamento}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                this.provincias = await r.json();
            } finally {
                this.loadingProvincias = false;
            }
        },

        async onProvinciaChange() {
            this.filtros.codigoUbigeo = '';
            this.distritos = [];
            if (!this.filtros.codigoProvincia) return;
            this.loadingDistritos = true;
            try {
                const r = await fetch(`{{ route('consulta-precios.distritos') }}?codigoDepartamento=${this.filtros.codigoDepartamento}&codigoProvincia=${this.filtros.codigoProvincia}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                this.distritos = await r.json();
            } finally {
                this.loadingDistritos = false;
            }
        },

        async buscarPrecios() {
            if (!this.puedeConsultar) return;
            this.loadingPrecios = true;
            this.errorMsg = '';
            this.buscado = false;
            this.resultados = [];
            this.paginaActual = 1;
            this.busquedaTabla = '';
            this.filtroTabla = '';
            this.ordenPrecio = '';

            try {
                const tokenGoogle = await getRecaptchaToken('ciudadano');

                const r = await fetch('{{ route("consulta-precios.buscar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        codigoProducto:         this.productoSeleccionado.grupo,
                        codigoDepartamento:     this.filtros.codigoDepartamento || null,
                        codigoProvincia:        this.filtros.codigoProvincia || null,
                        codigoUbigeo:           this.filtros.codigoUbigeo || null,
                        codTipoEstablecimiento: this.filtros.codTipoEstablecimiento || null,
                        nombreEstablecimiento:  this.filtros.nombreEstablecimiento || null,
                        nombreLaboratorio:      this.filtros.nombreLaboratorio || null,
                        codGrupoFF:             this.productoSeleccionado.codGrupoFF,
                        concent:                this.productoSeleccionado.concent,
                        pagina:                 1,
                        tokenGoogle:            tokenGoogle,
                    }),
                });
                const data = await r.json();
                if (data.success) {
                    this.resultados = data.data ?? [];
                } else {
                    this.errorMsg = data.message ?? 'Error al consultar el servicio DIGEMID.';
                }
            } catch (e) {
                this.errorMsg = 'Error de conexión. Por favor, intente nuevamente.';
            } finally {
                this.loadingPrecios = false;
                this.buscado = true;
            }
        },

        limpiarFiltros() {
            this.limpiarProducto();
            this.filtros = { codigoDepartamento: '', codigoProvincia: '', codigoUbigeo: '', codTipoEstablecimiento: '', nombreEstablecimiento: null, nombreLaboratorio: null };
            this.provincias = [];
            this.distritos = [];
            this.resultados = [];
            this.buscado = false;
            this.errorMsg = '';
            this.busquedaTabla = '';
            this.filtroTabla = '';
            this.ordenPrecio = '';
            this.paginaActual = 1;
        },

        async verDetalle(item) {
            this.modalDetalle = true;
            this.loadingDetalle = true;
            this.detalle = null;
            this.errorDetalle = '';
            try {
                const tokenGoogle = await getRecaptchaToken('detalle');

                const r = await fetch('{{ route("consulta-precios.detalle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        codigoProducto:     item.codProdE,
                        codEstablecimiento: item.codEstab,
                        tokenGoogle:        tokenGoogle,
                    }),
                });
                const data = await r.json();
                if (data.success && data.data) {
                    this.detalle = data.data;
                } else {
                    this.errorDetalle = data.message ?? 'No se pudo obtener el detalle.';
                }
            } catch (e) {
                this.errorDetalle = 'Error de conexión al obtener el detalle.';
            } finally {
                this.loadingDetalle = false;
            }
        },

        async exportarXLSX() {
            const XLSX = window.XLSX ?? (await import('xlsx').catch(() => null));
            if (!XLSX) {
                alert('No se pudo cargar la librería de exportación. Recargue la página.');
                return;
            }
            const producto = this.productoSeleccionado
                ? (this.productoSeleccionado.nombreProducto + ' ' + this.productoSeleccionado.concent).trim()
                : 'Precios';

            const cols = ['Tipo','Farmacia / Botica','Producto','Concentración','Forma farmacéutica','Laboratorio','Departamento','Provincia','Distrito','Dirección','Teléfono','P. Pack (S/)','P. Unitario (S/)','Fecha actualización'];
            const rows = this.resultadosFiltrados.map(r => [
                r.setcodigo ?? '',
                r.nombreComercial ?? '',
                r.nombreProducto ?? r.nombreSustancia ?? '',
                r.concent ?? '',
                r.nombreFormaFarmaceutica ?? '',
                r.nombreLaboratorio ?? '',
                r.departamento ?? '',
                r.provincia ?? '',
                r.distrito ?? '',
                r.direccion ?? '',
                r.telefono ?? '',
                r.precio1 != null ? Number(r.precio1) : '',
                r.precio2 != null ? Number(r.precio2) : '',
                r.fecha ?? '',
            ]);

            const wsData = [cols, ...rows];
            const ws = XLSX.utils.aoa_to_sheet(wsData);

            // Column widths
            ws['!cols'] = [
                {wch:10},{wch:30},{wch:25},{wch:14},{wch:22},{wch:28},
                {wch:16},{wch:16},{wch:16},{wch:38},{wch:14},{wch:14},{wch:14},{wch:18},
            ];

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Precios');

            const filename = `precios_${producto.replace(/\s+/g,'_').slice(0,30)}_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, filename);
        },
    };
}
</script>
@endpush
@endsection

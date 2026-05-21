@props([
    'isMultiRow' => false,
    'toolbarLeftBefore' => null,
    'toolbarLeftAfter' => null,
    'toolbarRightBefore' => null,
    'toolbarRightAfter' => null,
])

<v-datagrid {{ $attributes }}>
    {{ $slot }}
</v-datagrid>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-datagrid-template"
    >
        <div>
            <!-- Toolbar -->
            <x-admin::datagrid.toolbar>
                <x-slot:toolbar-left-before>
                    {{ $toolbarLeftBefore }}
                </x-slot>
                
                <x-slot:toolbar-left-after>
                    {{ $toolbarLeftAfter }}
                </x-slot>
                
                <x-slot:toolbar-right-before>
                    {{ $toolbarRightBefore }}
                </x-slot>
                
                <x-slot:toolbar-right-after>
                    {{ $toolbarRightAfter }}
                </x-slot>
            </x-admin::datagrid.toolbar>

            <div class="flex">
                <x-admin::datagrid.table :isMultiRow="$isMultiRow">
                    <template #header="{
                        isLoading,
                        available,
                        applied,
                        selectAll,
                        sort,
                        performAction
                    }">
                        <slot
                            name="header"
                            :is-loading="isLoading"
                            :available="available"
                            :applied="applied"
                            :select-all="selectAll"
                            :sort="sort"
                            :perform-action="performAction"
                        >
                        </slot>
                    </template>

                    <template #body="{
                        isLoading,
                        available,
                        applied,
                        selectAll,
                        sort,
                        performAction
                    }">
                        <slot
                            name="body"
                            :is-loading="isLoading"
                            :available="available"
                            :applied="applied"
                            :select-all="selectAll"
                            :sort="sort"
                            :perform-action="performAction"
                        >
                        </slot>
                    </template>
                </x-admin::datagrid.table>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-datagrid', {
            template: '#v-datagrid-template',

            props: ['src'],

            data() {
                return {
                    isLoading: false,

                    available: {
                        id: null,

                        columns: [],

                        actions: [],

                        massActions: [],

                        records: [],

                        meta: {},
                    },

                    applied: {
                        massActions: {
                            meta: {
                                mode: 'none',

                                action: null,
                            },

                            indices: [],

                            value: null,
                        },

                        pagination: {
                            page: 1,

                            perPage: 10,
                        },

                        sort: {
                            column: null,

                            order: null,
                        },

                        filters: {
                            columns: [
                                {
                                    index: 'all',
                                    value: [],
                                },
                            ],
                        },

                        savedFilterId: null,
                    },
                };
            },

            watch: {
                'available.records': function (newRecords, oldRecords) {
                    this.setCurrentSelectionMode();

                    this.updateDatagrids();

                    this.updateExportComponent();
                },

                'applied.savedFilterId': function (newSavedFilterId, oldSavedFilterId) {
                    this.updateDatagrids();
                },

                'applied.massActions.indices': function (newIndices, oldIndices) {
                    this.setCurrentSelectionMode();
                },
            },

            mounted() {
                this.boot();
            },

            methods: {
                /**
                 * Inicialización: esta función busca filtros previamente guardados en el almacenamiento local y los aplica según sea necesario.
                 *
                 * @returns {void}
                 */
                boot() {
                    let datagrids = this.getDatagrids();

                    const urlParams = new URLSearchParams(window.location.search);

                    if (urlParams.has('search')) {
                        let searchAppliedColumn = this.applied.filters.columns.find(column => column.index === 'all');

                        searchAppliedColumn.value = [urlParams.get('search')];
                    }

                    if (datagrids?.length) {
                        const currentDatagrid = datagrids.find(({ src }) => src === this.src);

                        if (currentDatagrid) {
                            this.applied.pagination = currentDatagrid.applied.pagination;

                            this.applied.sort = currentDatagrid.applied.sort;

                            this.applied.filters = currentDatagrid.applied.filters;

                            this.applied.savedFilterId = currentDatagrid.applied.savedFilterId;

                            if (urlParams.has('search')) {
                                let searchAppliedColumn = this.applied.filters.columns.find(column => column.index === 'all');

                                searchAppliedColumn.value = [urlParams.get('search')];
                            }

                            this.get();

                            return;
                        }
                    }

                    this.get();
                },

                /**
                 * Conseguir. Esto preparará los parámetros de los accesorios "aplicados" y obtendrá los datos del backend.
                 *
                 * @returns {void}
                 */
                get(extraParams = {}) {
                    let params = {
                        pagination: {
                            page: this.applied.pagination.page,
                            per_page: this.applied.pagination.perPage,
                        },

                        sort: {},

                        filters: {},
                    };

                    if (
                        this.applied.sort.column &&
                        this.applied.sort.order
                    ) {
                        params.sort = this.applied.sort;
                    }

                    this.applied.filters.columns.forEach(column => {
                        params.filters[column.index] = column.value;
                    });

                    const urlParams = new URLSearchParams(window.location.search);

                    urlParams.forEach((param, key) => params[key] = param);

                    this.isLoading = true;

                    this.$axios
                        .get(this.src, {
                            params: { ...params, ...extraParams }
                        })
                        .then((response) => {
                            /**
                             * Precisamente tomar todas las claves de la propiedad de datos para evitar agregar claves adicionales de la respuesta.
                             */
                            const {
                                id,
                                columns,
                                actions,
                                mass_actions,
                                records,
                                meta
                            } = response.data;

                            this.available.id = id;

                            this.available.columns = columns;

                            this.available.actions = actions;

                            this.available.massActions = mass_actions;

                            this.available.records = records;

                            this.available.meta = meta;

                            this.isLoading = false;
                        });
                },

                /**
                 * Cambiar página. Cuando el componente secundario haya manejado todos los casos, enviará el
                 * nueva página válida; de lo contrario, se bloqueará. Aquí estamos seguros de que tenemos
                 * una nueva página, por lo que el padre simplemente llamará al AJAX en función de la nueva página.
                 *
                 * @param {integer} newPage
                 * @returns {void}
                 */
                changePage(newPage) {
                    this.applied.pagination.page = newPage;

                    this.get();
                },

                /**
                 * Opción de cambio por página.
                 *
                 * @param {integer} option
                 * @returns {void}
                 */
                changePerPageOption(option) {
                    this.applied.pagination.perPage = option;

                    /**
                     * Cuando el total de registros es menor que la cantidad de datos por página, debemos restablecer la página.
                     */
                    if (this.available.meta.last_page >= this.applied.pagination.page) {
                        this.applied.pagination.page = 1;
                    }

                    this.get();
                },

                /**
                 * Ordenar resultados.
                 *
                 * @param {object} column
                 * @returns {void}
                 */
                sort(column) {
                    if (column.sortable) {
                        this.applied.sort = {
                            column: column.index,
                            order: this.applied.sort.order === 'asc' ? 'desc' : 'asc',
                        };

                        /**
                         * Cuando cambia la clasificación, debemos restablecer la página.
                         */
                        this.applied.pagination.page = 1;

                        this.get();
                    }
                },

                /**
                 * Resultados de la búsqueda.
                 *
                 * @param {object} filters
                 * @returns {void}
                 */
                search(filters) {
                    this.applied.filters.columns = [
                        ...(this.applied.filters.columns.filter((column) => column.index !== 'all')),
                        ...filters.columns,
                    ];

                    /**
                     * Necesitamos restablecer la página al filtrar.
                     */
                    this.applied.pagination.page = 1;

                    this.get();
                },

                /**
                 * Filtrar resultados.
                 *
                 * @param {object} filters
                 * @returns {void}
                 */
                 filter(filters) {
                    this.applied.filters.columns = [
                        ...(this.applied.filters.columns.filter((column) => column.index === 'all')),
                        ...filters.columns,
                    ];

                    /**
                     * Esto comprobará si hay valores de columna vacíos y restablecerá el ID del filtro guardado para garantizar que el filtro guardado no esté resaltado.
                     */
                    const isEmptyColumnValue = this.applied.filters.columns
                        .filter((column) => column.index !== 'all')
                        .every((column) => column.value.length === 0);

                    if (isEmptyColumnValue) {
                        this.applied.savedFilterId = null;
                    }

                    /**
                     * Necesitamos restablecer la página al filtrar.
                     */
                    this.applied.pagination.page = 1;

                    this.get();
                },

                /**
                 * Filtrar los resultados por el filtro guardado.
                 *
                 * @param {Object} filter
                 * @returns {void}
                 */
                 applySavedFilter(filter) {
                    if (! filter) {
                        this.applied.savedFilterId = null;

                        return;
                    }

                    this.applied = filter.applied;

                    this.applied.savedFilterId = filter.id;

                    this.get();
                },

                /**
                 * Esto analizará el modo de selección actual en función de los índices de acción masiva.
                 *
                 * @returns {void}
                 */
                setCurrentSelectionMode() {
                    this.applied.massActions.meta.mode = 'none';

                    if (! this.available.records.length) {
                        return;
                    }

                    let selectionCount = 0;

                    this.available.records.forEach(record => {
                        const id = record[this.available.meta.primary_column];

                        if (this.applied.massActions.indices.includes(id)) {
                            this.applied.massActions.meta.mode = 'partial';

                            ++selectionCount;
                        }
                    });

                    if (this.available.records.length === selectionCount) {
                        this.applied.massActions.meta.mode = 'all';
                    }
                },

                /**
                 * Esto seleccionará todos los registros y actualizará los índices de acción masiva.
                 *
                 * @returns {void}
                 */
                selectAll() {
                    if (['all', 'partial'].includes(this.applied.massActions.meta.mode)) {
                        this.available.records.forEach(record => {
                            const id = record[this.available.meta.primary_column];

                            this.applied.massActions.indices = this.applied.massActions.indices.filter(selectedId => selectedId !== id);
                        });

                        this.applied.massActions.meta.mode = 'none';
                    } else {
                        this.available.records.forEach(record => {
                            const id = record[this.available.meta.primary_column];

                            let found = this.applied.massActions.indices.find(selectedId => selectedId === id);

                            if (! found) {
                                this.applied.massActions.indices = [
                                    ...this.applied.massActions.indices,
                                    id,
                                ];
                            }
                        });

                        this.applied.massActions.meta.mode = 'all';
                    }
                },

                /**
                 * Actualiza las propiedades del componente de exportación cada vez que aparecen nuevos resultados en la cuadrícula de datos.
                 *
                 * @returns {void}
                 */
                 updateExportComponent() {
                    /**
                     * Este evento debe activarse cada vez que aparecen nuevos resultados. Esto permite que la función de exportación
                     * escúchelo y actualice sus propiedades en consecuencia.
                     */
                     this.$emitter.emit('change-datagrid', {
                        available: this.available,
                        applied: this.applied
                    });
                },

                //=======================================================================================
                // Soporte para valores aplicados previamente en datagrids. Todo el código se basa en el almacenamiento local.
                //=======================================================================================

                /**
                 * Actualiza las cuadrículas de datos almacenadas en el almacenamiento local con los datos más recientes.
                 *
                 * @returns {void}
                 */
                updateDatagrids() {
                    let datagrids = this.getDatagrids();

                    if (datagrids?.length) {
                        const currentDatagrid = datagrids.find(({ src }) => src === this.src);

                        if (currentDatagrid) {
                            datagrids = datagrids.map(datagrid => {
                                if (datagrid.src === this.src) {
                                    return {
                                        ...datagrid,
                                        requestCount: ++datagrid.requestCount,
                                        available: this.available,
                                        applied: this.applied,
                                    };
                                }

                                return datagrid;
                            });
                        } else {
                            datagrids.push(this.getDatagridInitialProperties());
                        }
                    } else {
                        datagrids = [this.getDatagridInitialProperties()];
                    }

                    this.setDatagrids(datagrids);
                },

                /**
                 * Devuelve las propiedades iniciales de una cuadrícula de datos.
                 *
                 * @returns {object} Initial properties for a datagrid.
                 */
                getDatagridInitialProperties() {
                    return {
                        src: this.src,
                        requestCount: 0,
                        available: this.available,
                        applied: this.applied,
                    };
                },

                /**
                 * Devuelve la clave de almacenamiento para las cuadrículas de datos en el almacenamiento local.
                 *
                 * @returns {string} Storage key for datagrids.
                 */
                getDatagridsStorageKey() {
                    return 'datagrids';
                },

                /**
                 * Recupera las cuadrículas de datos almacenadas en el almacenamiento local.
                 *
                 * @returns {Array} Datagrids stored in local storage.
                 */
                getDatagrids() {
                    let datagrids = localStorage.getItem(
                        this.getDatagridsStorageKey()
                    );

                    return JSON.parse(datagrids) ?? [];
                },

                /**
                 * Establece las cuadrículas de datos en el almacenamiento local.
                 *
                 * @param {Array} datagrids - Datagrids to be stored in local storage.
                 * @returns {void}
                 */
                setDatagrids(datagrids) {
                    localStorage.setItem(
                        this.getDatagridsStorageKey(),
                        JSON.stringify(datagrids)
                    );
                },
            },
        });
    </script>
@endPushOnce

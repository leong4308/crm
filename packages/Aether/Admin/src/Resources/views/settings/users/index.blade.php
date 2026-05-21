<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.users.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="settings.users" />

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.users.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                {!! view_render_event('admin.settings.users.index.create_button.before') !!}

                <!-- Create button for User -->
                @if (bouncer()->hasPermission('settings.user.users.create'))
                    <div class="flex items-center gap-x-2.5">
                        <a
                            href="{{ route('admin.settings.users.create') }}"
                            class="primary-button"
                        >
                            @lang('admin::app.settings.users.index.create-btn')
                        </a>
                    </div>
                @endif

                {!! view_render_event('admin.settings.users.index.create_button.after') !!}
            </div>
        </div>

        <v-users-settings ref="userSettings">
            <!-- DataGrid Shimmer -->
            <x-admin::shimmer.datagrid />
        </v-users-settings>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="users-settings-template"
        >
            <div>
                {!! view_render_event('admin.settings.users.index.datagrid.before') !!}

                <!-- Datagrid -->
                <x-admin::datagrid
                    :src="route('admin.settings.users.index')"
                    ref="datagrid"
                >
                    <template #body="{
                        isLoading,
                        available,
                        applied,
                        selectAll,
                        sort,
                        performAction
                    }">
                        <template v-if="isLoading">
                            <x-admin::shimmer.datagrid.table.body />
                        </template>

                        <template v-else>
                            <div
                                v-for="record in available.records"
                                class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950 max-lg:hidden"
                                :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                            >
                                <!-- Mass Actions -->
                                <div class="flex select-none items-center gap-16">
                                    <input
                                        type="checkbox"
                                        :name="`mass_action_select_record_${record.id}`"
                                        :id="`mass_action_select_record_${record.id}`"
                                        :value="record.id"
                                        class="peer hidden"
                                        v-model="applied.massActions.indices"
                                    >

                                    <label
                                        class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-600 peer-checked:text-brandColor dark:text-gray-300"
                                        :for="`mass_action_select_record_${record.id}`"
                                    ></label>
                                </div>

                                <!-- Users Id -->
                                <p>@{{ record.id }}</p>

                                <!-- Users Name -->
                                <div class="flex items-center gap-2.5">
                                    <x-admin::avatar ::name="record.name.name"/>
                                    <div class="text-sm">@{{ record.name.name }}</div>
                                </div>

                                <!-- Users Email -->
                                <p class="truncate">@{{ record.email }}</p>

                                <!-- Users Status -->
                                <span :class="record.status == 1 ? 'label-active' : 'label-inactive'">
                                    @{{ record.status == 1 ? '@lang('admin::app.settings.users.index.active')' : '@lang('admin::app.settings.users.index.inactive')' }}
                                </span>

                                <!-- Users Creation Date -->
                                <p>@{{ record.created_at }}</p>

                                <!-- Actions -->
                                <div class="flex justify-end">
                                    <a :href="'{{ route('admin.settings.users.edit', ':id') }}'.replace(':id', record.id)">
                                        <span class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></span>
                                    </a>

                                    <a @click="performAction(record.actions.find(action => action.index === 'delete'))">
                                        <span class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></span>
                                    </a>
                                </div>
                            </div>
                        </template>
                    </template>
                </x-admin::datagrid>
            </div>
        </script>

        <script type="module">
            app.component('v-users-settings', {
                template: '#users-settings-template',
                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;
                        if (this.$refs.datagrid.available.actions.length) ++count;
                        if (this.$refs.datagrid.available.massActions.length) ++count;
                        return count;
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>

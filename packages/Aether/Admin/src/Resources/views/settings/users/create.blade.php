<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.users.index.create.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.settings.users.store')"
        enctype="multipart/form-data"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <x-admin::breadcrumbs name="settings.users.create" />

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.settings.users.index.create.title')
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('admin::app.settings.users.index.create.save-btn')
                    </button>
                </div>
            </div>

            <v-users-settings-form :roles='@json($roles)' :groups='@json($groups)'></v-users-settings-form>
        </div>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-users-settings-form-template">
            <div class="flex gap-4">
                <!-- Left Column: User Details -->
                <div class="flex flex-col gap-4 flex-1">
                    <div class="rounded-lg border border-gray-300 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-bold text-gray-800 dark:text-white">Datos Personales</p>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.users.index.create.name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="name"
                                rules="required"
                                v-model="user.name"
                                :label="trans('admin::app.settings.users.index.create.name')"
                                placeholder="Nombre completo"
                            />

                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.users.index.create.email')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="email"
                                name="email"
                                rules="required|email"
                                v-model="user.email"
                                :label="trans('admin::app.settings.users.index.create.email')"
                                placeholder="email@ejemplo.com"
                            />

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <div class="flex gap-4">
                            <x-admin::form.control-group class="flex-1">
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.users.index.create.password')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="password"
                                    name="password"
                                    rules="required|min:6"
                                    v-model="user.password"
                                    :label="trans('admin::app.settings.users.index.create.password')"
                                    placeholder="Mínimo 6 caracteres"
                                />

                                <x-admin::form.control-group.error control-name="password" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group class="flex-1">
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.users.index.create.confirm-password')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="password"
                                    name="password_confirmation"
                                    rules="confirmed:@password"
                                    v-model="user.password_confirmation"
                                    :label="trans('admin::app.settings.users.index.create.confirm-password')"
                                    placeholder="Repite la contraseña"
                                />

                                <x-admin::form.control-group.error control-name="password_confirmation" />
                            </x-admin::form.control-group>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-300 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-bold text-gray-800 dark:text-white">Asignación de Grupos</p>

                        <x-admin::form.control-group>
                            <v-field
                                name="groups[]"
                                label="@lang('admin::app.settings.users.index.create.group')"
                                multiple
                                v-model="user.groups"
                                rules="required"
                            >
                                <select
                                    name="groups[]"
                                    class="flex min-h-[150px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                    multiple
                                    v-model="user.groups"
                                >
                                    <option v-for="group in groups" :value="group.id">@{{ group.name }}</option>
                                </select>
                            </v-field>
                            <p class="mt-1 text-xs text-gray-500">Mantén presionado Ctrl (o Cmd) para seleccionar varios</p>

                            <x-admin::form.control-group.error name="groups[]" />
                        </x-admin::form.control-group>
                    </div>

                    <div class="rounded-lg border border-gray-300 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-bold text-gray-800 dark:text-white">Estado y Visibilidad</p>

                        <div class="flex gap-10">
                            <x-admin::form.control-group class="flex-1">
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.users.index.create.view-permission')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="view_permission"
                                    rules="required"
                                    v-model="user.view_permission"
                                >
                                    <option value="global">@lang('admin::app.settings.users.index.create.global')</option>
                                    <option value="group">@lang('admin::app.settings.users.index.create.group')</option>
                                    <option value="individual">@lang('admin::app.settings.users.index.create.individual')</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <x-admin::form.control-group class="flex-1">
                                <x-admin::form.control-group.label>
                                    Estado de la cuenta
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    v-model="user.status"
                                    value="1"
                                    ::checked="user.status"
                                />
                            </x-admin::form.control-group>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Role & Permissions -->
                <div class="flex flex-col gap-4 flex-1">
                    <div class="rounded-lg border border-gray-300 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="mb-4 text-base font-bold text-gray-800 dark:text-white">Rol del Sistema</p>

                        <x-admin::form.control-group v-if="!user.create_new_role">
                            <x-admin::form.control-group.label class="required">Selecciona un Rol</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="select"
                                name="role_id"
                                rules="required"
                                v-model="user.role_id"
                            >
                                <option value="">--- Seleccionar ---</option>
                                <option v-for="role in roles" :value="role.id">@{{ role.name }}</option>
                            </x-admin::form.control-group.control>
                        </x-admin::form.control-group>

                        <x-admin::form.control-group class="flex items-center gap-2.5">
                            <input type="checkbox" name="create_new_role" v-model="user.create_new_role" id="create_new_role" class="peer hidden" />
                            <label class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-600 peer-checked:text-brandColor" for="create_new_role"></label>
                            <label for="create_new_role" class="cursor-pointer text-sm font-semibold text-gray-600 dark:text-gray-300">¿Crear un nuevo rol personalizado?</label>
                        </x-admin::form.control-group>

                        <template v-if="user.create_new_role">
                            <div class="mt-4 space-y-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                                <div class="flex gap-4">
                                    <x-admin::form.control-group class="flex-1">
                                        <x-admin::form.control-group.label class="required">Nombre del Rol</x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="text" name="role_name" rules="required" v-model="user.role_name" placeholder="Ej: Vendedor Premium" />
                                    </x-admin::form.control-group>
                                    <x-admin::form.control-group class="flex-1">
                                        <x-admin::form.control-group.label class="required">Descripción</x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="text" name="role_description" rules="required" v-model="user.role_description" placeholder="¿Qué hace?" />
                                    </x-admin::form.control-group>
                                </div>
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">Tipo de Acceso</x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control type="select" name="permission_type" v-model="user.permission_type">
                                        <option value="custom">Personalizado</option>
                                        <option value="all">Acceso Total</option>
                                    </x-admin::form.control-group.control>
                                </x-admin::form.control-group>
                            </div>
                        </template>

                        <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-800" v-if="user.role_id || user.create_new_role">
                            <div class="flex items-center justify-between mb-4">
                                <p class="text-sm font-bold text-gray-800 dark:text-white">Configuración de Permisos</p>
                                <button type="button" class="text-xs font-bold text-brandColor hover:underline" @click.stop="collapseAll">Contraer todo</button>
                            </div>

                            <div class="tree-container max-h-[800px] overflow-y-auto rounded-lg border border-gray-200 bg-white p-4 dark:bg-black">
                                <x-admin::tree.view
                                    input-type="checkbox"
                                    value-field="key"
                                    id-field="key"
                                    :items="json_encode(acl()->getItems())"
                                    :fallback-locale="config('app.fallback_locale')"
                                    ::value="user.permissions"
                                    ::key="'tree-view-' + user.role_id"
                                    :collapse="true"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-users-settings-form', {
                template: '#v-users-settings-form-template',
                props: ['roles', 'groups'],
                data() {
                    return {
                        user: {
                            name: '',
                            email: '',
                            password: '',
                            password_confirmation: '',
                            role_id: '',
                            view_permission: 'global',
                            status: 1,
                            create_new_role: false,
                            permission_type: 'custom',
                            permissions: [],
                            groups: [],
                        },
                    };
                },
                watch: {
                    'user.name': function(newName) {
                        if (this.user.create_new_role && (!this.user.role_name || this.user.role_name.startsWith('Rol de '))) {
                            this.user.role_name = newName ? `Rol de ${newName}` : '';
                        }
                    },
                    'user.role_id': function(newRoleId) {
                        const role = this.roles.find(r => r.id == newRoleId);
                        if (role) {
                            if (role.permission_type === 'all') {
                                this.user.permissions = this.getAllPermissionKeys();
                                this.user.permission_type = 'all';
                            } else {
                                try {
                                    let permissions = typeof role.permissions === 'string' ? JSON.parse(role.permissions) : role.permissions;
                                    permissions = Array.isArray(permissions) ? permissions : [];
                                    
                                    // Limpiar 'administrador'. prefijo si existe para que coincida con las claves ACL
                                    this.user.permissions = permissions.map(p => p.startsWith('admin.') ? p.substring(6) : p);
                                    
                                    this.user.permission_type = 'custom';
                                } catch (e) { 
                                    this.user.permissions = []; 
                                }
                            }

                            // Expandir carpetas automáticamente después de las actualizaciones de la interfaz de usuario
                            this.$nextTick(() => {
                                setTimeout(() => {
                                    this.autoExpandSelected();
                                }, 500);
                            });
                        }
                    }
                },
                methods: {
                    getAllPermissionKeys() {
                        const items = @json(acl()->getItems());
                        const keys = [];
                        const traverse = (item) => {
                            if (item.key) keys.push(item.key);
                            if (item.children) {
                                Object.values(item.children).forEach(traverse);
                            }
                        };
                        items.forEach(traverse);
                        return keys;
                    },

                    autoExpandSelected() {
                        const container = document.querySelector('.tree-container');
                        if (! container) return;

                        // Encuentra todas las casillas marcadas
                        const checked = container.querySelectorAll('input[type="checkbox"]:checked');
                        
                        if (checked.length === 0) return;

                        checked.forEach(input => {
                            let parent = input.closest('.v-tree-item');
                            while (parent) {
                                parent.classList.add('active');
                                const icon = parent.querySelector('.icon-right-arrow');
                                if (icon) {
                                    icon.classList.remove('icon-right-arrow');
                                    icon.classList.add('icon-down-arrow');
                                }
                                // Pasar a la siguiente carpeta principal
                                parent = parent.parentElement.closest('.v-tree-item');
                            }
                        });
                    },

                    collapseAll() {
                        const container = document.querySelector('.tree-container');
                        if (!container) return;
                        container.querySelectorAll('.v-tree-item.active').forEach(item => {
                            item.classList.remove('active');
                            const icon = item.querySelector('.icon-down-arrow');
                            if (icon) { icon.classList.remove('icon-down-arrow'); icon.classList.add('icon-right-arrow'); }
                        });
                        container.scrollTop = 0;
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>

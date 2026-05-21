@props([
    'entity' => null,
    'entityControlName' => null,
])

<!-- WhatsApp Button -->
<div>
    <button
        class="flex h-[74px] w-[84px] flex-col items-center justify-center gap-1 rounded-lg border border-transparent bg-green-200 font-medium text-green-800 transition-all hover:border-green-400"
        @click="$refs.whatsappActionComponent.openModal()"
    >
        <span class="icon-chat text-2xl dark:!text-green-800"></span>

        WhatsApp
    </button>

    <!-- WhatsApp Action Vue Component -->
    <v-whatsapp-activity
        ref="whatsappActionComponent"
        :entity="{{ json_encode($entity) }}"
        entity-control-name="{{ $entityControlName }}"
    ></v-whatsapp-activity>
</div>

@pushOnce('scripts')
    <script type="text/x-template" id="v-whatsapp-activity-template">
        <Teleport to="body">
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, send)">
                    <x-admin::modal 
                        ref="whatsappActivityModal"
                        position="bottom-right"
                    >
                        <x-slot:header>
                            <h3 class="text-base font-semibold dark:text-white">
                                Enviar WhatsApp
                            </h3>
                        </x-slot>

                        <x-slot:content>
                            <!-- Id -->
                            <x-admin::form.control-group.control
                                type="hidden"
                                ::name="entityControlName"
                                ::value="entity.id"
                            />

                            <!-- Phone -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Teléfono
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="phone"
                                    rules="required"
                                    ::value="entity.person?.contact_numbers?.length ? entity.person.contact_numbers[0].value : (entity.person?.emails?.length ? entity.person.emails[0].value : '')"
                                    label="Teléfono"
                                    placeholder="Ej: 529613600613"
                                />

                                <x-admin::form.control-group.error control-name="phone" />
                            </x-admin::form.control-group>

                            <!-- Message -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label class="required">
                                    Mensaje
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="message"
                                    rules="required"
                                    label="Mensaje"
                                    placeholder="Escribe el mensaje..."
                                />

                                <x-admin::form.control-group.error control-name="message" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <x-slot:footer>
                            <x-admin::button
                                class="primary-button"
                                title="Enviar Mensaje"
                                ::loading="isSending"
                                ::disabled="isSending"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </Teleport>
    </script>

    <script type="module">
        app.component('v-whatsapp-activity', {
            template: '#v-whatsapp-activity-template',

            props: {
                entity: {
                    type: Object,
                    required: true,
                    default: () => {}
                },

                entityControlName: {
                    type: String,
                    required: true,
                    default: ''
                }
            },

            data: function () {
                return {
                    isSending: false,
                }
            },

            methods: {
                openModal() {
                    this.$refs.whatsappActivityModal.open();
                },

                send(params) {
                    this.isSending = true;

                    // Ajustar la ruta si entityControlName no es lead_id, pero usualmente lo es
                    this.$axios.post("{{ route('admin.leads.whatsapp.send', ['id' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', this.entity.id), {
                        ...params,
                        lead_id: this.entity.id
                    })
                        .then (response => {
                            this.isSending = false;

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.whatsappActivityModal.close();
                        })
                        .catch (error => {
                            this.isSending = false;

                            if (error.response?.status == 422) {
                                setErrors(error.response.data.errors);
                            } else {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Error al enviar WhatsApp' });

                                this.$refs.whatsappActivityModal.close();
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce

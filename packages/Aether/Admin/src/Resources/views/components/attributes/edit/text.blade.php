@if ($attribute->validation === 'numeric' || $attribute->code === 'quantity')
    <v-numeric-component
        :attribute="{{ json_encode($attribute) }}"
        :validations="'{{ $validations }}'"
        :value="{{ json_encode(old($attribute->code) ?? $value) }}"
    >
    </v-numeric-component>
@else
    <x-admin::form.control-group.control
        type="text"
        :id="$attribute->code"
        :name="$attribute->code"
        :value="old($attribute->code) ?? $value"
        :rules="$validations"
        :label="$attribute->name"
    />
@endif

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-numeric-component-template"
    >
        <v-field
            v-slot="{ field, errors, handleChange }"
            :name="attribute.code"
            :rules="validations"
            :label="attribute.name"
            :value="formattedValue"
        >
            <input
                type="text"
                :name="attribute.code"
                :id="attribute.code"
                :value="field.value"
                :class="[errors.length ? 'border !border-red-600 hover:border-red-600' : '']"
                class="w-full rounded border border-gray-300 px-2.5 py-2 text-sm font-normal text-gray-800 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                @input="handleInput($event, handleChange)"
                :placeholder="attribute.name"
            />
        </v-field>
    </script>

    <script type="module">
        app.component('v-numeric-component', {
            template: '#v-numeric-component-template',

            props: ['validations', 'attribute', 'value'],

            data() {
                return {
                    formattedValue: this.$admin.formatInteger(this.value)
                };
            },

            methods: {
                handleInput(event, handleChange) {
                    let input = event.target;
                    let cursorPosition = input.selectionStart;
                    let originalLength = input.value.length;
                    
                    let formatted = this.$admin.formatInteger(input.value);
                    
                    input.value = formatted;
                    handleChange(formatted);
                    
                    let newLength = formatted.length;
                    let cursorDiff = newLength - originalLength;
                    let newCursorPosition = cursorPosition + cursorDiff;
                    
                    this.$nextTick(() => {
                        input.setSelectionRange(newCursorPosition, newCursorPosition);
                    });
                }
            }
        });
    </script>
@endPushOnce
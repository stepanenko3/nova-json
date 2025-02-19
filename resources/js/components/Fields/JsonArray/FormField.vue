<template>
    <DefaultField
        :field="currentField"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div
                class="flex flex-col md:flex-row border border-gray-200 dark:border-gray-700 rounded-lg mb-4"
                v-for="(row, index) of rows"
            >
                <component
                    class="flex-grow"
                    :is="'form-' + row.component"
                    :field="row"
                    :resourceName="resourceName"
                    :resourceId="resourceId"
                    :editMode="editMode"
                    :mode="mode"
                    :errors="errors"
                    :ref="
                        (el) => {
                            fieldRefs[row.attribute] = el;
                        }
                    "
                    @change="() => update(row.attribute, index)"
                />

                <div class="py-5 px-6 md:px-8">
                    <Button
                        type="button"
                        state="danger"
                        leading-icon="trash"
                        @click="() => deleteRow(index)"
                    />
                </div>
            </div>

            <div
                class="flex items-center py-5 md:py-2 px-6 md:px-8 md:mt-0 cursor-pointer border-2 font-bold border-dashed border-gray-200 dark:border-gray-700 hover:border-primary-500 rounded-lg"
                @click="addRow"
            >
                <Icon name="plus-circle" class="mr-2" />

                Add Row
            </div>
        </template>
    </DefaultField>
</template>

<script>

import { Button, Icon } from 'laravel-nova-ui'
import { DependentFormField, HandlesValidationErrors } from "laravel-nova";
import { ref } from "vue";
import uniqueId from "lodash/uniqueId"

export default {
    mixins: [DependentFormField, HandlesValidationErrors],
    components: { Button, Icon },

    props: ["resourceName", "resourceId", "field"],

    data: () => ({
        value: [],
        rows: [],
    }),

    setup() {
        const fieldRefs = ref([]);
        return {
            fieldRefs,
        };
    },

    methods: {
        fill(formData) {
            this.value.map((value, index) => {
                const attribute = `${this.currentField.attribute}[${index}]`;

                if (this.fieldRefs[attribute]?.fill) {
                    this.fieldRefs[attribute].fill(formData);
                }
            });
        },

        addRow() {
            this.value.push("");

            this.emitChange();
        },

        deleteRow(index) {
            delete this.fieldRefs[this.value[index]];
            this.value.splice(index, 1);

            this.emitChange();
        },

        update(attribute, index) {
            this.value[index] = this.fieldRefs[attribute].value;

            this.emitChange();
        },

        emitChange() {
            if (this.field) {
                this.emitFieldValueChange(this.field.attribute, this.value);
            }
        },
    },

    computed: {
        rows() {
            if (!this.value) {
                this.value = [];
            }

            this.fieldRefs = [];

            return this.value.map((value, index) => ({
                ...this.currentField.field,
                value: value,
                originalAttribute: this.currentField.field.attribute,
                validationKey: `${this.currentField.validationKey}.${index}`,
                attribute: `${this.currentField.attribute}[${index}]`,
            }));
        },
    },
};
</script>

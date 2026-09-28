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
                :key="row.rowKey"
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
                            fieldRefs[row.rowKey] = el;
                        }
                    "
                    @change="() => update(row.rowKey, index)"
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
        const fieldRefs = ref({});

        // One stable id per row, kept in step with `value`. Rows are keyed by it so
        // Vue removes the deleted row's component rather than the last one, and child
        // refs are stored under it so they do not shift when row indexes change.
        const rowKeys = [];

        return {
            fieldRefs,
            rowKeys,
        };
    },

    methods: {
        fill(formData) {
            this.value.map((value, index) => {
                const fieldRef = this.fieldRefs[this.rowKeys[index]];

                if (fieldRef?.fill) {
                    fieldRef.fill(formData);
                }
            });
        },

        addRow() {
            this.rowKeys.push(uniqueId("row-"));
            this.value.push("");

            this.emitChange();
        },

        deleteRow(index) {
            const [rowKey] = this.rowKeys.splice(index, 1);
            delete this.fieldRefs[rowKey];
            this.value.splice(index, 1);

            this.emitChange();
        },

        update(rowKey, index) {
            this.value[index] = this.fieldRefs[rowKey].value;

            this.emitChange();
        },

        syncRowKeys() {
            while (this.rowKeys.length < this.value.length) {
                this.rowKeys.push(uniqueId("row-"));
            }

            this.rowKeys.length = this.value.length;
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

            this.syncRowKeys();

            return this.value.map((value, index) => ({
                ...this.currentField.field,
                rowKey: this.rowKeys[index],
                value: value,
                originalAttribute: this.currentField.field.attribute,
                validationKey: `${this.currentField.validationKey}.${index}`,
                attribute: `${this.currentField.attribute}[${index}]`,
            }));
        },
    },
};
</script>

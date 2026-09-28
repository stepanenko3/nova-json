<template>
    <DefaultField
        :field="currentField"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div
                class="relative border border-gray-200 dark:border-gray-700 rounded-lg mb-4"
                v-for="(row, index) in rows"
                :key="row.rowKey"
            >
                <component
                    v-for="rowField in row.fields"
                    :key="rowField.originalAttribute"
                    :is="'form-' + rowField.component"
                    :resourceName="resourceName"
                    :resourceId="resourceId"
                    :editMode="editMode"
                    :mode="mode"
                    :field="rowField"
                    :errors="errors"
                    :ref="
                        (el) => {
                            fieldRefs[refKey(row.rowKey, rowField.originalAttribute)] = el;
                        }
                    "
                    @change="
                        () =>
                            update(
                                row.rowKey,
                                index,
                                rowField.originalAttribute
                            )
                    "
                />

                <div class="mb-4 px-8">
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
import {
    DependentFormField,
    HandlesValidationErrors,
    mapProps,
} from "laravel-nova";
import { ref } from "vue";
import uniqueId from "lodash/uniqueId";

export default {
    mixins: [DependentFormField, HandlesValidationErrors],

    components: {
        Icon,
        Button,
    },

    props: {
        ...mapProps(["resourceName", "resourceId", "field"]),
    },

    data: () => ({
        value: [],
        rows: [],
    }),

    setup() {
        const fieldRefs = ref({});

        // One stable id per row, kept in step with `value`. Rows are keyed by it so
        // Vue removes the deleted row's components rather than the last row's, and
        // child refs are stored under it so they do not shift when row indexes change.
        const rowKeys = [];

        return {
            fieldRefs,
            rowKeys,
        };
    },

    methods: {
        fill(formData) {
            this.value.map((value, index) => {
                Object.keys(value).map((key) => {
                    const fieldRef = this.fieldRefs[this.refKey(this.rowKeys[index], key)];

                    if (fieldRef?.fill) {
                        fieldRef.fill(formData);
                    }
                });
            });
        },

        addRow() {
            this.rowKeys.push(uniqueId("row-"));
            this.value.push({});

            this.emitChange();
        },

        deleteRow(index) {
            const [rowKey] = this.rowKeys.splice(index, 1);

            this.currentField.fields.forEach((fieldPayload) => {
                delete this.fieldRefs[this.refKey(rowKey, fieldPayload.attribute)];
            });

            this.value.splice(index, 1);

            this.emitChange();
        },

        update(rowKey, index, originalAttribute) {
            this.value[index][originalAttribute] =
                this.fieldRefs[this.refKey(rowKey, originalAttribute)].value;

            this.emitChange();
        },

        refKey(rowKey, originalAttribute) {
            return `${rowKey}.${originalAttribute}`;
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

            return this.value.map((value, index) => {
                const fields = [];

                this.currentField.fields.map((fieldPayload) => {
                    const field = {
                        ...fieldPayload,
                        value: value[fieldPayload.attribute] || "",
                        originalAttribute: fieldPayload.attribute,
                        validationKey: `${this.currentField.validationKey}.${index}.${fieldPayload.attribute}`,
                        attribute: `${this.currentField.attribute}[${index}][${fieldPayload.attribute}]`,
                    };

                    if (field.dependsOn) {
                        Object.keys(field.dependsOn).forEach((key) => {
                            const prefix = `${this.currentField.attribute}.${index}.`;

                            if (key.startsWith(prefix)) {
                                return;
                            }

                            field.dependsOn[prefix + key] =
                                field.dependsOn[key];

                            delete field.dependsOn[key];
                        });
                    }

                    fields.push(field);
                });

                return {
                    rowKey: this.rowKeys[index],
                    fields,
                };
            });
        },
    },
};
</script>

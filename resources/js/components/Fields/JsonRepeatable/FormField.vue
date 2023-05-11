<template>
    <DefaultField
        :field="currentField"
        :errors="errors"
        :show-help-text="showHelpText"
        :full-width-content="fullWidthContent"
    >
        <template #field>
            <div
                class="border border-gray-200 dark:border-gray-700 rounded-lg mb-4"
                v-for="(row, index) in rows"
            >
                <component
                    v-for="rowField in row"
                    :is="'form-' + rowField.component"
                    :resourceName="resourceName"
                    :resourceId="resourceId"
                    :editMode="editMode"
                    :mode="mode"
                    :field="rowField"
                    :errors="errors"
                    :ref="
                        (el) => {
                            fieldRefs[rowField.attribute] = el;
                        }
                    "
                    @change="
                        () =>
                            update(
                                rowField.attribute,
                                index,
                                rowField.originalAttribute
                            )
                    "
                />

                <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 md:py-5">
                    <DangerButton @click="() => deleteRow(index)" type="button">
                        <Icon type="trash" />
                    </DangerButton>
                </div>
            </div>

            <div
                class="px-6 md:px-8 md:mt-0 cursor-pointer md:py-5 border-2 font-bold border-dashed border-gray-200 dark:border-gray-700 hover:border-primary-500 rounded-lg"
                @click="addRow"
            >
                <Icon type="plus-circle" class="mr-2" />

                Add Row
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {
    DependentFormField,
    HandlesValidationErrors,
    mapProps,
} from "laravel-nova";
import { ref } from "vue";

export default {
    mixins: [DependentFormField, HandlesValidationErrors],

    props: {
        ...mapProps(["resourceName", "resourceId", "field"]),
    },

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
                Object.keys(value).map((key) => {
                    const attribute = `${this.currentField.attribute}[${index}][${key}]`;

                    if (this.fieldRefs[attribute]?.fill) {
                        this.fieldRefs[attribute].fill(formData);
                    }
                });
            });
        },

        addRow() {
            this.value.push({});

            this.emitChange();
        },

        deleteRow(index) {
            delete this.fieldRefs[this.value[index]];
            this.value.splice(index, 1);

            this.emitChange();
        },

        update(attribute, index, originalAttribute) {
            console.log(attribute, index, originalAttribute, this.fieldRefs[attribute].value);
            this.value[index][originalAttribute] =
                this.fieldRefs[attribute].value;

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

                return fields;
            });
        },
    },
};
</script>

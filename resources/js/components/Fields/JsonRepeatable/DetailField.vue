<template>
    <PanelItem :index="index" :field="field">
        <template #value>

            <div
                class="flex flex-col px-6 py-2 border border-gray-200 dark:border-gray-700 rounded-lg mb-4"
                v-for="(row, index) of rows"
            >
                <component
                    v-for="field of row"
                    class="flex-grow"
                    :is="'detail-' + field.component"
                    :field="field"
                    :resource-name="resourceName"
                />
            </div>
        </template>
    </PanelItem>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    index: {
        type: Number,
        required: true,
    },
    resource: {
        type: Object,
        required: true,
    },
    resourceName: {
        type: String,
        required: true,
    },
    resourceId: {
        type: String,
        required: true,
    },
    field: {
        type: Object,
        required: true,
    },
});

const rows = computed(function () {
    return props.field.value.map((value, index) => {
        return props.field.fields.map((fieldPayload) => ({
            ...fieldPayload,
            value: value[fieldPayload.attribute],
        }));
    });
});
</script>

<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <div
                class="flex flex-col px-6 md:flex-row border border-gray-200 dark:border-gray-700 rounded-lg mb-4"
                v-for="(row, index) of rows"
            >
                <component
                    class="flex-grow"
                    :is="'detail-' + row.component"
                    :field="row"
                    :resource-name="resourceName"
                />
            </div>
        </template>
    </PanelItem>
</template>

<script setup>
import { computed } from 'vue';

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
    return props.field.value.map((value, index) => ({
        ...props.field.field,
        value: value,
    }));
})
</script>

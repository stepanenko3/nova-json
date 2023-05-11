<template>
    <div
        v-if="field?.value?.length > 0"
        class="flex-shrink-0 bg-primary-500 text-white text-xs leading-none font-semibold inline-flex items-center justify-center w-8 h-8 rounded-lg border-2 border-solid border-white dark:border-gray-800"
    >
        {{ field.value.length }}
    </div>
    <p v-else>—</p>
</template>

<script setup>
const props = defineProps({
    resourceName: {
        type: String,
        required: true,
    },
    field: {
        type: Object,
        required: true,
    },
});
</script>

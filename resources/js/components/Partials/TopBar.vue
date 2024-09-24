<template>
   <div class="bg-gray-50 dark:bg-gray-800 rounded-t-lg">
      <div
         class="leading-normal h-full flex items-center box-content border-b border-gray-200 dark:border-gray-700"
      >
         <p class="text-80 grow px-4 py-4 font-bold">
            <span class="mr-2">#{{ index + 1 }}</span>
            {{ title }}
         </p>

         <div class="flex">
            <button
               @click="emit('deleteRow', index)"
               type="button"
               class="border mr-2 text-left appearance-none cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 disabled:cursor-not-allowed inline-flex items-center justify-center shadow h-9 px-3 bg-red-500 border-red-500 hover:[&:not(:disabled)]:bg-red-400 hover:[&:not(:disabled)]:border-red-400 text-white dark:text-red-950"
            >
               <Icon type="trash" />
            </button>
         </div>
      </div>
   </div>
</template>

<script setup>
const emit = defineEmits(['deleteRow'])
const props = defineProps({
   index: {
      type: Number,
      required: true,
   },
   title: {
      type: String,
      required: true,
   },
})

</script>

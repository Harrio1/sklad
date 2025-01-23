<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const expanded = ref(null);

function toggleExpand(section) {
    expanded.value = expanded.value === section ? null : section;
}
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Главная страница
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Основной блок для Продукты -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-4 sm:p-6 cursor-pointer" @click="toggleExpand('products')">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Продукты</h3>
                        <p v-if="expanded === 'products'" class="text-gray-600 dark:text-gray-400">Полная информация о продуктах...</p>
                        <p v-else class="text-gray-600 dark:text-gray-400">Минимальная информация о продуктах...</p>
                    </div>

                    <!-- Блок для Поставщики -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-4 sm:p-6 cursor-pointer" @click="toggleExpand('suppliers')">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Поставщики</h3>
                        <p v-if="expanded === 'suppliers'" class="text-gray-600 dark:text-gray-400">Полная информация о поставщиках...</p>
                        <p v-else class="text-gray-600 dark:text-gray-400">Минимальная информация о поставщиках...</p>
                    </div>

                    <!-- Блок для Поставки -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-4 sm:p-6 cursor-pointer" @click="toggleExpand('deliveries')">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Поставки</h3>
                        <p v-if="expanded === 'deliveries'" class="text-gray-600 dark:text-gray-400">Полная информация о поставках...</p>
                        <p v-else class="text-gray-600 dark:text-gray-400">Минимальная информация о поставках...</p>
                    </div>

                    <!-- Блок для Номенклатура -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-4 sm:p-6 cursor-pointer" @click="toggleExpand('nomenclature')">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Номенклатура</h3>
                        <p v-if="expanded === 'nomenclature'" class="text-gray-600 dark:text-gray-400">Полная информация о номенклатуре...</p>
                        <p v-else class="text-gray-600 dark:text-gray-400">Минимальная информация о номенклатуре...</p>
                    </div>

                    <!-- Блок для Номенклатура продукта -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-4 sm:p-6 cursor-pointer" @click="toggleExpand('productNomenclature')">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Номенклатура продукта</h3>
                        <p v-if="expanded === 'productNomenclature'" class="text-gray-600 dark:text-gray-400">Полная информация о номенклатуре продукта...</p>
                        <p v-else class="text-gray-600 dark:text-gray-400">Минимальная информация о номенклатуре продукта...</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.cursor-pointer {
    transition: transform 0.3s;
}
@media (min-width: 640px) {
    .cursor-pointer:hover {
        transform: scale(1.02);
    }
}
</style>

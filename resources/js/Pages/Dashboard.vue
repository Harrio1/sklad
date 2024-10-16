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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Главная страница
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Основной блок для Продукты -->
                    <div class="col-span-2 bg-white shadow-xl rounded-lg p-6 cursor-pointer" @click="toggleExpand('products')">
                        <h3 class="text-lg font-bold">Продукты</h3>
                        <p v-if="expanded === 'products'">Полная информация о продуктах...</p>
                        <p v-else>Минимальная информация о продуктах...</p>
                    </div>

                    <!-- Блок для Поставщики -->
                    <div class="bg-white shadow-xl rounded-lg p-6 cursor-pointer" @click="toggleExpand('suppliers')">
                        <h3 class="text-lg font-bold">Поставщики</h3>
                        <p v-if="expanded === 'suppliers'">Полная информация о поставщиках...</p>
                        <p v-else>Минимальная информация о поставщиках...</p>
                    </div>

                    <!-- Блок для Поставки -->
                    <div class="bg-white shadow-xl rounded-lg p-6 cursor-pointer" @click="toggleExpand('deliveries')">
                        <h3 class="text-lg font-bold">Поставки</h3>
                        <p v-if="expanded === 'deliveries'">Полная информация о поставках...</p>
                        <p v-else>Минимальная информация о поставках...</p>
                    </div>

                    <!-- Блок для Номенклатура -->
                    <div class="bg-white shadow-xl rounded-lg p-6 cursor-pointer" @click="toggleExpand('nomenclature')">
                        <h3 class="text-lg font-bold">Номенклатура</h3>
                        <p v-if="expanded === 'nomenclature'">Полная информация о номенклатуре...</p>
                        <p v-else>Минимальная информация о номенклатуре...</p>
                    </div>

                    <!-- Блок для Номенклатура продукта -->
                    <div class="bg-white shadow-xl rounded-lg p-6 cursor-pointer" @click="toggleExpand('productNomenclature')">
                        <h3 class="text-lg font-bold">Номенклатура продукта</h3>
                        <p v-if="expanded === 'productNomenclature'">Полная информация о номенклатуре продукта...</p>
                        <p v-else>Минимальная информация о номенклатуре продукта...</p>
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
.cursor-pointer:hover {
    transform: scale(1.05);
}
</style>

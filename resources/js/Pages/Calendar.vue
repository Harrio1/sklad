<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const expanded = ref(null);

function toggleExpand(section) {
    expanded.value = expanded.value === section ? null : section;
}

const products = ref([
  { id: 1, name: 'Продукт 1' },
  { id: 2, name: 'Продукт 2' },
  // Добавьте другие продукты
]);

const days = ref(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']);

const tableData = ref([]);

const selectedProduct = ref(products.value[0].id);

function selectProduct(productId) {
  selectedProduct.value = productId;
  updateTable();
}

function updateTable() {
  // Логика для обновления tableData на основе выбранного продукта
}
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Календарь
            </h2>
        </template>

        <div class="flex py-6 sm:py-12">
            <div class="w-1/6 pr-4 pl-4">
                <div class="space-y-2">
                    <div v-for="product in products" :key="product.id" @click="selectProduct(product.id)"
                         :class="{'bg-gray-200 dark:bg-gray-600': selectedProduct === product.id}"
                         class="cursor-pointer p-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                        <h3 class="font-semibold">{{ product.name }}</h3>
                    </div>
                </div>
            </div>

            <div class="w-5/6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Номенклатура</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ед. изм.</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Расчетное количество</th>
                            <th v-for="day in days" :key="day" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ day }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Сумма</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="item in tableData" :key="item.id">
                            <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ item.name }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ item.unit }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ item.calculatedQuantity }}</td>
                            <td v-for="day in days" :key="day" class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ item.dailyData[day] }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ item.total }}</td>
                        </tr>
                    </tbody>
                </table>
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

@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const expanded = ref(null);
function toggleExpand(section) {
    expanded.value = expanded.value === section ? null : section;
}

const products = ref([]);
const nomenclatures = ref([]);
const days = ref(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']);
const tableData = ref([]);
const selectedProduct = ref(null);
const orders = ref([]);

// Загрузка продуктов
function loadProducts() {
    axios.get('/api/products')
        .then(response => {
            products.value = response.data.products;
            if (products.value.length > 0) {
                selectProduct(products.value[0].id);
            }
        })
        .catch(error => {
            console.error('Ошибка при загрузке продуктов:', error);
        });
}

// Загрузка номенклатур для выбранного продукта
function loadNomenclatures(productId) {
    axios.get(`/api/products/${productId}/nomenclatures`)
        .then(response => {
            nomenclatures.value = response.data.nomenclatures;
            updateTable();
        })
        .catch(error => {
            console.error('Ошибка при загрузке номенклатур:', error);
        });
}

// Загрузка заказов
function loadOrders() {
    axios.get('/api/orders')
        .then(response => {
            orders.value = response.data.orders;
            updateTable();
        })
        .catch(error => {
            console.error('Ошибка при загрузке заказов:', error);
        });
}

function selectProduct(productId) {
    selectedProduct.value = productId;
    loadNomenclatures(productId);
}

// Подсчёт заказов по дням недели (статус 2)
function getOrderCountByDay(productId) {
    const orderCountByDay = {
        'Пн': 0,
        'Вт': 0,
        'Ср': 0,
        'Чт': 0,
        'Пт': 0,
        'Сб': 0
    };

    orders.value.forEach(order => {
        if (order.status === 2) {
            const orderDate = new Date(order.created_at);
            const dayOfWeek = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'][orderDate.getDay()];
            const productsInOrder = JSON.parse(order.products);
            productsInOrder.forEach(product => {
                if (product.id === productId) {
                    orderCountByDay[dayOfWeek] += 1;
                }
            });
        }
    });
    return orderCountByDay;
}

function updateTable() {
    tableData.value = nomenclatures.value.map(nomenclature => {
        const orderCountByDay = getOrderCountByDay(nomenclature.nomenclature.id);
        return {
            id: nomenclature.nomenclature.id,
            name: nomenclature.nomenclature.name,
            unit: nomenclature.nomenclature.unit_of_measurement,
            calculatedQuantity: nomenclature.quantity,
            dailyData: orderCountByDay,
            total: nomenclature.price * nomenclature.quantity,
        };
    });
}

onMounted(() => {
    loadProducts();
    loadOrders();
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Товарооборот
            </h2>
        </template>

        <div class="flex py-4 sm:py-8">
            <!-- Список продуктов -->
            <div class="w-1/6 pr-4 pl-4">
                <div class="space-y-2">
                    <div v-for="product in products" :key="product.id" 
                         @click="selectProduct(product.id)"
                         :class="{'bg-blue-100 dark:bg-blue-700': selectedProduct === product.id}"
                         class="cursor-pointer p-2 border border-blue-300 dark:border-blue-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                        <h3 class="font-medium text-sm truncate" :title="product.name">{{ product.name }}</h3>
                    </div>
                </div>
            </div>
            
            <!-- Полосатая таблица -->
            <div class="w-5/6 overflow-x-auto">
                <table class="min-w-full table-auto divide-y divide-gray-300 dark:divide-gray-600 text-xs">
                    <thead class="bg-gray-200 dark:bg-gray-800">
                        <tr>
                            <th class="px-2 py-1 text-center font-medium text-gray-700 dark:text-gray-300 uppercase w-40">Номенклатура</th>
                            <th class="px-2 py-1 text-center font-medium text-gray-700 dark:text-gray-300 uppercase w-16">Ед. изм.</th>
                            <th class="px-2 py-1 text-center font-medium text-gray-700 dark:text-gray-300 uppercase w-16">Кол-во</th>
                            <th v-for="day in days" :key="day" class="px-2 py-1 text-center font-medium text-gray-700 dark:text-gray-300 uppercase w-12">{{ day }}</th>
                            <th class="px-2 py-1 text-center font-medium text-gray-700 dark:text-gray-300 uppercase w-20">Сумма</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="item in tableData" :key="item.id" class="hover:bg-gray-200 dark:hover:bg-gray-600 odd:bg-white even:bg-gray-50">
                            <td class="px-2 py-1 text-center text-gray-800 dark:text-gray-100 truncate" :title="item.name">{{ item.name }}</td>
                            <td class="px-2 py-1 text-center text-gray-800 dark:text-gray-100">{{ item.unit }}</td>
                            <td class="px-2 py-1 text-center text-gray-800 dark:text-gray-100">{{ item.calculatedQuantity }}</td>
                            <td v-for="day in days" :key="day" class="px-2 py-1 text-center text-gray-800 dark:text-gray-100">{{ item.dailyData[day] }}</td>
                            <td class="px-2 py-1 text-center text-gray-800 dark:text-gray-100">{{ item.total }}</td>
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
.cursor-pointer:hover {
    transform: scale(1.05);
}
@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

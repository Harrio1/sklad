<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Данные статистики
const stats = ref({
    products: { count: 0, recentItems: [] },
    suppliers: { count: 0, recentItems: [] },
    supplies: { count: 0, recentItems: [] },
    nomenclature: { count: 0, recentItems: [] },
    productNomenclature: { count: 0, recentItems: [] },
    orders: { count: 0, recentItems: [] }
});

// Состояние развернутого блока
const expandedBlock = ref(null);

// Информация о блоках
const blockInfo = {
    products: {
        title: 'Продукты',
        description: 'Товары, которые производятся из различных компонентов. Каждый продукт может состоять из нескольких номенклатурных позиций.',
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        color: 'blue',
        link: '/products'
    },
    suppliers: {
        title: 'Поставщики',
        description: 'Компании и частные лица, которые поставляют компоненты для производства. Включает контактную информацию и историю поставок.',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        color: 'green',
        link: '/suppliers'
    },
    supplies: {
        title: 'Поставки',
        description: 'Партии номенклатуры, полученные от поставщиков. Включает информацию о количестве, дате поставки и поставщике.',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        color: 'purple',
        link: '/supplies'
    },
    nomenclature: {
        title: 'Номенклатура',
        description: 'Базовые компоненты, используемые в производстве продуктов. Включает единицы измерения и спецификации.',
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'amber',
        link: '/nomenclature'
    },
    orders: {
        title: 'Заказы',
        description: 'Заказы на продукцию, полученные от клиентов. Включает информацию о продуктах, количестве и статусе заказа.',
        icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        color: 'red',
        link: '/orders'
    }
};

// Индикатор загрузки и ошибки
const isLoading = ref(true);
const errors = ref([]);

// Загрузка данных при монтировании компонента
onMounted(() => {
    Promise.all([
        getStats('products'),
        getStats('suppliers'),
        getStats('supplies'),
        getStats('nomenclature'),
        getStats('product-nomenclature'),
        getStats('orders')
    ]).finally(() => {
        isLoading.value = false;
    });
});

// Функция для получения статистики с сервера
function getStats(type) {
    return axios.get(`/api/dashboard/${type}`)
        .then(response => {
            const key = type === 'product-nomenclature' ? 'productNomenclature' : type;
            if (response.data && response.data.success) {
                stats.value[key] = response.data.data;
            }
        })
        .catch(error => {
            console.error(`Ошибка при получении статистики ${type}:`, error);
            errors.value.push(`Не удалось загрузить данные: ${type}`);
            const key = type === 'product-nomenclature' ? 'productNomenclature' : type;
            stats.value[key] = { count: 0, recentItems: [] };
        });
}

// Функция для переключения состояния блока
function toggleBlock(blockKey) {
    if (expandedBlock.value === blockKey) {
        expandedBlock.value = null;
    } else {
        expandedBlock.value = blockKey;
    }
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
                <!-- Индикатор загрузки -->
                <div v-if="isLoading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-500 dark:border-blue-400"></div>
                </div>

                <!-- Сообщения об ошибках -->
                <div v-else-if="errors.length > 0" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-lg p-4 mb-6">
                    <h3 class="text-red-800 dark:text-red-400 font-medium mb-2">Возникли ошибки при загрузке данных:</h3>
                    <ul class="list-disc pl-5 text-red-700 dark:text-red-300 text-sm">
                        <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
                    </ul>
                    </div>

                <!-- Основное содержимое -->
                <div v-else class="space-y-6">
                    <!-- Верхние метрики -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Метрика Продукты -->
                        <div 
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transform hover:scale-102 transition-all duration-200 cursor-pointer"
                            :class="{'ring-2 ring-blue-500 dark:ring-blue-400': expandedBlock === 'products'}"
                            @click="toggleBlock('products')"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">Продукты</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.products.count }}</p>
                                </div>
                                <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="expandedBlock === 'products'" class="mt-4 animate-fadeIn">
                                <div v-if="stats.products.recentItems.length > 0" class="mb-3">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Последние добавленные:</p>
                                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2 pl-2">
                                        <li v-for="item in stats.products.recentItems.slice(0, 3)" :key="item.id" class="flex items-center">
                                            <span class="w-2 h-2 bg-blue-500 dark:bg-blue-400 rounded-full mr-2"></span>
                                            <span class="font-medium">{{ item.name }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <a href="/products" class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline mt-1">
                                    Перейти к продуктам
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                            <div v-else class="mt-4">
                                <a href="/products" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Перейти к продуктам →</a>
                            </div>
                        </div>

                        <!-- Метрика Поставщики -->
                        <div 
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transform hover:scale-102 transition-all duration-200 cursor-pointer"
                            :class="{'ring-2 ring-green-500 dark:ring-green-400': expandedBlock === 'suppliers'}"
                            @click="toggleBlock('suppliers')"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">Поставщики</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.suppliers.count }}</p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="expandedBlock === 'suppliers'" class="mt-4 animate-fadeIn">
                                <div v-if="stats.suppliers.recentItems.length > 0" class="mb-3">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Последние добавленные:</p>
                                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2 pl-2">
                                        <li v-for="item in stats.suppliers.recentItems.slice(0, 3)" :key="item.id" class="flex items-center">
                                            <span class="w-2 h-2 bg-green-500 dark:bg-green-400 rounded-full mr-2"></span>
                                            <span class="font-medium">{{ item.name }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <a href="/suppliers" class="inline-flex items-center text-sm font-medium text-green-600 dark:text-green-400 hover:underline mt-1">
                                    Перейти к поставщикам
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                            <div v-else class="mt-4">
                                <a href="/suppliers" class="text-sm font-medium text-green-600 dark:text-green-400 hover:underline">Перейти к поставщикам →</a>
                            </div>
                        </div>

                        <!-- Метрика Поставки -->
                        <div 
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transform hover:scale-102 transition-all duration-200 cursor-pointer"
                            :class="{'ring-2 ring-purple-500 dark:ring-purple-400': expandedBlock === 'supplies'}"
                            @click="toggleBlock('supplies')"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">Поставки</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.supplies.count }}</p>
                                </div>
                                <div class="bg-purple-100 dark:bg-purple-900/30 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="expandedBlock === 'supplies'" class="mt-4 animate-fadeIn">
                                <div v-if="stats.supplies.recentItems.length > 0" class="mb-3">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Последние поставки:</p>
                                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2 pl-2">
                                        <li v-for="item in stats.supplies.recentItems.slice(0, 3)" :key="item.id" class="flex items-center">
                                            <span class="w-2 h-2 bg-purple-500 dark:bg-purple-400 rounded-full mr-2"></span>
                                            <span class="font-medium">{{ item.nomenclature }}</span>
                                            <span class="ml-1">({{ item.quantity }})</span>
                                        </li>
                                    </ul>
                                </div>
                                <a href="/supplies" class="inline-flex items-center text-sm font-medium text-purple-600 dark:text-purple-400 hover:underline mt-1">
                                    Перейти к поставкам
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                            <div v-else class="mt-4">
                                <a href="/supplies" class="text-sm font-medium text-purple-600 dark:text-purple-400 hover:underline">Перейти к поставкам →</a>
                            </div>
                    </div>

                        <!-- Метрика Номенклатура -->
                        <div 
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transform hover:scale-102 transition-all duration-200 cursor-pointer"
                            :class="{'ring-2 ring-amber-500 dark:ring-amber-400': expandedBlock === 'nomenclature'}"
                            @click="toggleBlock('nomenclature')"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">Номенклатура</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.nomenclature.count }}</p>
                                </div>
                                <div class="bg-amber-100 dark:bg-amber-900/30 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="expandedBlock === 'nomenclature'" class="mt-4 animate-fadeIn">
                                <div v-if="stats.nomenclature.recentItems.length > 0" class="mb-3">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Последние добавленные:</p>
                                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2 pl-2">
                                        <li v-for="item in stats.nomenclature.recentItems.slice(0, 3)" :key="item.id" class="flex items-center">
                                            <span class="w-2 h-2 bg-amber-500 dark:bg-amber-400 rounded-full mr-2"></span>
                                            <span class="font-medium">{{ item.name }}</span>
                                            <span class="ml-1 text-gray-500 dark:text-gray-400">({{ item.unit || 'Нет ед. изм.' }})</span>
                                        </li>
                                    </ul>
                                </div>
                                <a href="/nomenclature" class="inline-flex items-center text-sm font-medium text-amber-600 dark:text-amber-400 hover:underline mt-1">
                                    Перейти к номенклатуре
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                            <div v-else class="mt-4">
                                <a href="/nomenclature" class="text-sm font-medium text-amber-600 dark:text-amber-400 hover:underline">Перейти к номенклатуре →</a>
                            </div>
                    </div>

                        <!-- Метрика Заказы -->
                        <div 
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transform hover:scale-102 transition-all duration-200 cursor-pointer"
                            :class="{'ring-2 ring-red-500 dark:ring-red-400': expandedBlock === 'orders'}"
                            @click="toggleBlock('orders')"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-base font-medium text-gray-700 dark:text-gray-300">Заказы</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ stats.orders.count }}</p>
                                </div>
                                <div class="bg-red-100 dark:bg-red-900/30 p-3 rounded-full">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="expandedBlock === 'orders'" class="mt-4 animate-fadeIn">
                                <div v-if="stats.orders.recentItems.length > 0" class="mb-3">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Последние заказы:</p>
                                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2 pl-2">
                                        <li v-for="item in stats.orders.recentItems.slice(0, 3)" :key="item.id" class="flex items-center">
                                            <span class="w-2 h-2 bg-red-500 dark:bg-red-400 rounded-full mr-2"></span>
                                            <span class="font-medium">{{ item.name }}</span>
                                            <span class="ml-1">({{ item.count || 0 }} шт.)</span>
                                        </li>
                                    </ul>
                                </div>
                                <a href="/orders" class="inline-flex items-center text-sm font-medium text-red-600 dark:text-red-400 hover:underline mt-1">
                                    Перейти к заказам
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                            <div v-else class="mt-4">
                                <a href="/orders" class="text-sm font-medium text-red-600 dark:text-red-400 hover:underline">Перейти к заказам →</a>
                            </div>
                        </div>
                    </div>

                    <!-- Последние действия -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Последние действия</h3>
                        <div class="space-y-4">
                            <div v-for="(item, index) in stats.supplies.recentItems.slice(0, 5)" :key="index" 
                                 class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ item.nomenclature }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Количество: {{ item.quantity }}
                                    </p>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ item.date }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.transition-transform {
    transition-property: transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

.hover\:scale-102:hover {
        transform: scale(1.02);
    }

@keyframes fadeIn {
    0% { opacity: 0; max-height: 0; }
    100% { opacity: 1; max-height: 500px; }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>

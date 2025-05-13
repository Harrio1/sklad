<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Для отслеживания открытой секции (одновременно открыта может быть только одна)
const expanded = ref(null);
const stats = ref({
    products: { count: 0, recentItems: [] },
    suppliers: { count: 0, recentItems: [] },
    supplies: { count: 0, recentItems: [] },
    nomenclature: { count: 0, recentItems: [] },
    productNomenclature: { count: 0, recentItems: [] }
});
const isLoading = ref(true);
const errors = ref([]);

// Переключение открытого состояния секции
function toggleExpand(section) {
    expanded.value = expanded.value === section ? null : section;
}

onMounted(() => {
    Promise.all([
        getStats('products'),
        getStats('suppliers'),
        getStats('supplies'),
        getStats('nomenclature'),
        getStats('product-nomenclature')
    ]).finally(() => {
        isLoading.value = false;
    });
});

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
                
                <!-- Основное содержимое с блоками данных -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Блок Продукты -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden hover:shadow-2xl transition-shadow">
                        <!-- Заголовок блока - при клике открывает/закрывает детали -->
                        <div class="p-4 sm:p-6 cursor-pointer" @click="toggleExpand('products')">
                            <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Продукты</h3>
                                <div class="bg-blue-500 text-white text-sm font-semibold rounded-full px-3 py-1">
                                    {{ stats.products.count }}
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Всего продуктов в системе</p>
                            </div>
                        </div>
                        
                        <!-- Развернутые детали блока - показываются только если блок раскрыт -->
                        <div v-if="expanded === 'products'" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Последние продукты:</h4>
                            <ul v-if="stats.products.recentItems.length > 0" class="space-y-2">
                                <li v-for="item in stats.products.recentItems" :key="item.id" class="text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-800 dark:text-gray-200">{{ item.name }}</span>
                                        <span v-if="item.count" class="text-gray-600 dark:text-gray-400">{{ item.count }}</span>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="text-gray-500 dark:text-gray-400 text-sm italic text-center py-2">
                                Нет данных
                            </div>
                            <div class="mt-3 text-right">
                                <a href="/products" class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                    Перейти к продуктам
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Блок Поставщики -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden hover:shadow-2xl transition-shadow">
                        <!-- Заголовок блока -->
                        <div class="p-4 sm:p-6 cursor-pointer" @click="toggleExpand('suppliers')">
                            <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Поставщики</h3>
                                <div class="bg-green-500 text-white text-sm font-semibold rounded-full px-3 py-1">
                                    {{ stats.suppliers.count }}
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Активных поставщиков</p>
                            </div>
                        </div>
                        
                        <!-- Развернутые детали блока -->
                        <div v-if="expanded === 'suppliers'" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Основные поставщики:</h4>
                            <ul v-if="stats.suppliers.recentItems.length > 0" class="space-y-2">
                                <li v-for="item in stats.suppliers.recentItems" :key="item.id" class="text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-800 dark:text-gray-200">{{ item.name }}</span>
                                        <span v-if="item.phone" class="text-gray-600 dark:text-gray-400">{{ item.phone }}</span>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="text-gray-500 dark:text-gray-400 text-sm italic text-center py-2">
                                Нет данных
                            </div>
                            <div class="mt-3 text-right">
                                <a href="/suppliers" class="inline-flex items-center text-sm font-medium text-green-600 dark:text-green-400 hover:underline">
                                    Перейти к поставщикам
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Блок Поставки -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden hover:shadow-2xl transition-shadow">
                        <!-- Заголовок блока -->
                        <div class="p-4 sm:p-6 cursor-pointer" @click="toggleExpand('supplies')">
                            <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Поставки</h3>
                                <div class="bg-purple-500 text-white text-sm font-semibold rounded-full px-3 py-1">
                                    {{ stats.supplies.count }}
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Всего поставок компонентов</p>
                            </div>
                        </div>
                        
                        <!-- Развернутые детали блока -->
                        <div v-if="expanded === 'supplies'" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Последние поставки:</h4>
                            <ul v-if="stats.supplies.recentItems.length > 0" class="space-y-2">
                                <li v-for="item in stats.supplies.recentItems" :key="item.id" class="text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-800 dark:text-gray-200">{{ item.nomenclature }}</span>
                                        <div class="text-right">
                                            <span v-if="item.quantity" class="text-gray-600 dark:text-gray-400">{{ item.quantity }}</span>
                                            <span v-if="item.date" class="text-gray-500 dark:text-gray-500 text-xs ml-2">{{ item.date }}</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="text-gray-500 dark:text-gray-400 text-sm italic text-center py-2">
                                Нет данных
                            </div>
                            <div class="mt-3 text-right">
                                <a href="/supplies" class="inline-flex items-center text-sm font-medium text-purple-600 dark:text-purple-400 hover:underline">
                                    Перейти к поставкам
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Блок Номенклатура -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden hover:shadow-2xl transition-shadow">
                        <!-- Заголовок блока -->
                        <div class="p-4 sm:p-6 cursor-pointer" @click="toggleExpand('nomenclature')">
                            <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Номенклатура</h3>
                                <div class="bg-amber-500 text-white text-sm font-semibold rounded-full px-3 py-1">
                                    {{ stats.nomenclature.count }}
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Компонентов в номенклатуре</p>
                            </div>
                        </div>
                        
                        <!-- Развернутые детали блока -->
                        <div v-if="expanded === 'nomenclature'" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Популярные компоненты:</h4>
                            <ul v-if="stats.nomenclature.recentItems.length > 0" class="space-y-2">
                                <li v-for="item in stats.nomenclature.recentItems" :key="item.id" class="text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-800 dark:text-gray-200">{{ item.name }}</span>
                                        <span v-if="item.unit" class="text-gray-600 dark:text-gray-400">{{ item.unit }}</span>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="text-gray-500 dark:text-gray-400 text-sm italic text-center py-2">
                                Нет данных
                            </div>
                            <div class="mt-3 text-right">
                                <a href="/nomenclature" class="inline-flex items-center text-sm font-medium text-amber-600 dark:text-amber-400 hover:underline">
                                    Перейти к номенклатуре
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Блок Номенклатура продукта -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden hover:shadow-2xl transition-shadow">
                        <!-- Заголовок блока -->
                        <div class="p-4 sm:p-6 cursor-pointer" @click="toggleExpand('productNomenclature')">
                            <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Номенклатура продукта</h3>
                                <div class="bg-indigo-500 text-white text-sm font-semibold rounded-full px-3 py-1">
                                    {{ stats.productNomenclature.count }}
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Связей между продуктами и компонентами</p>
                            </div>
                        </div>
                        
                        <!-- Развернутые детали блока -->
                        <div v-if="expanded === 'productNomenclature'" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Компоненты продуктов:</h4>
                            <ul v-if="stats.productNomenclature.recentItems.length > 0" class="space-y-2">
                                <li v-for="item in stats.productNomenclature.recentItems" :key="item.id" class="text-sm">
                                    <div>
                                        <span class="text-gray-800 dark:text-gray-200">{{ item.product }}</span>
                                        <div class="flex justify-between mt-0.5 ml-4">
                                            <span class="text-gray-600 dark:text-gray-400">{{ item.nomenclature }}</span>
                                            <span v-if="item.quantity" class="text-gray-500 dark:text-gray-500 text-xs">{{ item.quantity }}</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="text-gray-500 dark:text-gray-400 text-sm italic text-center py-2">
                                Нет данных
                            </div>
                            <div class="mt-3 text-right">
                                <a href="/product-nomenclature" class="inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Перейти к номенклатуре продукта
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Блок статистики и аналитики -->
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 shadow-xl rounded-lg overflow-hidden md:col-span-2 lg:col-span-3">
                        <div class="p-6 text-white">
                            <h3 class="text-xl font-bold mb-4">Сводная статистика</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                <div class="bg-white/20 rounded-lg p-4">
                                    <p class="text-sm opacity-80">Продукты</p>
                                    <p class="text-2xl font-bold">{{ stats.products.count }}</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-4">
                                    <p class="text-sm opacity-80">Поставщики</p>
                                    <p class="text-2xl font-bold">{{ stats.suppliers.count }}</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-4">
                                    <p class="text-sm opacity-80">Поставки</p>
                                    <p class="text-2xl font-bold">{{ stats.supplies.count }}</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-4">
                                    <p class="text-sm opacity-80">Номенклатура</p>
                                    <p class="text-2xl font-bold">{{ stats.nomenclature.count }}</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-4">
                                    <p class="text-sm opacity-80">Ном. продукта</p>
                                    <p class="text-2xl font-bold">{{ stats.productNomenclature.count }}</p>
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
.transition-shadow {
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.hover\:shadow-2xl:hover {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transform: translateY(-3px);
}

@media (prefers-color-scheme: dark) {
    .hover\:shadow-2xl:hover {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
}
</style>

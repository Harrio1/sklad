<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted, computed, watch } from 'vue';
import NotificationToast from '@/Components/NotificationToast.vue';
import useNotifications from '@/Composables/useNotifications';
import axios from 'axios';

const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const form = reactive({
    product_id: null,
    nomenclatures: [{ id: null, quantity: 0, price: 0, unit: '' }]
});

const { notifications, showNotification, closeNotification } = useNotifications();

const products = ref([]);
const productsNomenclatures = ref([]);
const isLoading = ref(true);
const availableNomenclatures = ref([]);
const isLoadingNomenclatures = ref(false);

function openModal(message, color) {
    showNotification(message, color, color === 'mgreen' ? 'Успех' : 'Ошибка');
}

function getProductsNomenclatures() {
    axios.get(route('get-products-nomenclatures'))
        .then((response) => {
            products.value = response.data.products || [];
            productsNomenclatures.value = response.data.products.filter(product => product.nomenclatures && product.nomenclatures.length > 0) || [];
            isLoading.value = false;
        }).catch((error) => {
            console.error('Ошибка при получении данных:', error);
            showNotification('Ошибка при получении данных', 'mred', 'Ошибка');
            products.value = [];
            productsNomenclatures.value = [];
            isLoading.value = false;
        });
}

onMounted(() => {
    getProductsNomenclatures();
});

function addNomenclatureLine() {
    if (canAddNomenclatureLine.value) {
        form.nomenclatures.push({ id: null, quantity: 0, price: 0, unit: '' });
    }
}

function removeNomenclatureLine(index) {
    form.nomenclatures.splice(index, 1);
}

function deleteProductNomenclature(productId, nomenclatureId) {
    if (confirm('Вы действительно хотите удалить эту связь?')) {
        axios.post('/delete-products-nomenclatures', {
            product_id: productId,
            nomenclature_id: nomenclatureId,
        }).then((response) => {
            openModal(response.data.status, 'mgreen');
            getProductsNomenclatures();
        }).catch(error => {
            console.error('Ошибка при удалении связи:', error);
            openModal('Ошибка при удалении связи', 'mred');
        });
    }
}

function submitForm() {
    if (!form.product_id || form.nomenclatures.some(n => !n.id || n.quantity <= 0)) {
        openModal('Заполните все поля формы корректно', 'mred');
        return;
    }

    axios.post('/add-products-nomenclatures', form)
        .then((response) => {
            openModal(response.data.status, 'mgreen');
            getProductsNomenclatures();
            form.product_id = null;
            form.nomenclatures = [];
            loadAvailableNomenclatures();
        }).catch(error => {
            console.error('Ошибка при добавлении связи:', error);
            openModal('Ошибка при добавлении связи', 'mred');
        });
}

function loadAvailableNomenclatures() {
    isLoadingNomenclatures.value = true;
    axios.get('/get-available-nomenclatures')
        .then((response) => {
            availableNomenclatures.value = response.data.nomenclatures;
            isLoadingNomenclatures.value = false;
        }).catch((error) => {
            console.error('Ошибка при загрузке доступных номенклатур:', error);
            isLoadingNomenclatures.value = false;
        });
}

watch(() => form.product_id, (newValue) => {
    if (newValue) {
        loadAvailableNomenclatures();
        form.nomenclatures = [{ id: null, quantity: 0, price: 0, unit: '' }];
    } else {
        form.nomenclatures = [{ id: null, quantity: 0, price: 0, unit: '' }];
    }
});

const canAddNomenclatureLine = computed(() => {
    return availableNomenclatures.value.length > form.nomenclatures.length;
});

function isNomenclatureAvailable(nomenclatureId, currentIndex) {
    return !form.nomenclatures.some((n, index) => index !== currentIndex && n.id === nomenclatureId);
}

function calculateTotalPrice(nomenclature) {
    return (nomenclature.pivot.price * nomenclature.pivot.quantity).toFixed(2);
}

function updateNomenclatureDetails(nomenclature) {
    const selectedNomenclature = availableNomenclatures.value.find(n => n.id === nomenclature.id);
    if (selectedNomenclature) {
        nomenclature.unit = selectedNomenclature.unit;
        nomenclature.price = selectedNomenclature.price_per_unit * nomenclature.quantity;
        
        if (nomenclature.unit === 'шт.') {
            nomenclature.quantity = Math.round(nomenclature.quantity);
        }
    }
}

function getQuantityStep(unit) {
    return unit === 'шт.' ? 1 : 0.01;
}
</script>

<template>
    <AppLayout title="Продукты номенклатуры">
        <NotificationToast 
            :notifications="notifications"
            @close="closeNotification"
        />
        
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Продукты номенклатуры
            </h2>
        </template>

        <div class="py-6 sm:py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mb-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Добавление номенклатуры к продукту</h3>
                    <form @submit.prevent="submitForm">
                        <input type="hidden" name="_token" :value="csrf">

                        <div class="mb-4">
                            <label for="product" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Продукт</label>
                            <select id="product" v-model="form.product_id" required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                <option value="">Выберите продукт</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mb-4">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Номенклатура</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Количество</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Цена (₽)</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Действие</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(nomenclature, index) in form.nomenclatures" :key="index">
                                    <td class="px-3 py-2">
                                        <select v-model="nomenclature.id"
                                                @change="updateNomenclatureDetails(nomenclature)"
                                                required
                                                class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                            <option :value="null">Выберите номенклатуру</option>
                                            <option v-for="n in availableNomenclatures" 
                                                    :key="n.id" 
                                                    :value="n.id"
                                                    :disabled="form.nomenclatures.some(fn => fn.id === n.id && fn !== nomenclature)">
                                                {{ n.name }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" 
                                               v-model.number="nomenclature.quantity"
                                               @input="updateNomenclatureDetails(nomenclature)"
                                               required 
                                               :step="getQuantityStep(nomenclature.unit)"
                                               class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" 
                                               v-model.number="nomenclature.price"
                                               readonly
                                               class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <button type="button" @click="removeNomenclatureLine(index)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-500">
                                            Удалить
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <button type="button" @click="addNomenclatureLine" 
                                :disabled="!canAddNomenclatureLine"
                                class="mb-4 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-500" 
                                :class="{ 'opacity-50 cursor-not-allowed': !canAddNomenclatureLine }">
                            + Добавить номенклатуру
                        </button>

                        <button type="submit"
                                class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить связь
                        </button>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mt-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Список связей продуктов и номенклатуры</h3>
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Продукт</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Номенклатура</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Количество</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Цена (₽)</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Действие</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <template v-for="product in productsNomenclatures" :key="product.id">
                                    <tr v-for="nomenclature in product.nomenclatures" :key="nomenclature.id">
                                        <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                            <div class="text-sm font-medium">{{ product.name }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                            <div class="text-sm">{{ nomenclature.name }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                            <div class="text-sm">{{ nomenclature.pivot.quantity }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                            <div class="text-sm">{{ calculateTotalPrice(nomenclature) }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                            <a @click="deleteProductNomenclature(product.id, nomenclature.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-500 cursor-pointer">Удалить</a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="sm:hidden">
                        <div v-for="product in productsNomenclatures" :key="product.id" class="mb-6">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-2">{{ product.name }}</h4>
                            <div v-for="nomenclature in product.nomenclatures" :key="nomenclature.id" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-4 p-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Номенклатура: <span class="text-gray-900 dark:text-gray-200">{{ nomenclature.name }}</span></p>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Количество: <span class="text-gray-900 dark:text-gray-200">{{ nomenclature.pivot.quantity }}</span></p>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Цена: <span class="text-gray-900 dark:text-gray-200">{{ calculateTotalPrice(nomenclature) }} ₽</span></p>
                                <div class="mt-2">
                                    <a @click="deleteProductNomenclature(product.id, nomenclature.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-500 cursor-pointer">Удалить</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-500 dark:border-blue-400"></div>
        </div>
    </AppLayout>
</template>

<style scoped>
.modalMessage {
    position: fixed;
    top: 10%;
    right: -100%;
    border: 1px solid #ccc;
    box-shadow: 0px 0px 20px #444;
    background-color: rgba(0, 95, 13, 0.9);
    padding: 20px 40px;
    color: #fff;
    transition: right 0.5s ease;
    z-index: 1000;
}

.modalMessage.show {
    right: 0%;
}

.mgreen {
    background-color: rgba(0, 95, 13, 0.9);
}

.mred {
    background-color: rgba(104, 2, 10, 0.9);
}
</style>

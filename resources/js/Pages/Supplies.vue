<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted, computed, watch } from 'vue';
import NotificationToast from '@/Components/NotificationToast.vue';
import useNotifications from '@/Composables/useNotifications';
import axios from 'axios';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const form = reactive({
    supplier_id: null,
    nomenclatures: [{ id: null, quantity: 0, price: 0, unit: '' }],
    step: 1,
    compatibleUnit: ''
});

let isEdit = ref(false);
let isEditId = ref(0);

const { notifications, showNotification, closeNotification } = useNotifications();

let nomenclatures = ref([]);
let supplies = ref([]);
let units = ref([]);
const isLoading = ref(true);

const showFilter = ref(false);
const selectedNomenclatures = ref([]);
const itemsPerPage = 20;
const currentPage = ref(1);
const hasMoreItems = ref(false);
const allSupplies = ref([]);

const filteredSupplies = computed(() => {
    let result = allSupplies.value;
    
    if (selectedNomenclatures.value.length > 0) {
        result = result.filter(supply => 
            selectedNomenclatures.value.includes(supply.nomenclature_id)
        );
    }
    
    supplies.value = result.slice(0, currentPage.value * itemsPerPage);
    
    hasMoreItems.value = supplies.value.length < result.length;
    
    return supplies.value;
});

const totalPrice = computed(() => {
    return calculateTotalPrice(form.quantity, form.nomenclatureId);
});

onMounted(() => {
    Promise.all([getNomenclatures(), getSupplies(), getUnits()]).then(() => {
        isLoading.value = false;
    });
});

watch(selectedNomenclatures, () => {
    currentPage.value = 1;
});

function getNomenclatures() {
    return axios.get('/get-nomenclatures').then((response) => {
        nomenclatures.value = response.data.nomenclatures;
    }).catch(error => {
        console.error('Ошибка при получении номенклатур:', error);
        showNotification('Ошибка при получении номенклатур', 'mred', 'Ошибка');
        nomenclatures.value = [];
    });
}

function getSupplies() {
    return axios.get('/get-supplies').then((response) => {
        allSupplies.value = response.data.supplies;
        supplies.value = allSupplies.value.slice(0, itemsPerPage);
        hasMoreItems.value = supplies.value.length < allSupplies.value.length;
    }).catch(error => {
        console.error('Ошибка при получении поставок:', error);
        showNotification('Ошибка при получении поставок', 'mred', 'Ошибка');
        allSupplies.value = [];
        supplies.value = [];
    });
}

function getUnits() {
    return axios.get('/get-units-of-measurement').then((response) => {
        units.value = response.data.units;
    }).catch(error => {
        console.error('Error getting units:', error);
        units.value = [
            { name: 'шт.', type: 'integer', step: 1 },
            { name: 'кг.', type: 'decimal', step: 0.01 },
            { name: 'л.', type: 'decimal', step: 0.01 }
        ];
    });
}

watch(() => form.unit, (newValue) => {
    if (newValue && !['шт.', 'кг.', 'л.'].includes(newValue)) {
        if (isWholeNumber(newValue)) {
            form.compatibleUnit = 'шт.';
        } else {
            const lowerCase = newValue.toLowerCase();
            if (lowerCase.includes('л') || lowerCase.includes('жидк') || lowerCase.includes('volu')) {
                form.compatibleUnit = 'л.';
            } else {
                form.compatibleUnit = 'кг.';
            }
        }
    } else {
        form.compatibleUnit = newValue;
    }
});

function addSupply() {
    if (!form.nomenclatureId || !form.supplyDate || form.quantity <= 0) {
        openModal('Пожалуйста, заполните все обязательные поля корректно.', 'mred');
        return;
    }

    const selectedNomenclature = nomenclatures.value.find(n => n.id === form.nomenclatureId);
    
    const quantity = parseFloat(parseFloat(form.quantity).toFixed(4));
    const pricePerUnit = selectedNomenclature ? parseFloat(selectedNomenclature.price_per_unit) : 0;
    const price = parseFloat((quantity * pricePerUnit).toFixed(2));

    const compatibleUnit = form.compatibleUnit || (isWholeNumber(form.unit) ? 'шт.' : 'кг.');

    axios.post('/add-supply', {
        nomenclature_id: form.nomenclatureId,
        supply_date: form.supplyDate,
        quantity: quantity,
        unit: compatibleUnit,
        price: price
    }).then((response) => {
        openModal('Поставка успешно добавлена!', 'mgreen');
        getSupplies();
        resetForm();
    }).catch(error => {
        console.error('Error adding supply:', error);
        
        let errorMessage = 'Ошибка при добавлении поставки.';
        if (error.response && error.response.data) {
            if (error.response.data.message) {
                errorMessage = error.response.data.message;
            }
            
            if (error.response.data.errors) {
                const validationErrors = Object.values(error.response.data.errors).flat();
                if (validationErrors.length > 0) {
                    errorMessage = validationErrors.join('\n');
                }
            }
        }
        
        openModal(errorMessage, 'mred');
    });
}

function updateSupply() {
    axios.post('/update-supply', { ...form, supplyId: isEditId.value }).then((response) => {
        updateTable(response.data.status);
    });
}

function editSupply(supply) {
    isEdit.value = true;
    isEditId.value = supply.id;
    form.nomenclatureId = supply.nomenclature_id;
    form.supplyDate = supply.supply_date;
    form.quantity = supply.quantity;
    form.unit = supply.unit;
    form.price = supply.price;
}

function calculateTotalPrice(quantity, nomenclatureId) {
    const nomenclature = nomenclatures.value.find(n => n.id === nomenclatureId);
    const pricePerUnit = nomenclature ? nomenclature.price_per_unit : 0;
    return (quantity || 0) * pricePerUnit;
}

function validateQuantity(form) {
    if (form.quantity < 0) {
        form.quantity = 0;
    }
    
    if (isWholeNumber(form.unit)) {
        form.quantity = Math.floor(form.quantity);
    } else {
        const step = parseFloat(form.step);
        if (!isNaN(step) && step > 0) {
            const decimalPlaces = (step.toString().split('.')[1] || '').length;
            form.quantity = Math.round(form.quantity / step) * step;
            form.quantity = parseFloat(form.quantity.toFixed(decimalPlaces));
        }
    }
}

function isWholeNumber(unit) {
    const unitDetails = units.value.find(u => u.name === unit);
    return unitDetails ? unitDetails.type === 'integer' : (unit === 'шт.');
}

function getStepForUnit(unit) {
    const unitDetails = units.value.find(u => u.name === unit);
    return unitDetails ? unitDetails.step : (isWholeNumber(unit) ? 1 : 0.01);
}

function updateNomenclatureDetails(nomenclatureId) {
    const selectedNomenclature = nomenclatures.value.find(n => n.id === nomenclatureId);
    if (selectedNomenclature) {
        form.unit = selectedNomenclature.unit_of_measurement;
        form.step = getStepForUnit(form.unit);
        
        if (!['шт.', 'кг.', 'л.'].includes(form.unit)) {
            if (isWholeNumber(form.unit)) {
                form.compatibleUnit = 'шт.';
            } else {
                const lowerCase = form.unit.toLowerCase();
                if (lowerCase.includes('л') || lowerCase.includes('жидк') || lowerCase.includes('volu')) {
                    form.compatibleUnit = 'л.';
                } else {
                    form.compatibleUnit = 'кг.';
                }
            }
        } else {
            form.compatibleUnit = form.unit;
        }
        
        validateQuantity(form);
    }
}

function openModal(message, color) {
    showNotification(message, color, color === 'mgreen' ? 'Успех' : 'Ошибка');
}

function updateTable(status) {
    if (status === 'success') {
        getSupplies();
        openModal('Поставка успешно добавлена!', 'mgreen');
    } else {
        openModal('Ошибка при добавлении поставки.', 'mred');
    }
}

function deleteSupply(supplyId) {
    if (confirm('Вы действительно хотите удалить эту поставку?')) {
        axios.delete(`/delete-supply/${supplyId}`)
            .then(response => {
                if (response.data.status === 'Поставка успешно удалена') {
                    getSupplies();
                    openModal('Поставка успешно удалена!', 'mgreen');
                } else {
                    openModal('Ошибка при удалении поставки.', 'mred');
                }
            })
            .catch(error => {
                console.error('Error deleting supply:', error);
                openModal('Ошибка при удалении поставки.', 'mred');
            });
    }
}

function loadMoreSupplies() {
    currentPage.value++;
}

function toggleFilter() {
    showFilter.value = !showFilter.value;
}

function clearFilters() {
    selectedNomenclatures.value = [];
    currentPage.value = 1;
}

function resetForm() {
    form.nomenclatureId = null;
    form.quantity = 0;
    form.unit = '';
    form.compatibleUnit = '';
    form.supplyDate = null;
}
</script>

<template>
    <AppLayout title="Supplies">
        <NotificationToast 
            :notifications="notifications"
            @close="closeNotification"
        />
        
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Поставки
            </h2>
        </template>

        <div v-if="!isLoading" class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5">
                    <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">{{ isEdit ? 'Редактировать поставку' : 'Добавить новую поставку' }}</h3>
                    <form @submit.prevent>
                        <div class="mb-4">
                            <label for="nomenclatureId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Номенклатура</label>
                            <select id="nomenclatureId" v-model="form.nomenclatureId" required
                                    @change="updateNomenclatureDetails(form.nomenclatureId)"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                <option v-for="item in nomenclatures" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="supplyDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Дата поставки</label>
                            <input type="date" id="supplyDate" v-model="form.supplyDate" required
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                        </div>

                        <div class="mb-4">
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Количество <span class="text-xs text-gray-500 dark:text-gray-400">(шаг: {{ form.step }})</span>
                            </label>
                            <input type="number" id="quantity" v-model="form.quantity" 
                                   :step="form.step"
                                   min="0"
                                   @input="validateQuantity(form)"
                                   required
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                        </div>

                        <div class="mb-4">
                            <label for="totalPrice" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Общая цена (₽)</label>
                            <input type="text" id="totalPrice" :value="totalPrice.toFixed(2)" readonly
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                        </div>

                        <button v-if="!isEdit" type="submit" @click.prevent="addSupply()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить поставку
                        </button>
                        <button v-else type="submit" @click.prevent="updateSupply()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Изменить
                        </button>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mt-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Список поставок</h3>
                        <button @click="toggleFilter" class="inline-flex items-center py-2 px-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ showFilter ? 'Скрыть фильтр' : 'Показать фильтр' }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </button>
                    </div>
                    
                    <div v-if="showFilter" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4 transition-all">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">Фильтр по номенклатуре</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mb-3">
                            <div v-for="nom in nomenclatures" :key="nom.id" class="flex items-center">
                                <input type="checkbox" 
                                       :id="'nom-' + nom.id" 
                                       :value="nom.id" 
                                       v-model="selectedNomenclatures"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label :for="'nom-' + nom.id" class="ml-2 block text-sm text-gray-700 dark:text-gray-300 truncate">
                                    {{ nom.name }}
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button @click="clearFilters" 
                                    class="inline-flex items-center py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Очистить фильтры
                            </button>
                        </div>
                    </div>
                    
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">Номенклатура</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">Дата</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">Количество</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">Цена (₽)</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="supply in filteredSupplies" :key="supply.id">
                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-200 break-words">{{ supply.nomenclature.name }}</td>
                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-200">{{ supply.supply_date }}</td>
                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-200">{{ supply.quantity }} {{ supply.unit }}</td>
                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-200">{{ supply.price }}</td>
                                    <td class="px-3 py-2 text-sm font-medium">
                                        <a @click="editSupply(supply)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-500 cursor-pointer">Редактировать</a>
                                        <a @click="deleteSupply(supply.id)" class="ml-2 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-500 cursor-pointer">Удалить</a>
                                    </td>
                                </tr>
                                <tr v-if="filteredSupplies.length === 0">
                                    <td colspan="5" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                        Нет данных для отображения
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="sm:hidden">
                        <div v-for="supply in filteredSupplies" :key="supply.id" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-4 p-4">
                            <h4 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200">
                                {{ supply.nomenclature.name }}
                            </h4>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Дата: {{ supply.supply_date }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Количество: {{ supply.quantity }} {{ supply.unit }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Цена: {{ supply.price }} ₽
                            </p>
                            <div class="mt-2">
                                <a @click="editSupply(supply)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-500 cursor-pointer mr-2">Редактировать</a>
                                <a @click="deleteSupply(supply.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-500 cursor-pointer">Удалить</a>
                            </div>
                        </div>
                        <div v-if="filteredSupplies.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">
                            Нет данных для отображения
                        </div>
                    </div>
                    
                    <div v-if="hasMoreItems" class="mt-4 text-center">
                        <button @click="loadMoreSupplies" class="inline-flex items-center justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Загрузить ещё
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
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

@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}

.bg-gray-50, .bg-gray-700 {
    transition: all 0.3s ease-in-out;
}

input[type="checkbox"] {
    cursor: pointer;
}

input[type="checkbox"]:checked + label {
    color: #2563eb;
    font-weight: 500;
}

tbody tr:hover {
    background-color: rgba(243, 244, 246, 0.5);
}

.dark tbody tr:hover {
    background-color: rgba(55, 65, 81, 0.5);
}

.inline-flex.items-center.justify-center {
    transition: transform 0.2s;
}

.inline-flex.items-center.justify-center:hover {
    transform: translateY(-2px);
}
</style>

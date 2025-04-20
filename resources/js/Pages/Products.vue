<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import NotificationToast from '@/Components/NotificationToast.vue';
import useNotifications from '@/Composables/useNotifications';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

const form = useForm({
    name: '',
    markup: '',
});

let isEdit = ref(false);
let isEditId = ref(0);

const { notifications, showNotification, closeNotification } = useNotifications();

let isLoaded = ref(false);

function openModal(message, color) {
    showNotification(message, color, color === 'mgreen' ? 'Успех' : 'Ошибка');
}

const products = reactive({})

function getProducts(){
    axios.get(route('products.index'))
        .then(response => {
            products.value = response.data.products.map(product => ({
                ...product,
                total_price: parseFloat(product.total_price) || 0,
            }));
            isLoaded.value = true;
    })
    .catch(error => {
        console.error('Ошибка при получении продуктов:', error);
        showNotification('Ошибка при получении продуктов', 'mred', 'Ошибка');
    });
}
getProducts();

function updateTable(mes, isok){
    isEdit = false;
    isEditId = 0;
    
    showNotification(mes, isok ? 'mgreen' : 'mred', isok ? 'Успех' : 'Ошибка');
    
    getProducts();
}

function updateProducts(ids){
    let b = products.value.find((el) => el.id == ids);
    form.name = b.name;
    form.markup = b.markup;
    isEdit.value = true;
    isEditId.value = ids;
}

function clearProducts(){
    form.name = '';
    form.markup = '';
}

function cancelEdit() {
    isEdit.value = false;
    isEditId.value = 0;
    form.reset();
}

function deleteProducts(ids){
    let a = confirm('Вы действительно хотите удалить запись?');
    if (a == true) {
        axios.delete(route('products.delete', ids))
            .then(response => {
                openModal(response.data.status, 'mgreen');
                getProducts();
            })
            .catch(error => {
                console.error('Ошибка при удалении продукта:', error);
                openModal('Ошибка при удалении продукта', 'mred');
            });
    }
}

const selectedProduct = ref(null);
const showModal = ref(false);

function openProductDetails(productId) {
    axios.get(route('products.details', productId))
        .then(response => {
            selectedProduct.value = response.data.product;
            showModal.value = true;
        })
        .catch(error => {
            console.error('Ошибка при получении деталей продукта:', error);
        });
}

function closeModal() {
    showModal.value = false;
    selectedProduct.value = null;
}

const searchQuery = ref('');
const sortBy = ref('name');
const sortOrder = ref('asc');

const minPrice = ref('');
const maxPrice = ref('');

const errors = ref({});

function validateForm() {
    errors.value = {};
    if (!form.name) errors.value.name = 'Название продукта обязательно';
    if (!form.markup) errors.value.markup = 'Наценка обязательна';
    if (isNaN(form.markup) || form.markup < 0) errors.value.markup = 'Наценка должна быть положительным числом';
    return Object.keys(errors.value).length === 0;
}

function submitForm() {
    if (validateForm()) {
        const url = isEdit.value ? route('products.update', isEditId.value) : route('products.store');
        const data = {
            name: form.name,
            markup: form.markup
        };

        axios.post(url, data)
            .then(response => {
                openModal(response.data.flash?.message || 'Успешно добавлено', 'mgreen');
                form.reset();
                if (isEdit.value) {
                    isEdit.value = false;
                    isEditId.value = 0;
                }
                getProducts();
            })
            .catch(error => {
                console.error(error);
                if (error.response?.data?.errors) {
                    errors.value = error.response.data.errors;
                }
                openModal('Ошибка при добавлении продукта', 'mred');
            });
    }
}

const editingProduct = ref(null);

</script>

<template>
    <AppLayout title="Products">
        <NotificationToast 
            :notifications="notifications"
            @close="closeNotification"
        />
        
        <div v-if="!isLoaded" class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-500 dark:border-blue-400"></div>
        </div>
        
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Продукты
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mb-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">{{ isEdit ? 'Редактировать продукт' : 'Добавить новый продукт' }}</h3>
                    <form @submit.prevent="submitForm">
                        <input type="hidden" name="_token" :value="csrf">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Название продукта</label>
                            <input type="text" id="name" v-model="form.name" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                            <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
                        </div>
                        <div class="mb-4">
                            <label for="markup" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Наценка (%)</label>
                            <input type="number" id="markup" v-model="form.markup" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                            <p v-if="errors.markup" class="mt-2 text-sm text-red-600">{{ errors.markup }}</p>
                        </div>
                        <button type="submit" class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ isEdit ? 'Обновить продукт' : 'Добавить продукт' }}
                        </button>
                        <button v-if="isEdit" type="button" @click="cancelEdit" class="mt-2 sm:mt-0 sm:ml-2 w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Отменить
                        </button>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5">
                    <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Список продуктов</h3>
                    <div v-if="isLoaded" class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Название</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Наценка (%)</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Себестоимость (₽)</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Итоговая цена (₽)</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in products.value" :key="item.id">
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                        <Link :href="'/get-product-by-id/'+item.id">
                                            {{ item.name }}
                                        </Link>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ item.markup }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ item.price }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ parseFloat(item.total_price).toFixed(2) }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                        <a @click="updateProducts(item.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-500 cursor-pointer">Ред.</a>
                                        <a @click="deleteProducts(item.id)" class="ml-2 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-500 cursor-pointer">Удал.</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="isLoaded" class="sm:hidden">
                        <div v-for="item in products.value" :key="item.id" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-4 p-4">
                            <h4 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200">
                                <Link :href="'/get-product-by-id/'+item.id">
                                    {{ item.name }}
                                </Link>
                            </h4>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Наценка: {{ item.markup }}%
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Себестоимость: {{ item.price }} ₽
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Итоговая цена: {{ parseFloat(item.total_price).toFixed(2) }} ₽
                            </p>
                            <div class="mt-2">
                                <a @click="updateProducts(item.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-500 cursor-pointer mr-2">Редактировать</a>
                                <a @click="deleteProducts(item.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-500 cursor-pointer">Удалить</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="selectedProduct" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="my-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200">{{ selectedProduct.name }}</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Номенклатура:
                            <span v-for="nomenclature in selectedProduct.nomenclatures" :key="nomenclature.id">
                                {{ nomenclature.name }},
                            </span>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Количество: {{ selectedProduct.total_quantity }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Общая цена номенклатуры: {{ selectedProduct.total_nomenclature_price }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Себестоимость продукта: {{ selectedProduct.cost_price }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Стоимость продукта с наценкой: {{selectedProduct.total_price}} 
                        </p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="ok-btn" @click="closeModal" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
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
</style>

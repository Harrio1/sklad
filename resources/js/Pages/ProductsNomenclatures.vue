<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const form = reactive({
    product_id: null,
    nomenclatures: [{ id: null, quantity: 0, price: 0 }]
});

let isOpenModal = ref(false);
let messageResponse = ref('');
const products = ref([]);
const nomenclatures = ref([]);
const productsNomenclatures = ref([]);
const isLoading = ref(true);

function closemessageResponse() {
    isOpenModal.value = false;
}

function getProductsNomenclatures() {
    axios.get('/get-products-nomenclatures')
        .then((response) => {
            products.value = response.data.products;
            nomenclatures.value = response.data.nomenclatures;
            productsNomenclatures.value = response.data.products;
            isLoading.value = false;
        });
}

onMounted(() => {
    getProductsNomenclatures();
});

function addNomenclatureLine() {
    form.nomenclatures.push({ id: null, quantity: 0, price: 0 });
}

function removeNomenclatureLine(index) {
    form.nomenclatures.splice(index, 1);
}

function deleteProductNomenclature(productId, nomenclatureId) {
    let confirmed = confirm('Вы действительно хотите удалить эту связь?');
    if (confirmed) {
        axios.post('/delete-products-nomenclatures', {
            product_id: productId,
            nomenclature_id: nomenclatureId,
        }).then((response) => {
            isOpenModal.value = true;
            messageResponse.value = response.data.status;
            getProductsNomenclatures();
            setTimeout(closemessageResponse, 2000);
        });
    }
}

function submitForm() {
    axios.post('/add-products-nomenclatures', form)
        .then((response) => {
            isOpenModal.value = true;
            messageResponse.value = response.data.status;
            getProductsNomenclatures();
            form.product_id = null;
            form.nomenclatures = [{ id: null, quantity: 0, price: 0 }];
            setTimeout(closemessageResponse, 2000);
        });
}
</script>

<template>
    <AppLayout title="Продукты номенклатуры">
        <div class="modalMessage" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Продукты номенклатуры
            </h2>
        </template>

        <div class="py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-4">Добавление номенклатуры к продукту</h3>
                    <form @submit.prevent="submitForm">
                        <input type="hidden" name="_token" :value="csrf">

                        <div class="mb-4">
                            <label for="product" class="block text-sm font-medium text-gray-700">Продукт</label>
                            <select id="product" v-model="form.product_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>

                        <div v-for="(nomenclature, index) in form.nomenclatures" :key="index" class="mb-4 flex items-center space-x-4">
                            <div class="flex-grow">
                                <label :for="'nomenclature-' + index" class="block text-sm font-medium text-gray-700">Номенклатура</label>
                                <select :id="'nomenclature-' + index" v-model="nomenclature.id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                    <option v-for="n in nomenclatures" :key="n.id" :value="n.id">
                                        {{ n.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="w-1/4">
                                <label :for="'quantity-' + index" class="block text-sm font-medium text-gray-700">Количество</label>
                                <input type="number" :id="'quantity-' + index" v-model="nomenclature.quantity" required min="0" step="0.01"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div class="w-1/4">
                                <label :for="'price-' + index" class="block text-sm font-medium text-gray-700">Цена</label>
                                <input type="number" :id="'price-' + index" v-model="nomenclature.price" required min="0" step="0.01"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <button type="button" @click="removeNomenclatureLine(index)" class="mt-6 text-red-600 hover:text-red-900">
                                Удалить
                            </button>
                        </div>

                        <button type="button" @click="addNomenclatureLine" class="mb-4 text-blue-600 hover:text-blue-900">
                            + Добавить номенклатуру
                        </button>

                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить связь
                        </button>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5 mt-6">
                    <h3 class="text-lg font-medium mb-4">Список связей продуктов и номенклатуры</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Продукт
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Номенклатура
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Количество
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Цена
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действие
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="product in productsNomenclatures" :key="product.id">
                                    <tr v-for="nomenclature in product.nomenclatures" :key="nomenclature.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ product.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ nomenclature.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ nomenclature.pivot.quantity }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ nomenclature.pivot.price }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a @click="deleteProductNomenclature(product.id, nomenclature.id)" class="text-red-600 hover:text-red-900 cursor-pointer">
                                                Удалить
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="preloader">
            <div class="loader"></div>
        </div>
    </AppLayout>
</template>

<style scoped>
.modalMessage {
    position: fixed;
    top: 10%;
    border: 1px solid #ccc;
    box-shadow: 0px 0px 20px #444;
    background-color: rgb(0, 95, 13);
    padding: 20px 40px;
    color: #ccc;
}
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}
.loader {
    border: 16px solid #f3f3f3;
    border-radius: 50%;
    border-top: 16px solid #3498db;
    width: 120px;
    height: 120px;
    animation: spin 2s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

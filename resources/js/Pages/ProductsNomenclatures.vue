<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';

// Получаем CSRF-токен из мета-тега
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Реактивная форма для ввода данных
const form = reactive({
    product_id: null,
    nomenclatures: [{ id: null, quantity: 0, price: 0, unit: '' }]
});

// Переменные для управления модальным окном и сообщениями
const isOpenModal = ref(false);
const messageResponse = ref('');
const messageResponseColor = ref(''); // Добавляем переменную для цвета сообщения

// Переменные для хранения данных
const products = ref([]);
const productsNomenclatures = ref([]);
const isLoading = ref(true);
const availableNomenclatures = ref([]);
const isLoadingNomenclatures = ref(false);

// Функция для открытия модального окна
function openModal(message, color) {
    messageResponse.value = message;
    messageResponseColor.value = color;
    isOpenModal.value = true;
    setTimeout(() => {
        const modalElement = document.querySelector('.modalMessage');
        if (modalElement) {
            modalElement.classList.add('show');
        }
    }, 10);

    // Закрыть модальное окно через 2 секунды
    setTimeout(closemessageResponse, 3000);
}

// Функция для закрытия модального окна
function closemessageResponse() {
    const modalElement = document.querySelector('.modalMessage');
    if (modalElement) {
        modalElement.classList.remove('show');
    }
    setTimeout(() => {
        isOpenModal.value = false;
        messageResponse.value = '';
        messageResponseColor.value = '';
    }, 500);
}

// Функция для получения данных о продуктах и их номенклатурах
function getProductsNomenclatures() {
    axios.get(route('get-products-nomenclatures'))
        .then((response) => {
            products.value = response.data.products || [];
            productsNomenclatures.value = response.data.products.filter(product => product.nomenclatures && product.nomenclatures.length > 0) || [];
            isLoading.value = false;
        }).catch(() => {
            products.value = [];
            productsNomenclatures.value = [];
            isLoading.value = false;
        });
}

// Выполняем функцию при монтировании компонента
onMounted(() => {
    getProductsNomenclatures();
});

// Функция для добавления новой строки номенклатуры
function addNomenclatureLine() {
    if (canAddNomenclatureLine.value) {
        form.nomenclatures.push({ id: null, quantity: 0, price: 0, unit: '' });
    }
}

// Функция для удаления строки номенклатуры
function removeNomenclatureLine(index) {
    form.nomenclatures.splice(index, 1);
}

// Функция для удаления связи продукта и номенклатуры
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

// Функция для отправки формы
function submitForm() {
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

// Функция для загрузки доступных номенклатур
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

// Следим за изменением выбранного продукта
watch(() => form.product_id, (newValue) => {
    if (newValue) {
        loadAvailableNomenclatures();
        form.nomenclatures = [{ id: null, quantity: 0, price: 0, unit: '' }];
    } else {
        form.nomenclatures = [{ id: null, quantity: 0, price: 0, unit: '' }];
    }
});

// Вычисляемое свойство для проверки возможности добавления новой строки номенклатуры
const canAddNomenclatureLine = computed(() => {
    return availableNomenclatures.value.length > form.nomenclatures.length;
});

// Функция для проверки доступности номенклатуры
function isNomenclatureAvailable(nomenclatureId, currentIndex) {
    return !form.nomenclatures.some((n, index) => index !== currentIndex && n.id === nomenclatureId);
}

// Функция для расчета общей цены
function calculateTotalPrice(nomenclature) {
    return (nomenclature.pivot.price * nomenclature.pivot.quantity).toFixed(2);
}

// Функция для обновления деталей номенклатуры
function updateNomenclatureDetails(nomenclature) {
    const selectedNomenclature = availableNomenclatures.value.find(n => n.id === nomenclature.id);
    if (selectedNomenclature) {
        nomenclature.unit = selectedNomenclature.unit;
        nomenclature.price = selectedNomenclature.price_per_unit * nomenclature.quantity;
        
        // Округляем количество до целого числа, если единица измерения - "шт."
        if (nomenclature.unit === 'шт.') {
            nomenclature.quantity = Math.round(nomenclature.quantity);
        }
    }
}

// Функция для определения шага ввода количества
function getQuantityStep(unit) {
    return unit === 'шт.' ? 1 : 0.01;
}
</script>

<template>
    <AppLayout title="Продукты номенклатуры">
        <div class="modalMessage" :class="messageResponseColor" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Продукты номенклатуры
            </h2>
        </template>

        <div class="py-6 sm:py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mb-6">
                    <h3 class="text-lg font-medium mb-4">Добавление номенклатуры к продукту</h3>
                    <form @submit.prevent="submitForm">
                        <input type="hidden" name="_token" :value="csrf">

                        <div class="mb-4">
                            <label for="product" class="block text-sm font-medium text-gray-700">Продукт</label>
                            <select id="product" v-model="form.product_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите продукт</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>

                        <div v-for="(nomenclature, index) in form.nomenclatures" :key="index" class="mb-4">
                            <div class="mb-2">
                                <label :for="'nomenclature-' + index" class="block text-sm font-medium text-gray-700">Номенклатура</label>
                                <select :id="'nomenclature-' + index" 
                                        v-model="nomenclature.id"
                                        @change="updateNomenclatureDetails(nomenclature)"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                    <option :value="null">Выберите номенклатуру</option>
                                    <option v-for="n in availableNomenclatures" 
                                            :key="n.id" 
                                            :value="n.id"
                                            :disabled="form.nomenclatures.some(fn => fn.id === n.id && fn !== nomenclature)">
                                        {{ n.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label :for="'quantity-' + index" class="block text-sm font-medium text-gray-700">Количество</label>
                                <input type="number" 
                                       :id="'quantity-' + index" 
                                       v-model.number="nomenclature.quantity"
                                       @input="updateNomenclatureDetails(nomenclature)"
                                       required 
                                       :step="getQuantityStep(nomenclature.unit)"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div class="mb-2">
                                <label :for="'price-' + index" class="block text-sm font-medium text-gray-700">Цена (₽)</label>
                                <input type="number" :id="'price-' + index" 
                                       v-model.number="nomenclature.price"
                                       readonly
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <button type="button" @click="removeNomenclatureLine(index)" class="mt-2 text-red-600 hover:text-red-900">
                                Удалить
                            </button>
                        </div>

                        <button type="button" @click="addNomenclatureLine" 
                                :disabled="!canAddNomenclatureLine"
                                class="mb-4 text-blue-600 hover:text-blue-900" 
                                :class="{ 'opacity-50 cursor-not-allowed': !canAddNomenclatureLine }">
                            + Добавить номенклатуру
                        </button>

                        <button type="submit"
                                class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить связь
                        </button>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mt-6">
                    <h3 class="text-lg font-medium mb-4">Список связей продуктов и номенклатуры</h3>
                    <!-- Таблица для десктопной версии -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Продукт</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номенклатура</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Количество</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена (₽)</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действие</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="product in productsNomenclatures" :key="product.id">
                                    <tr v-for="nomenclature in product.nomenclatures" :key="nomenclature.id">
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ nomenclature.name }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ nomenclature.pivot.quantity }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ calculateTotalPrice(nomenclature) }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                            <a @click="deleteProductNomenclature(product.id, nomenclature.id)" class="text-red-600 hover:text-red-900 cursor-pointer">Удал.</a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <!-- Мобильное представление -->
                    <div class="sm:hidden">
                        <div v-for="product in productsNomenclatures" :key="product.id" class="mb-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-2">{{ product.name }}</h4>
                            <div v-for="nomenclature in product.nomenclatures" :key="nomenclature.id" class="bg-white shadow overflow-hidden sm:rounded-lg mb-4 p-4">
                                <p class="text-sm font-medium text-gray-500">Номенклатура: <span class="text-gray-900">{{ nomenclature.name }}</span></p>
                                <p class="text-sm font-medium text-gray-500 mt-1">Количество: <span class="text-gray-900">{{ nomenclature.pivot.quantity }}</span></p>
                                <p class="text-sm font-medium text-gray-500 mt-1">Цена: <span class="text-gray-900">{{ calculateTotalPrice(nomenclature) }} ₽</span></p>
                                <div class="mt-2">
                                    <a @click="deleteProductNomenclature(product.id, nomenclature.id)" class="text-red-600 hover:text-red-900 cursor-pointer">Удалить</a>
                                </div>
                            </div>
                        </div>
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
    right: -100%; /* Начальная позиция за пределами экрана */
    border: 1px solid #ccc;
    box-shadow: 0px 0px 20px #444;
    background-color: rgba(0, 95, 13, 0.9); /* Более мягкий цвет */
    padding: 20px 40px;
    color: #fff;
    transition: right 0.5s ease; /* Анимация появления */
    z-index: 1000;
}

.modalMessage.show {
    right: 0%; /* Конечная позиция на экране */
}

.mgreen {
    background-color: rgba(0, 95, 13, 0.9);
}

.mred {
    background-color: rgba(104, 2, 10, 0.9);
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

@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

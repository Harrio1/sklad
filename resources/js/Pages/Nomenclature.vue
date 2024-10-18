<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';

// Получаем CSRF-токен из мета-тега
let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Массив единиц измерения
const units = ['шт.', 'кг.', 'л.'];

// Реактивная форма для ввода данных
const form = reactive({
    name: null,
    suppliers_id: null,
    price_per_unit: null,
    unit_of_measurement: null, // Добавляем поле для единицы измерения
});

// Добавляем новую переменную для отслеживания шага ввода цены
const priceStep = ref(1);

// Следим за изменением единицы измерения
watch(() => form.unit_of_measurement, (newValue) => {
    if (newValue === 'шт.') {
        priceStep.value = 1;
        // Округляем текущее значение до целого числа
        if (form.price_per_unit) {
            form.price_per_unit = Math.round(form.price_per_unit);
        }
    } else {
        priceStep.value = 0.01;
    }
});

// Переменные для управления модальным окном и сообщениями
let isOpenModal = ref(false);
let messageResponse = ref('');
const suppliers = ref([]);
const isLoading = ref(true); // Добавляем состояние загрузки

let messageResponseColor = ref(''); // Добавляем переменную для цвета сообщения

// Функция для открытия модального окна
function openModal(message, color) {
    messageResponse.value = message;
    messageResponseColor.value = color;
    isOpenModal.value = true;
    setTimeout(() => {
        document.querySelector('.modalMessage').classList.add('show');
    }, 10);

    // Закрыть модальное окно через 2 секунды
    setTimeout(closemessageResponse, 3000);
}

// Функция для закрытия модального окна
function closemessageResponse() {
    document.querySelector('.modalMessage').classList.remove('show');
    setTimeout(() => {
        isOpenModal.value = false;
        messageResponse.value = '';
        messageResponseColor.value = '';
    }, 500);
}

// Реактивный объект для хранения данных номенклатуры
const nomenclature = reactive({});

// Функция для получения данных номенклатуры
function getNomenclature() {
    axios({
        method: 'get',
        url: '/get-nomenclature',
    }).then((response) => {
        nomenclature.value = response.data.nomenclatures;
        isLoading.value = false; // Отключаем прелоадер после загрузки данных
    });
}

// Функция для получения данных поставщиков
function getSuppliers() {
    axios({
        method: 'get',
        url: '/get-suppliers',
    }).then((response) => {
        suppliers.value = response.data.suppliers;
        isLoading.value = false; // Отключаем прелоадер после загрузки данных
    });
}

// Выполняем функции при монтировании компонента
onMounted(() => {
    getNomenclature();
    getSuppliers();
});

// Функция для удаления номенклатуры
function deleteNomenclature(ids) {
    let a = confirm('Вы действительно хотите удалить запись?');
    if (a == true) {
        axios.post('/delete-nomenclature', {
            nomenclature_id: ids,
        }).then((response) => {
            openModal(response.data.status, 'mgreen');
            getNomenclature();
        });
    }
}

// Функция для обновления таблицы
function updateTable(mes) {
    openModal(mes, 'mgreen');
    getNomenclature();
    form.name = '';
    form.suppliers_id = '';
    form.price_per_unit = '';
    form.unit_of_measurement = '';
}

// Функция для отправки данных номенклатуры
function responseNomenclature() {
    // Перед отправкой данных, убедимся, что цена соответствует выбранной единице измерения
    if (form.unit_of_measurement === 'шт.') {
        form.price_per_unit = Math.round(form.price_per_unit);
    }

    axios({
        method: 'post',
        url: '/add-nomenclature',
        data: {
            csrf: csrf,
            name: form.name,
            supplier_id: form.suppliers_id,
            price_per_unit: form.price_per_unit,
            unit_of_measurement: form.unit_of_measurement, // Добавляем поле в отправляемые данные
        },
    }).then((response) => {
        updateTable(response.data.status);
    });
}
</script>

<template>
    <AppLayout title="Nomenclature">
        <div class="modalMessage" :class="messageResponseColor" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Номенклатура
            </h2>
        </template>

        <div class="py-6 sm:py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mb-6">
                    <h3 class="text-lg font-medium mb-4">Добавить новую номенклатуру</h3>
                    <form>
                        <input type="hidden" name="_token" :value="csrf">

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Имя</label>
                            <input type="text" id="name" v-model="form.name" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="mb-4">
                            <label for="supplier" class="block text-sm font-medium text-gray-700">Поставщик</label>
                            <select id="supplier" v-model="form.suppliers_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                    {{ supplier.name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="unit_of_measurement" class="block text-sm font-medium text-gray-700">Единица измерения</label>
                            <select id="unit_of_measurement" v-model="form.unit_of_measurement" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="unit in units" :key="unit" :value="unit">
                                    {{ unit }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="price_per_unit" class="block text-sm font-medium text-gray-700">Цена за единицу (₽)</label>
                            <input type="number" id="price_per_unit" v-model="form.price_per_unit" 
                                   :step="priceStep" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <button type="submit" @click.prevent="responseNomenclature"
                                class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить номенклатуру
                        </button>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5">
                    <h3 class="text-lg font-medium mb-4">Список номенклатуры</h3>
                    <!-- Таблица для десктопной версии -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Имя
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Поставщик
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ед. изм.
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Цена (₽)
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Кол-во
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Сумма (₽)
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действие
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in nomenclature.value" :key="item.id">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ item.name }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.supplier.name }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.unit_of_measurement }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.price_per_unit }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.total_quantity }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.total_price }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                        <a @click="deleteNomenclature(item.id)" :data="item.id" class="text-red-600 hover:text-red-900">Удалить</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Мобильное представление -->
                    <div class="sm:hidden">
                        <div v-for="item in nomenclature.value" :key="item.id" class="bg-white shadow overflow-hidden rounded-lg mb-4 p-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ item.name }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Поставщик: {{ item.supplier.name }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Ед. изм.: {{ item.unit_of_measurement }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Цена: {{ item.price_per_unit }} ₽
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Количество: {{ item.total_quantity }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Сумма: {{ item.total_price }} ₽
                            </p>
                            <div class="mt-2">
                                <a @click="deleteNomenclature(item.id)" :data="item.id" class="text-red-600 hover:text-red-900">Удалить</a>
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

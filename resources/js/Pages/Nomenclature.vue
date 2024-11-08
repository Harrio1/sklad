<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import ExcelJS from 'exceljs';

// Получаем CSRF-токен из мета-тега
let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Массив единиц измерения
const units = ['шт.', 'кг.', 'л.'];

// Реактивная форма для ввода данных
const form = reactive({
    name: null,
    suppliers_id: null,
    price_per_unit: null,
    unit_of_measurement: null,
});

// Добавляем новую переменную для отслеживания шага ввода цены
const priceStep = ref(1);

// Следим за изменением единицы измерения
watch(() => form.unit_of_measurement, (newValue) => {
    if (newValue === 'шт.') {
        priceStep.value = 1;
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
const isLoading = ref(true);

let messageResponseColor = ref('');

// Функция для открытия модального окна
function openModal(message, color) {
    messageResponse.value = message;
    messageResponseColor.value = color;
    isOpenModal.value = true;
    setTimeout(() => {
        document.querySelector('.modalMessage').classList.add('show');
    }, 10);

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

// Реактивный объект для хранения данных номенклатуры
const nomenclature = reactive({});
const turnoverData = ref([]); // Данные для оборотной ведомости

// Функция для получения данных номенклатуры
function getNomenclature() {
    axios.get('/get-nomenclature').then((response) => {
        nomenclature.value = response.data.nomenclatures;
        isLoading.value = false;
    });
}

// Функция для получения данных поставщиков
function getSuppliers() {
    axios.get('/get-suppliers').then((response) => {
        suppliers.value = response.data.suppliers;
        isLoading.value = false;
    });
}

// Функция для получения данных оборотной ведомости
function getTurnoverData() {
    axios.get('/get-turnover-data').then((response) => {
        turnoverData.value = response.data.turnover;
    }).catch(error => {
        console.error("Ошибка при получении данных оборотной ведомости:", error);
    });
}

// Выполняем функции при монтировании компонента
onMounted(() => {
    getNomenclature();
    getSuppliers();
    getTurnoverData();
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
    getTurnoverData();
    form.name = '';
    form.suppliers_id = '';
    form.price_per_unit = '';
    form.unit_of_measurement = '';
}

// Функция для отправки данных номенклатуры
function responseNomenclature() {
    if (form.unit_of_measurement === 'шт.') {
        form.price_per_unit = Math.round(form.price_per_unit);
    }

    axios.post('/add-nomenclature', {
        csrf: csrf,
        name: form.name,
        supplier_id: form.suppliers_id,
        price_per_unit: form.price_per_unit,
        unit_of_measurement: form.unit_of_measurement,
    }).then((response) => {
        updateTable(response.data.status);
    });
}

// Инициализация текущей вкладки из localStorage или по умолчанию
const currentTab = ref(localStorage.getItem('currentTab') || 'nomenclature');

// Следим за изменением текущей вкладки и сохраняем в localStorage
watch(currentTab, (newTab) => {
    localStorage.setItem('currentTab', newTab);
});

async function downloadExcel() {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Оборотная ведомость');

    // Добавляем заголовки
    worksheet.columns = [
        { header: 'Имя', key: 'name', width: 20 },
        { header: 'Поставщик', key: 'supplier', width: 20 },
        { header: 'Ед. изм.', key: 'unit', width: 10 },
        { header: 'Остаток на начало', key: 'start_balance', width: 20 },
        { header: 'Поступление', key: 'received', width: 15 },
        { header: 'Остаток (текущее)', key: 'current_balance', width: 20  },
        { header: 'Сумма (₽)', key: 'total_price', width: 15 }
    ];

    // Добавляем данные
    turnoverData.value.forEach(item => {
        worksheet.addRow({
            name: item.name,
            supplier: item.supplier.name,
            unit: item.unit_of_measurement,
            start_balance: item.start_balance,
            received: item.received,
            current_balance: item.current_balance,
            total_price: item.total_price
        });
    });

    // Получаем текущую дату и форматируем ее
    const today = new Date();
    const formattedDate = `${String(today.getDate()).padStart(2, '0')}.${String(today.getMonth() + 1).padStart(2, '0')}.${today.getFullYear()}`;

    // Генерируем и скачиваем файл
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `Оборотная_ведомость_${formattedDate}.xlsx`;
    link.click();
}
</script>

<template>
    <AppLayout title="Nomenclature">
        <div class="modalMessage" :class="messageResponseColor" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <div class="flex space-x-4">
                <h2 
                    @click="currentTab = 'nomenclature'" 
                    :class="{ active: currentTab === 'nomenclature' }"
                    class="font-semibold text-xl text-gray-800 leading-tight cursor-pointer"
                >
                    Номенклатура
                </h2>
                <h2 
                    @click="currentTab = 'turnover'" 
                    :class="{ active: currentTab === 'turnover' }"
                    class="font-semibold text-xl text-gray-800 leading-tight cursor-pointer"
                >
                    Оборотная ведомость
                </h2>
            </div>
        </template>

        <div class="py-6 sm:py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="currentTab === 'nomenclature'">
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

                <div v-if="currentTab === 'turnover'" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Оборотная ведомость</h3>
                        <button @click="downloadExcel" class="text-blue-600 hover:text-blue-900">
                            Скачать как Excel
                        </button>
                    </div>
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
                                        Остаток на начало
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Поступление
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Остаток (текущее)
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Сумма (₽)
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in turnoverData" :key="item.id">
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
                                        <div class="text-sm text-gray-900">{{ item.start_balance }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.received }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.current_balance }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.total_price }}</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="sm:hidden">
                        <div v-for="item in turnoverData" :key="item.id" class="bg-white shadow overflow-hidden rounded-lg mb-4 p-4">
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
                                Остаток на начало: {{ item.start_balance }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Поступление: {{ item.received }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Остаток (текущее): {{ item.current_balance }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Сумма: {{ item.total_price }} ₽
                            </p>
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

h2 {
    padding: 5px 10px;
    cursor: pointer;
    position: relative;
}

h2.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: rgb(129, 140, 248);
}

@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>


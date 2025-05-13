<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted, watch } from 'vue';
import NotificationToast from '@/Components/NotificationToast.vue';
import useNotifications from '@/Composables/useNotifications';
import axios from 'axios';
import ExcelJS from 'exceljs';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const units = ref([]);
const { notifications, showNotification, closeNotification } = useNotifications();

const form = reactive({
    name: null,
    suppliers_id: null,
    price_per_unit: null,
    unit_of_measurement: null,
    unit_of_measurement_last: null,
});

const isUnitModalOpen = ref(false);
const unitForm = reactive({
    name: '',
    type: 'integer',
    step: 1,
    min_value: 0
});

const priceStep = ref(1);
const suppliers = ref([]);
const isLoading = ref(true);
const nomenclature = reactive({});
const turnoverData = ref([]);
const exportLoading = ref(false);

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

// Функции для работы с данными
function getNomenclature() {
    axios.get('/get-nomenclature')
        .then((response) => {
        nomenclature.value = response.data.nomenclatures;
        isLoading.value = false;
        })
        .catch((error) => {
            showNotification('Ошибка при загрузке номенклатуры', 'mred', 'Ошибка');
            console.error(error);
    });
}

function getSuppliers() {
    axios.get('/get-suppliers')
        .then((response) => {
        suppliers.value = response.data.suppliers;
        isLoading.value = false;
        })
        .catch((error) => {
            showNotification('Ошибка при загрузке поставщиков', 'mred', 'Ошибка');
            console.error(error);
    });
}

function getUnits() {
    axios.get('/get-units-of-measurement')
        .then((response) => {
            units.value = response.data.units.map(unit => unit.name);
        })
        .catch(error => {
            console.error("Ошибка при получении единиц измерения:", error);
            units.value = ['шт.', 'кг.', 'л.'];
        });
}

function getTurnoverData() {
    axios.get('/get-turnover-data')
        .then((response) => {
        turnoverData.value = response.data.turnover;
        })
        .catch(error => {
        console.error("Ошибка при получении данных оборотной ведомости:", error);
            showNotification('Ошибка при загрузке оборотной ведомости', 'mred', 'Ошибка');
});
}

// Функции для модальных окон и уведомлений
function deleteNomenclature(ids) {
    let confirmed = confirm('Вы действительно хотите удалить запись?');
    if (confirmed) {
        axios.post('/delete-nomenclature', {
            nomenclature_id: ids,
        })
        .then((response) => {
            showNotification(response.data.status, 'mgreen', 'Успех');
            getNomenclature();
        })
        .catch((error) => {
            showNotification('Ошибка при удалении', 'mred', 'Ошибка');
            console.error(error);
        });
    }
}

function updateTable(message) {
    showNotification(message, 'mgreen', 'Успех');
    getNomenclature();
    getTurnoverData();
    resetForm();
}

function resetForm() {
    form.name = '';
    form.suppliers_id = '';
    form.price_per_unit = '';
    form.unit_of_measurement = '';
}

function responseNomenclature() {
    if (!form.name || !form.suppliers_id || !form.price_per_unit || !form.unit_of_measurement) {
        showNotification('Заполните все поля формы', 'mred', 'Ошибка');
        return;
    }

    if (form.unit_of_measurement === 'шт.') {
        form.price_per_unit = Math.round(form.price_per_unit);
    }

    axios.post('/add-nomenclature', {
        csrf: csrf,
        name: form.name,
        supplier_id: form.suppliers_id,
        price_per_unit: form.price_per_unit,
        unit_of_measurement: form.unit_of_measurement,
    })
    .then((response) => {
        updateTable(response.data.status);
    })
    .catch((error) => {
        showNotification('Ошибка при добавлении номенклатуры', 'mred', 'Ошибка');
        console.error(error);
    });
}

// Работа с модальным окном единиц измерения
function openUnitModal() {
    unitForm.name = '';
    unitForm.type = 'integer';
    unitForm.step = 1;
    unitForm.min_value = 0;
    isUnitModalOpen.value = true;
}

function closeUnitModal() {
    isUnitModalOpen.value = false;
}

function handleUnitChange() {
    if (form.unit_of_measurement === 'create_new') {
        openUnitModal();
        form.unit_of_measurement = form.unit_of_measurement_last || '';
    } else {
        form.unit_of_measurement_last = form.unit_of_measurement;
    }
}

function createUnit() {
    if (!unitForm.name) {
        showNotification('Введите название единицы измерения', 'mred', 'Ошибка');
        return;
    }
    
    axios.post('/add-unit-of-measurement', {
        name: unitForm.name,
        type: unitForm.type,
        step: unitForm.step,
        min_value: unitForm.min_value
    })
    .then((response) => {
        units.value.push(unitForm.name);
        form.unit_of_measurement = unitForm.name;
        form.unit_of_measurement_last = unitForm.name;
        
        if (unitForm.type === 'integer') {
            priceStep.value = 1;
        } else {
            priceStep.value = unitForm.step || 0.01;
        }
        
        closeUnitModal();
        showNotification(response.data.status, 'mgreen', 'Успех');
    })
    .catch(error => {
        let errorMessage = 'Ошибка при создании единицы измерения';
        if (error.response && error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
        }
        showNotification(errorMessage, 'mred', 'Ошибка');
    });
}

// Управление вкладками
const currentTab = ref(localStorage.getItem('currentTab') || 'nomenclature');

watch(currentTab, (newTab) => {
    localStorage.setItem('currentTab', newTab);
});

// Экспорт данных в Excel
async function exportToExcel() {
    if (!turnoverData.value || turnoverData.value.length === 0) {
        showNotification('Нет данных для экспорта', 'mred', 'Ошибка');
        return;
    }

    exportLoading.value = true;

    try {
        // Создаем новую книгу Excel
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Оборотная ведомость');

        // Формируем дату для заголовка
        const currentDate = new Date().toLocaleDateString();
        
        // Заголовок с датой
        worksheet.mergeCells('A1:G1');
        const titleCell = worksheet.getCell('A1');
        titleCell.value = `Оборотная ведомость`;
        titleCell.font = { bold: true, size: 14 };
        titleCell.alignment = { horizontal: 'center' };
        
        // Дата формирования
        worksheet.mergeCells('A2:G2');
        const dateCell = worksheet.getCell('A2');
        dateCell.value = `Дата формирования: ${currentDate}`;
        dateCell.alignment = { horizontal: 'center' };
        
        // Устанавливаем заголовки колонок в соответствии с данными оборотной ведомости
        const headers = [
            'Наименование',
            'Поставщик',
            'Ед. изм.',
            'Остаток на начало',
            'Поступление',
            'Остаток (текущее)',
            'Сумма (₽)'
        ];
        
        // Добавляем строку заголовков (строка 4, так как строки 1-2 это заголовок и дата)
        worksheet.addRow([]); // пустая строка 3 для отступа
        const headerRow = worksheet.addRow(headers);
        
        // Применяем стиль к заголовкам
        headerRow.font = { bold: true };
        headerRow.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFE0E0E0' }
        };
        
        // Устанавливаем ширину колонок
    worksheet.columns = [
            { key: 'name', width: 30 },
            { key: 'supplier', width: 20 },
            { key: 'unit', width: 10 },
            { key: 'start_balance', width: 20 },
            { key: 'received', width: 15 },
            { key: 'current_balance', width: 20 },
            { key: 'total_price', width: 15 }
    ];

        // Добавляем данные оборотной ведомости
        let totalSum = 0;

    turnoverData.value.forEach(item => {
            // Преобразуем строку в число для суммирования
            const price = typeof item.total_price === 'string' 
                ? parseFloat(item.total_price.replace(/[^\d.-]/g, '')) 
                : parseFloat(item.total_price) || 0;
                
            totalSum += price;
            
        worksheet.addRow({
            name: item.name,
            supplier: item.supplier.name,
            unit: item.unit_of_measurement,
            start_balance: item.start_balance,
            received: item.received,
            current_balance: item.current_balance,
                total_price: price.toFixed(2)
        });
    });

        // Добавляем итоговую строку
        const totalRow = worksheet.addRow({
            name: 'ИТОГО:',
            supplier: '',
            unit: '',
            start_balance: '',
            received: '',
            current_balance: '',
            total_price: totalSum.toFixed(2)
        });
        
        // Форматируем итоговую строку
        totalRow.font = { bold: true };
        totalRow.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFE6F0D0' }
        };

        // Выравнивание числовых ячеек вправо
        worksheet.eachRow({ includeEmpty: false }, (row, rowNumber) => {
            if (rowNumber > 4) { // Пропускаем заголовки и пустую строку
                row.eachCell((cell, colNumber) => {
                    if (colNumber >= 4) { // Числовые колонки
                        cell.alignment = { horizontal: 'right' };
                    }
                });
            }
        });

        // Добавляем границы для всех ячеек
        const borderStyle = {
            top: { style: 'thin' },
            left: { style: 'thin' },
            bottom: { style: 'thin' },
            right: { style: 'thin' }
        };
        
        worksheet.eachRow((row) => {
            row.eachCell((cell) => {
                cell.border = borderStyle;
            });
        });

    // Генерируем и скачиваем файл
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        
    const link = document.createElement('a');
        link.href = url;
        link.download = `Оборотная_ведомость_${currentDate.replace(/\./g, '-')}.xlsx`;
    link.click();
        
        window.URL.revokeObjectURL(url);
        showNotification('Экспорт успешно выполнен', 'mgreen', 'Успех');
        
    } catch (error) {
        console.error('Ошибка при экспорте в Excel:', error);
        showNotification('Ошибка при экспорте в Excel', 'mred', 'Ошибка');
    } finally {
        exportLoading.value = false;
    }
}

// Инициализация данных
onMounted(() => {
    getNomenclature();
    getSuppliers();
    getUnits();
    getTurnoverData();
});
</script>

<template>
    <AppLayout title="Номенклатура">
        <NotificationToast 
            :notifications="notifications"
            @close="closeNotification"
        />
        
        <template #header>
            <div class="flex space-x-4">
                <h2 
                    @click="currentTab = 'nomenclature'" 
                    :class="{ active: currentTab === 'nomenclature' }"
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight cursor-pointer"
                >
                    Номенклатура
                </h2>
                <h2 
                    @click="currentTab = 'turnover'" 
                    :class="{ active: currentTab === 'turnover' }"
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight cursor-pointer"
                >
                    Оборотная ведомость
                </h2>
            </div>
        </template>

        <div class="py-6 sm:py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="currentTab === 'nomenclature'">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Добавить новую номенклатуру</h3>
                        <form>
                            <input type="hidden" name="_token" :value="csrf">

                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Имя</label>
                                <input type="text" id="name" v-model="form.name" required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                            </div>

                            <div class="mb-4">
                                <label for="supplier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Поставщик</label>
                                <select id="supplier" v-model="form.suppliers_id" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                        {{ supplier.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="unit_of_measurement" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Единица измерения</label>
                                <div class="relative">
                                    <select id="unit_of_measurement" v-model="form.unit_of_measurement" @change="handleUnitChange" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                        <option value="create_new">Создать единицу измерения...</option>
                                    <option v-for="unit in units" :key="unit" :value="unit">
                                        {{ unit }}
                                    </option>
                                </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="price_per_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Цена за единицу (₽)</label>
                                <input type="number" id="price_per_unit" v-model="form.price_per_unit" 
                                    :step="priceStep" required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                            </div>

                            <button type="submit" @click.prevent="responseNomenclature"
                                    class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Добавить номенклатуру
                            </button>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5">
                        <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Список номенклатуры</h3>
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Имя
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Поставщик
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Ед. изм.
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Цена (₽)
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Кол-во
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Сумма (₽)
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Действие
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in nomenclature.value" :key="item.id">
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                {{ item.name }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.supplier.name }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.unit_of_measurement }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.price_per_unit }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.total_quantity }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.total_price }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                            <a @click="deleteNomenclature(item.id)" :data="item.id" class="text-red-600 hover:text-red-900">Удалить</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="sm:hidden">
                            <div v-for="item in nomenclature.value" :key="item.id" class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg mb-4 p-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200">
                                    {{ item.name }}
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                    Поставщик: {{ item.supplier.name }}
                                </p>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                    Ед. изм.: {{ item.unit_of_measurement }}
                                </p>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                    Цена: {{ item.price_per_unit }} ₽
                                </p>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                    Количество: {{ item.total_quantity }}
                                </p>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                    Сумма: {{ item.total_price }} ₽
                                </p>
                                <div class="mt-2">
                                    <a @click="deleteNomenclature(item.id)" :data="item.id" class="text-red-600 hover:text-red-900">Удалить</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="currentTab === 'turnover'" class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Оборотная ведомость</h3>
                        <button 
                    @click="exportToExcel" 
                    :disabled="exportLoading"
                    class="inline-flex items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                >
                    <svg v-if="exportLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    {{ exportLoading ? 'Экспорт...' : 'Экспорт в Excel' }}
                        </button>
                    </div>
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Имя
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Поставщик
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Ед. изм.
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Остаток на начало
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Поступление
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Остаток (текущее)
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Сумма (₽)
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in turnoverData" :key="item.id">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                            {{ item.name }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.supplier.name }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.unit_of_measurement }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.start_balance }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.received }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.current_balance }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ item.total_price }}</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="sm:hidden">
                        <div v-for="item in turnoverData" :key="item.id" class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg mb-4 p-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200">
                                {{ item.name }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Поставщик: {{ item.supplier.name }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Ед. изм.: {{ item.unit_of_measurement }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Остаток на начало: {{ item.start_balance }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Поступление: {{ item.received }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Остаток (текущее): {{ item.current_balance }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Сумма: {{ item.total_price }} ₽
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-500 dark:border-blue-400"></div>
        </div>
        <Transition name="modal">
            <div v-if="isUnitModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeUnitModal"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                        Добавить новую единицу измерения
                                    </h3>
                                    <div class="mt-4">
                                        <div class="mb-4">
                                            <label for="unit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Название единицы измерения</label>
                                            <input type="text" id="unit_name" v-model="unitForm.name" required
                                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                                        </div>
                                        <div class="mb-4">
                                            <label for="unit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Тип значения</label>
                                            <select id="unit_type" v-model="unitForm.type" required
                                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                                <option value="integer">Целое число</option>
                                                <option value="decimal">Дробное число</option>
                                            </select>
                                        </div>
                                        <div class="mb-4" v-if="unitForm.type === 'decimal'">
                                            <label for="unit_step" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Шаг изменения</label>
                                            <input type="number" id="unit_step" v-model="unitForm.step" step="0.01" min="0.01" required
                                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                                        </div>
                                        <div class="mb-4">
                                            <label for="unit_min" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Минимальное значение</label>
                                            <input type="number" id="unit_min" v-model="unitForm.min_value" :step="unitForm.type === 'integer' ? '1' : '0.01'" min="0" required
                                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="createUnit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Добавить
                            </button>
                            <button type="button" @click="closeUnitModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Отмена
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
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

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s, transform 0.3s;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>


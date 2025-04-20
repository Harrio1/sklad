<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, computed, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';
import ExcelJS from 'exceljs';
import NotificationToast from '@/Components/NotificationToast.vue';
import useNotifications from '@/Composables/useNotifications';

const products = ref([]);
const nomenclatures = ref([]);
const days = ref([
    { short: 'Пн', full: 'Понедельник' },
    { short: 'Вт', full: 'Вторник' },
    { short: 'Ср', full: 'Среда' },
    { short: 'Чт', full: 'Четверг' },
    { short: 'Пт', full: 'Пятница' },
    { short: 'Сб', full: 'Суббота' }
]);
const tableData = ref([]);
const selectedProduct = ref(null);
const orders = ref([]);
const activeTab = ref('all');
const searchQuery = ref('');
const isLoading = ref(false);
const isMobile = ref(false);
const mobileView = ref('products');

const { notifications, showNotification, closeNotification } = useNotifications();
const exportLoading = ref(false);

function checkMobile() {
    isMobile.value = window.innerWidth < 768;
}

const filteredProducts = computed(() => {
    if (!searchQuery.value) return products.value;
    
    const query = searchQuery.value.toLowerCase();
    return products.value.filter(product => 
        product.name.toLowerCase().includes(query)
    );
});

const filteredOrders = computed(() => {
    return orders.value.filter(order => order.status === 2);
});

function loadProducts() {
    isLoading.value = true;
    axios.get('/api/products')
        .then(response => {
            products.value = response.data.products;
            if (products.value.length > 0) {
                selectProduct(products.value[0].id);
            }
            isLoading.value = false;
        })
        .catch(error => {
            console.error('Ошибка при загрузке продуктов:', error);
            showNotification('Ошибка при загрузке продуктов', 'mred', 'Ошибка');
            isLoading.value = false;
        });
}

function loadNomenclatures(productId) {
    isLoading.value = true;
    axios.get(`/api/products/${productId}/nomenclatures`)
        .then(response => {
            nomenclatures.value = response.data.nomenclatures;
            updateTable();
            isLoading.value = false;
        })
        .catch(error => {
            console.error('Ошибка при загрузке номенклатур:', error);
            showNotification('Ошибка при загрузке номенклатур', 'mred', 'Ошибка');
            isLoading.value = false;
        });
}

function loadOrders() {
    isLoading.value = true;
    axios.get('/api/orders')
        .then(response => {
            orders.value = response.data.orders;
            updateTable();
            isLoading.value = false;
        })
        .catch(error => {
            console.error('Ошибка при загрузке заказов:', error);
            showNotification('Ошибка при загрузке заказов', 'mred', 'Ошибка');
            isLoading.value = false;
        });
}

function selectProduct(productId) {
    selectedProduct.value = productId;
    loadNomenclatures(productId);
}

function setActiveTab(tab) {
    activeTab.value = tab;
}

function clearSearch() {
    searchQuery.value = '';
}

function getOrderCountByDay(productId) {
    const orderCountByDay = {
        'Пн': 0,
        'Вт': 0,
        'Ср': 0,
        'Чт': 0,
        'Пт': 0,
        'Сб': 0
    };

    const ordersWithStatus2 = orders.value.filter(order => order.status === 2);

    ordersWithStatus2.forEach(order => {
        try {
            const orderDate = new Date(order.created_at);
            const dayOfWeek = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'][orderDate.getDay()];
            
            let productsInOrder;
            if (typeof order.products === 'string') {
                productsInOrder = JSON.parse(order.products);
            } else if (Array.isArray(order.products)) {
                productsInOrder = order.products;
            } else {
                console.error('Неизвестный формат order.products:', order.products);
                return;
            }
            
            productsInOrder.forEach(product => {
                if (product.id === productId) {
                    const quantity = product.quantity || 0;
                    orderCountByDay[dayOfWeek] += parseInt(quantity);
                }
            });
        } catch (error) {
            console.error('Ошибка при обработке заказа:', error, order);
        }
    });
    
    return orderCountByDay;
}

function updateTable() {
    tableData.value = nomenclatures.value.map(nomenclature => {
        const orderCountByDay = getOrderCountByDay(selectedProduct.value);
        
        const totalOrderedQuantity = Object.values(orderCountByDay).reduce((sum, count) => sum + (Number(count) || 0), 0);
        
        const quantity = Number(nomenclature.quantity) || 0;
        const totalConsumedQuantity = quantity * totalOrderedQuantity;
        
        const dailyConsumedQuantity = {};
        for (const day in orderCountByDay) {
            const dayCount = Number(orderCountByDay[day]) || 0;
            dailyConsumedQuantity[day] = dayCount * quantity;
        }
        
        const price = Number(nomenclature.price) || 0;
        return {
            id: nomenclature.nomenclature.id,
            name: nomenclature.nomenclature.name,
            unit: nomenclature.nomenclature.unit_of_measurement,
            calculatedQuantity: totalConsumedQuantity,
            dailyData: dailyConsumedQuantity,
            dailyOrdersData: orderCountByDay,   
            total: price * totalConsumedQuantity,
        };
    });
}

async function exportToExcel() {
    if (tableData.value.length === 0) {
        showNotification('Нет данных для экспорта', 'mred', 'Ошибка');
        return;
    }

    exportLoading.value = true;
    
    try {
        const productInfo = products.value.find(p => p.id === selectedProduct.value);
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Товарооборот');
        
        worksheet.mergeCells('A1:I1');
        const titleCell = worksheet.getCell('A1');
        titleCell.value = `${productInfo ? productInfo.name : 'Товарооборот'}`;
        titleCell.font = { bold: true, size: 14 };
        titleCell.alignment = { horizontal: 'center' };
        
        worksheet.mergeCells('A2:I2');
        const dateCell = worksheet.getCell('A2');
        dateCell.value = `Дата формирования: ${new Date().toLocaleDateString()}`;
        dateCell.alignment = { horizontal: 'center' };
        
        let daysForExport;
        if (activeTab.value === 'all') {
            daysForExport = days.value;
        } else {
            daysForExport = days.value.filter(day => day.short === activeTab.value);
        }
        
        const headers = ['Номенклатура', 'Ед. изм.'];
        
        daysForExport.forEach(day => {
            headers.push(day.short);
        });
        
        headers.push('Всего');
        
        worksheet.addRow([]); 
        
        let totalManufactured = 0;
        const manufacturedData = [];
        manufacturedData.push('Изготовлено'); 
        manufacturedData.push(''); 
        
        daysForExport.forEach(day => {
            const count = tableData.value && tableData.value[0] 
                ? tableData.value[0].dailyOrdersData[day.short] || 0 
                : 0;
            
            manufacturedData.push(count);
            totalManufactured += count;
        });
        
        manufacturedData.push(totalManufactured);
        
        const mRow = worksheet.addRow(manufacturedData);
        mRow.font = { bold: true, size: 14 };
        mRow.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFBCDFEF' } 
        };
        
        mRow.eachCell((cell, colNumber) => {
            if (colNumber > 2) { 
                cell.alignment = { horizontal: 'right' };
                if (cell.value > 0) {
                    cell.font = { bold: true, size: 14, color: { argb: 'FF0070C0' } };
                }
            } else if (colNumber === 1) { 
                cell.font = { bold: true, size: 14, color: { argb: 'FF000080' } };
            }
        });
        
        const headerRow = worksheet.addRow(headers);
        
        headerRow.font = { bold: true };
        headerRow.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFE0E0E0' }
        };
        
        const columnWidths = [
            { key: 'name', width: 30 },
            { key: 'unit', width: 10 }
        ];
        
        daysForExport.forEach(day => {
            columnWidths.push({ 
                key: day.short, 
                width: 10
            });
        });
        
        columnWidths.push({ 
            key: 'total', 
            width: 15
        });
        
        worksheet.columns = columnWidths;
        
        tableData.value.forEach(item => {
            const rowData = {
                name: item.name,
                unit: item.unit,
                total: (item.calculatedQuantity || 0).toFixed((item.calculatedQuantity || 0) % 1 === 0 ? 0 : 1)
            };
            
            daysForExport.forEach(day => {
                const value = item.dailyData[day.short] !== undefined ? Number(item.dailyData[day.short]) : 0;
                rowData[day.short] = value.toFixed(value % 1 === 0 ? 0 : 1);
            });
            
            worksheet.addRow(rowData);
        });
        

        
        daysForExport.forEach(day => {
            const total = tableData.value.reduce((sum, item) => {
                const value = item.dailyData[day.short];
                return sum + (Number(value) || 0);
            }, 0);
        });
        
  
        worksheet.eachRow({ includeEmpty: false }, (row, rowNumber) => {
            if (rowNumber > 4) {
                row.eachCell((cell, colNumber) => {
                    if (colNumber > 2) {
                        cell.alignment = { horizontal: 'right' };
                    }
                });
            }
        });
        
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
        
        worksheet.lastRow.eachCell((cell) => {
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: 'FFE6F0D0' }
            };
        });
        
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `Товарооборот_${productInfo ? productInfo.name.replace(/\s+/g, '_') : 'Все_товары'}_${new Date().toLocaleDateString().replace(/\./g, '-')}.xlsx`;
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

const selectedProductInfo = computed(() => {
    if (!selectedProduct.value) return null;
    return products.value.find(p => p.id === selectedProduct.value);
});

const filteredDays = computed(() => {
    if (activeTab.value === 'all') {
        return days.value.map(day => day.short);
    } else {
        return [activeTab.value];
    }
});

const getDayShortName = (dayObj) => {
    return dayObj.short;
};

onMounted(() => {
    loadProducts();
    loadOrders();
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', checkMobile);
});

watch(orders, updateTable);
</script>

<template>
    <AppLayout title="Товарооборот">
        <NotificationToast 
            :notifications="notifications"
            @close="closeNotification"
        />
        
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Товарооборот
            </h2>
        </template>

        <div class="py-4 sm:py-8">
            <div class="mb-4 border-b border-gray-200 dark:border-gray-700 overflow-x-auto overflow-y-hidden hide-scrollbar">
                <div class="flex flex-nowrap -mb-px text-sm font-medium text-center min-w-full pb-1">
                        <a @click="setActiveTab('all')" 
                       :class="{'text-blue-600 border-blue-600 dark:text-blue-400 dark:border-blue-400': activeTab === 'all',
                              'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 dark:text-gray-300 dark:hover:text-white dark:hover:border-gray-400': activeTab !== 'all'}"
                       class="flex-1 p-3 sm:p-4 border-b-2 rounded-t-lg cursor-pointer whitespace-nowrap">
                            Все дни
                        </a>
                        
                        <a v-for="day in days" :key="day.short" 
                           @click="setActiveTab(day.short)"
                       :class="{'text-blue-600 border-blue-600 dark:text-blue-400 dark:border-blue-400': activeTab === day.short,
                              'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 dark:text-gray-300 dark:hover:text-white dark:hover:border-gray-400': activeTab !== day.short}"
                       class="flex-1 p-3 sm:p-4 border-b-2 rounded-t-lg cursor-pointer whitespace-nowrap">
                        <span>{{ day.short }}</span>
                        <span class="hidden md:inline"> ({{ day.full }})</span>
                    </a>
                </div>
            </div>

            <div v-if="isMobile" class="mb-4">
                <select 
                    v-model="mobileView" 
                    class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                >
                    <option value="products">Список товаров</option>
                    <option value="table">Таблица данных</option>
                </select>
            </div>

            <div class="flex flex-col md:flex-row">
                <div 
                    v-if="!isMobile || mobileView === 'products'" 
                    class="w-full md:w-1/4 lg:w-1/5 pr-0 md:pr-4 pl-0 md:pl-4 mb-4 md:mb-0"
                >
                    <div class="mb-4">
                        <h3 class="text-lg font-medium mb-3 text-gray-800 dark:text-gray-200">Готовые товары</h3>
                        
                        <div class="relative mb-4">
                            <input 
                                type="text" 
                                v-model="searchQuery" 
                                class="pl-3 pr-10 py-2 w-full border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:text-white" 
                                placeholder="Поиск товара..." 
                            />
                            <button 
                                v-if="searchQuery" 
                                @click="clearSearch" 
                                class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <div v-if="selectedProductInfo" class="mb-3 p-3 bg-blue-50 dark:bg-blue-800 rounded-lg">
                            <h4 class="font-bold text-sm text-gray-800 dark:text-gray-100">Выбрано:</h4>
                            <p class="text-sm text-gray-700 dark:text-gray-200">{{ selectedProductInfo.name }}</p>
                        </div>

                        <button 
                            v-if="isMobile && selectedProduct" 
                            @click="mobileView = 'table'" 
                            class="w-full mb-3 p-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 transition-colors"
                        >
                            Показать данные
                        </button>
                    </div>
                    
                    <div v-if="isLoading" class="flex justify-center mb-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 dark:border-blue-400"></div>
                    </div>
                    
                    <div class="space-y-2 max-h-[50vh] md:max-h-[60vh] overflow-y-auto overflow-x-hidden hide-scrollbar-x pr-1">
                        <div v-if="filteredProducts.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">
                            Товары не найдены
                        </div>
                        <div v-for="product in filteredProducts" :key="product.id" 
                             @click="selectProduct(product.id)"
                             :class="{'bg-blue-100 dark:bg-blue-700 text-gray-900 dark:text-white': selectedProduct === product.id, 'text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-700': selectedProduct !== product.id}"
                             class="cursor-pointer p-2 border border-blue-300 dark:border-blue-600 rounded-md shadow-sm">
                            <h3 class="font-medium text-sm truncate" :title="product.name">{{ product.name }}</h3>
                        </div>
                    </div>
                </div>
                
                <div 
                    v-if="!isMobile || mobileView === 'table'" 
                    class="w-full md:w-3/4 lg:w-4/5"
                >
                    <button 
                        v-if="isMobile" 
                        @click="mobileView = 'products'" 
                        class="mb-3 p-2 bg-gray-200 dark:bg-gray-700 rounded-md text-gray-800 dark:text-gray-200 flex items-center hover:bg-gray-300 dark:hover:bg-gray-600"
                    >
                        <span class="mr-1">←</span> К списку товаров
                    </button>

                    <div v-if="isLoading" class="flex justify-center my-4">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 dark:border-blue-400"></div>
                    </div>
                    
                    <div v-else class="border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden shadow-lg">
                        <div class="bg-blue-600 dark:bg-blue-700 text-center p-2 font-bold text-white flex justify-between items-center">
                            <span class="flex-1"></span>
                            <span class="flex-1 text-center">{{ selectedProductInfo?.name || 'Готовый продукт' }}</span>
                            <span class="flex-1 text-right">
                                <button 
                                    @click="exportToExcel" 
                                    :disabled="exportLoading || tableData.length === 0"
                                    class="inline-flex items-center py-1 px-2 text-sm border border-transparent rounded shadow-sm text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="exportLoading" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m-9 3h14" />
                                    </svg>
                                    {{ exportLoading ? 'Экспорт...' : 'Excel' }}
                                </button>
                            </span>
                        </div>

                        <div class="overflow-x-auto overflow-y-hidden hide-scrollbar">
                            <div class="grid bg-blue-100 dark:bg-blue-900 min-w-[700px] pb-1" style="grid-template-columns: minmax(130px, 1.5fr) repeat(7, 1fr);">

                                <div class="p-2 text-center font-semibold text-gray-800 dark:text-gray-200">Изготовлено</div>
                            <div v-for="(day, index) in filteredDays" :key="index" class="p-2 text-center font-semibold text-gray-800 dark:text-gray-200 border-l border-gray-300 dark:border-gray-700">
                                {{ tableData[0]?.dailyOrdersData[day] || 0 }}
                            </div>
                                <div class="p-2 text-center font-semibold bg-blue-700 dark:bg-blue-800 text-white border-l border-gray-300 dark:border-gray-700">
                                {{ tableData[0] ? Object.values(tableData[0].dailyOrdersData || {}).reduce((sum, val) => sum + (Number(val) || 0), 0) : 0 }}
                            </div>
                        </div>

                            <div class="grid bg-gray-100 dark:bg-gray-800 min-w-[700px]" style="grid-template-columns: minmax(130px, 1.5fr) repeat(7, 1fr);">
                                <div class="p-2 text-center font-semibold text-gray-800 dark:text-gray-200 text-sm sm:text-base break-words">Номенклатура</div>
                            <div v-for="day in filteredDays" :key="day" class="p-2 text-center font-semibold text-gray-800 dark:text-gray-200 border-l border-gray-300 dark:border-gray-700">
                                {{ day }}
                            </div>
                            <div class="p-2 text-center font-semibold text-gray-800 dark:text-gray-200 border-l border-gray-300 dark:border-gray-700">Всего</div>
                        </div>

                            <div v-if="tableData.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400 min-w-[700px]">
                            Нет данных для отображения
                        </div>
                        <template v-else>
                            <div v-for="(item, index) in tableData" :key="item.id" 
                                 :class="{'bg-white dark:bg-gray-900': index % 2 === 0, 'bg-gray-50 dark:bg-gray-800': index % 2 !== 0}"
                                     class="grid border-t border-gray-300 dark:border-gray-700 min-w-[700px]" style="grid-template-columns: minmax(130px, 1.5fr) repeat(7, 1fr);">
                                <div class="p-2 truncate text-gray-700 dark:text-gray-300" :title="item.name">{{ item.name }}</div>
                                <div v-for="day in filteredDays" :key="day" class="p-2 text-center text-gray-700 dark:text-gray-300 border-l border-gray-300 dark:border-gray-700">
                                    {{ (item.dailyData[day] !== undefined ? Number(item.dailyData[day]) : 0).toFixed(Number(item.dailyData[day]) % 1 === 0 ? 0 : 1) }}
                                </div>
                                <div class="p-2 text-center text-gray-700 dark:text-gray-300 font-medium border-l border-gray-300 dark:border-gray-700">
                                    {{ (item.calculatedQuantity || 0).toFixed(item.calculatedQuantity % 1 === 0 ? 0 : 1) }}
                                </div>
                            </div>
                        </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.cursor-pointer {
    transition: all 0.3s;
}
.cursor-pointer:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: rgba(59, 130, 246, 0.05);
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar-x {
    overflow-x: hidden;
}
.hide-scrollbar-x::-webkit-scrollbar-horizontal {
    display: none;
}
@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const products = ref([]);
const cart = ref([]);
const insufficientItems = ref([]);
const orders = ref([]);
const showOrderHistory = ref(localStorage.getItem('showOrderHistory') === 'true');
const isLoading = ref(true);
const isOpenModal = ref(false);
const messageResponse = ref('');
const messageResponseColor = ref('');
const displayMode = ref('single');
const selectedStatuses = ref([]);

// Загрузка продуктов из базы данных
async function loadProducts() {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data.products.map(product => ({
            ...product,
            total_price: parseFloat(product.total_price) || 0,
            quantity: 0
        }));
    } catch (error) {
        console.error('Ошибка при загрузке продуктов:', error);
    }
}

// Загрузка истории заказов
async function loadOrders() {
    try {
        const response = await axios.get('/api/orders');
        orders.value = response.data.orders.map(order => ({
            ...order,
            showDetails: false
        }));
        restoreOrderDetailsState();
    } catch (error) {
        console.error('Ошибка при загрузке заказов:', error);
    } finally {
        isLoading.value = false;
    }
}

// Переключение режима отображения
function toggleDisplayMode() {
    if (displayMode.value === 'single') {
        displayMode.value = 'double';
    } else if (displayMode.value === 'double') {
        displayMode.value = 'compact';
    } else {
        displayMode.value = 'single';
    }
}

// Переключение истории заказов
function toggleOrderHistory() {
    showOrderHistory.value = !showOrderHistory.value;
    localStorage.setItem('showOrderHistory', showOrderHistory.value);
    if (showOrderHistory.value) {
        loadOrders();
    } else {
        saveOrderDetailsState();
    }
}

// Обработчик клика на строку заказа
function toggleOrderDetails(order) {
    orders.value.forEach(o => {
        if (o.id !== order.id) {
            o.showDetails = false;
        }
    });
    order.showDetails = !order.showDetails;
    saveOrderDetailsState();
}

// Изменение статуса заказа
function changeOrderStatus(order, newStatus) {
    axios.post(`/api/orders/${order.id}/status`, { status: newStatus })
        .then(response => {
            order.status = response.data.status;
            openModal('Статус заказа обновлен', 'mgreen');
        })
        .catch(error => {
            console.error('Ошибка при обновлении статуса заказа:', error);
            openModal('Ошибка при обновлении статуса заказа', 'mred');
        });
}

// Инициализация данных
onMounted(() => {
    loadProducts();
    if (showOrderHistory.value) {
        loadOrders();
    } else {
        isLoading.value = false;
    }
});

function saveOrderDetailsState() {
    const openOrderIds = orders.value.filter(order => order.showDetails).map(order => order.id);
    localStorage.setItem('openOrderIds', JSON.stringify(openOrderIds));
}

function restoreOrderDetailsState() {
    const openOrderIds = JSON.parse(localStorage.getItem('openOrderIds') || '[]');
    orders.value.forEach(order => {
        order.showDetails = openOrderIds.includes(order.id);
    });
}

// Размещение заказа
async function placeOrder() {
    try {
        const orderData = {
            items: cart.value,
            total: totalPrice.value,
            date: new Date().toISOString(),
            status: 1
        };
        const response = await axios.post('/api/orders', orderData);
        cart.value = [];
        products.value.forEach(product => product.quantity = 0);
        insufficientItems.value = [];
        openModal('Заказ успешно размещен', 'mgreen');
    } catch (error) {
        if (error.response && error.response.status === 400) {
            const insufficient = error.response.data.insufficient;
            insufficientItems.value = insufficient.map(item => item.id);
            let message = 'Недостаточно материалов для заказа:\n';
            insufficient.forEach(item => {
                message += `${item.name}: требуется ${item.required}, доступно ${item.available}\n`;
            });
            openModal(message, 'mred');
        } else {
            console.error('Ошибка при размещении заказа:', error);
        }
    }
}

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

// Общая стоимость заказа
const totalPrice = computed(() => {
    return cart.value.reduce((total, item) => total + item.quantity * item.total_price, 0);
});

// Увеличение количества продукта
const increaseProductQuantity = (productId) => {
    const product = products.value.find(p => p.id === productId);
    if (product) {
        product.quantity += 1;
        updateCart(product);
    }
};

// Уменьшение количества продукта
const decreaseProductQuantity = (productId) => {
    const product = products.value.find(p => p.id === productId);
    if (product && product.quantity > 0) {
        product.quantity -= 1;
        updateCart(product);
    }
};

// Вспомогательная функция для обновления корзины
const updateCart = (product) => {
    const cartItem = cart.value.find(item => item.id === product.id);
    if (product.quantity > 0) {
        if (cartItem) {
            cartItem.quantity = product.quantity;
        } else {
            cart.value.push({ ...product });
        }
    } else {
        cart.value = cart.value.filter(item => item.id !== product.id);
    }
};

// Для заказов
const increaseOrderQuantity = (orderId) => {
    const order = orders.value.find(o => o.id === orderId);
    if (!order) return;

    const parsedProducts = JSON.parse(order.products);
    parsedProducts.forEach(product => {
        product.quantity += 1;
    });

    axios.post(route('orders.update-quantity'), {
        order_id: orderId,
        products: JSON.stringify(parsedProducts)
    })
    .then(response => {
        if (response.data.success) {
            order.products = JSON.stringify(parsedProducts);
            orders.value = [...orders.value];
        }
    })
    .catch(error => {
        console.error('Ошибка при обновлении количества:', error);
    });
};

const decreaseOrderQuantity = (orderId) => {
    const order = orders.value.find(o => o.id === orderId);
    if (!order) return;

    const parsedProducts = JSON.parse(order.products);
    parsedProducts.forEach(product => {
        if (product.quantity > 0) {
            product.quantity -= 1;
        }
    });

    axios.post(route('orders.update-quantity'), {
        order_id: orderId,
        products: JSON.stringify(parsedProducts)
    })
    .then(response => {
        if (response.data.success) {
            order.products = JSON.stringify(parsedProducts);
            orders.value = [...orders.value];
        }
    })
    .catch(error => {
        console.error('Ошибка при обновлении количества:', error);
    });
};

// Форматирование даты
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

// Фильтрация заказов по статусу
const filteredOrders = computed(() => {
    if (selectedStatuses.value.length === 0) {
        return orders.value;
    }
    return orders.value.filter(order => selectedStatuses.value.includes(order.status));
});
</script>

<template>
    <AppLayout title="Заказы">
        <div class="modalMessage" :class="messageResponseColor" v-if="isOpenModal">{{ messageResponse }}</div>
        <div v-if="isLoading" class="preloader">
            <div class="loader"></div>
        </div>
        <div v-else class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex">
                <!-- Боковое меню для фильтрации -->
                <div class="w-1/4 pr-4">
                    <div class="bg-white shadow-xl sm:rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium mb-4">Фильтр по статусу</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="selectedStatuses" :value="0" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                                <span class="ml-2 text-gray-700">Отменен</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="selectedStatuses" :value="1" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                                <span class="ml-2 text-gray-700">В процессе</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="selectedStatuses" :value="2" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                                <span class="ml-2 text-gray-700">Завершен</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Основной контент -->
                <div class="w-3/4">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Управление заказами</h3>
                        <button @click="toggleOrderHistory" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            {{ showOrderHistory ? 'Вернуться к продуктам' : 'История заказов' }}
                        </button>
                    </div>

                    <div v-if="showOrderHistory" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">История заказов</h3>
                        <button @click="toggleDisplayMode" class="mb-4 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Переключить режим
                        </button>
                        <div :class="{'grid grid-cols-1': displayMode === 'single', 'grid grid-cols-2 gap-4': displayMode === 'double', 'grid grid-cols-3 gap-2': displayMode === 'compact'}">
                            <div v-for="order in filteredOrders" :key="order.id" 
                                 @click="toggleOrderDetails(order)" 
                                 class="cursor-pointer p-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-semibold">Заказ #{{ order.id }}</span>
                                        <span class="ml-2 text-sm text-gray-500">{{ formatDate(order.created_at) }}</span>
                                    </div>
                                    <span :class="{'text-green-500': order.status === 2, 'text-blue-500': order.status === 1, 'text-red-500': order.status === 0}">
                                        {{ order.status === 2 ? 'Завершен' : order.status === 1 ? 'В процессе' : 'Отменен' }}
                                    </span>
                                </div>
                                <div v-if="order.showDetails" class="mt-2">
                                    <ul>
                                        <li v-for="item in JSON.parse(order.products)" :key="item.id" class="flex justify-between items-center mb-2">
                                            <span class="font-bold">{{ item.name }} - {{ item.quantity }}</span>
                                            <span>{{ (item.quantity * item.total_price).toFixed(2) }} ₽</span>
                                        </li>
                                    </ul>
                                    <hr class="my-2">
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="font-semibold">Итого:</span>
                                        <span class="font-bold">{{ JSON.parse(order.products).reduce((total, item) => total + item.quantity * item.total_price, 0).toFixed(2) }} ₽</span>
                                    </div>
                                    <div class="mt-2">
                                        <button v-if="order.status !== 0" @click.stop="changeOrderStatus(order, 0)" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Отменить</button>
                                        <button v-if="order.status !== 1" @click.stop="changeOrderStatus(order, 1)" class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 ml-2">В процессе</button>
                                        <button v-if="order.status !== 2" @click.stop="changeOrderStatus(order, 2)" class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 ml-2">Завершить</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <!-- Список продуктов -->
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-medium mb-4">Список продуктов</h3>
                            <div v-for="product in products" :key="product.id" class="flex justify-between items-center mb-4 p-4 border-b border-gray-200">
                                <div class="text-gray-700 font-semibold">{{ product.name }} - {{ product.total_price.toFixed(2) }} ₽</div>
                                <div class="flex items-center">
                                    <button @click="decreaseProductQuantity(product.id)" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">-</button>
                                    <span class="mx-3 text-gray-700">{{ product.quantity }}</span>
                                    <button @click="increaseProductQuantity(product.id)" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Корзина заказа -->
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium mb-4">Корзина заказа</h3>
                            <div v-for="item in cart" :key="item.id" 
                                 class="flex justify-between items-center mb-2 p-2 border-b border-gray-200">
                                <div>{{ item.name }} - {{ item.quantity }} x {{ item.total_price.toFixed(2) }} ₽</div>
                                <div>{{ (item.quantity * item.total_price).toFixed(2) }} ₽</div>
                            </div>
                            <div class="flex justify-between items-center mt-4">
                                <div class="text-lg font-semibold">Итого:</div>
                                <div class="text-lg font-semibold">{{ totalPrice.toFixed(2) }} ₽</div>
                            </div>
                            <button @click="placeOrder" class="mt-6 w-full sm:w-auto inline-flex justify-center py-3 px-6 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Заказать
                            </button>
                        </div>
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
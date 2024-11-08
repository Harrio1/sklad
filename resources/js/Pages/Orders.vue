<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import axios from 'axios';

const products = ref([]); // Список продуктов
const cart = ref([]); // Корзина заказа
const insufficientItems = ref([]);

// Загрузка продуктов из базы данных
async function loadProducts() {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data.products.map(product => ({
            ...product,
            total_price: parseFloat(product.total_price) || 0, // Преобразуем total_price в число
            quantity: 0
        }));
    } catch (error) {
        console.error('Ошибка при загрузке продуктов:', error);
    }
}

// Увеличение количества продукта
function increaseQuantity(product) {
    const index = products.value.findIndex(p => p.id === product.id);
    if (index !== -1) {
        products.value[index].quantity += 1;
        updateCart(products.value[index]);
    }
}

// Уменьшение количества продукта
function decreaseQuantity(product) {
    const index = products.value.findIndex(p => p.id === product.id);
    if (index !== -1 && product.quantity > 0) {
        products.value[index].quantity -= 1;
        updateCart(products.value[index]);
    }
}

// Обновление корзины
function updateCart(product) {
    const index = cart.value.findIndex(item => item.id === product.id);
    if (product.quantity > 0) {
        if (index === -1) {
            cart.value.push({ ...product });
        } else {
            cart.value[index].quantity = product.quantity;
        }
    } else if (index !== -1) {
        cart.value.splice(index, 1);
    }
}

// Общая стоимость заказа
const totalPrice = computed(() => {
    return cart.value.reduce((total, item) => total + item.quantity * item.total_price, 0);
});

// Размещение заказа
async function placeOrder() {
    try {
        const orderData = {
            items: cart.value,
            total: totalPrice.value,
            date: new Date().toISOString(),
            status: 'Новый'
        };
        const response = await axios.post('/api/orders', orderData);
        cart.value = [];
        products.value.forEach(product => product.quantity = 0);
        insufficientItems.value = []; // Очистка списка
        alert('Заказ успешно размещен');
    } catch (error) {
        if (error.response && error.response.status === 400) {
            const insufficient = error.response.data.insufficient;
            insufficientItems.value = insufficient.map(item => item.id); // Используем идентификаторы
            let message = 'Недостаточно материалов для заказа:\n';
            insufficient.forEach(item => {
                message += `${item.name}: требуется ${item.required}, доступно ${item.available}\n`;
            });
            alert(message);
        } else {
            console.error('Ошибка при размещении заказа:', error);
        }
    }
}

// Инициализация данных
loadProducts();
</script>

<template>
    <AppLayout title="Заказы">
        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Список продуктов -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium mb-4">Список продуктов</h3>
                    <div v-for="product in products" :key="product.id" class="flex justify-between items-center mb-4 p-4 border-b border-gray-200">
                        <div class="text-gray-700 font-semibold">{{ product.name }} - {{ product.total_price.toFixed(2) }} ₽</div>
                        <div class="flex items-center">
                            <button @click="decreaseQuantity(product)" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">-</button>
                            <span class="mx-3 text-gray-700">{{ product.quantity }}</span>
                            <button @click="increaseQuantity(product)" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">+</button>
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
    </AppLayout>
</template>

<style scoped>
/* Добавьте стили, если необходимо */
</style>
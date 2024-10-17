<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive } from 'vue';
import axios from 'axios';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const form = reactive({
    supplierName: null,
    address: null,
    supplierComments: null,
    phoneNumber: null
});

let isEdit = ref(false);
let isEditId = ref(0);
let isOpenModal = ref(false);
let messageResponse = ref('');
let messageResponseColor = ref('');
let isLoaded = ref(false);

function closemessageResponse() {
    document.querySelector('.modalMessage').classList.remove('show');
    setTimeout(() => {
        isOpenModal.value = false;
        messageResponse.value = '';
        messageResponseColor.value = '';
    }, 500);
}

const suppliers = reactive({});
function getSuppliers() {
    axios.get('/get-suppliers').then((response) => {
        suppliers.value = response.data.suppliers;
        isLoaded.value = true;
    });
}
getSuppliers();

function deleteSuppliers(ids) {
    if (confirm('Вы действительно хотите удалить запись?')) {
        axios.post('/delete-suppliers', { suppliers_id: ids }).then((response) => {
            openModal(response.data.status, 'mgreen');
            getSuppliers();
        });
    }
}

function updateSuppliers(ids) {
    let b = suppliers.value.find((el) => el.id == ids);
    form.supplierName = b.name;
    form.supplierComments = b.comments;
    form.address = b.address;
    form.phoneNumber = b.phone;
    isEdit.value = true;
    isEditId.value = ids;
}

function clearSuppliers() {
    form.supplierName = '';
    form.supplierComments = '';
    form.address = '';
    form.phoneNumber = null;
}

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

function responseSuppliers() {
    axios.post('/add-suppliers', {
        csrf: csrf,
        supplierName: form.supplierName,
        address: form.address,
        supplierComments: form.supplierComments,
        phoneNumber: form.phoneNumber
    }).then((response) => {
        if (response.data.isOk) {
            clearSuppliers();
            openModal(response.data.status, 'mgreen');
            getSuppliers();
        } else {
            openModal(response.data.status, 'mred');
        }
    });
}

function updateSuppliersToServ() {
    axios.post('/update-suppliers', {
        csrf: csrf,
        supplierId: isEditId.value,
        supplierName: form.supplierName,
        address: form.address,
        supplierComments: form.supplierComments,
        phoneNumber: form.phoneNumber
    }).then((response) => {
        if (response.data.isOk) {
            clearSuppliers();
            openModal(response.data.status, 'mgreen');
            getSuppliers();
        } else {
            openModal(response.data.status, 'mred');
        }
    });
}
</script>

<template>
    <AppLayout title="Suppliers">
        <div class="modalMessage" :class="messageResponseColor" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Поставщики
            </h2>
        </template>

        <div class="py-6 sm:py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mb-6">
                    <h3 class="text-lg font-medium mb-4">Добавить нового поставщика</h3>
                    <form>
                        <input type="hidden" name="_token" :value="csrf">
                        <div class="mb-4">
                            <label for="supplierName" class="block text-sm font-medium text-gray-700">Имя поставщика</label>
                            <input type="text" id="supplierName" v-model="form.supplierName" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="mb-4">
                            <label for="phoneNumber" class="block text-sm font-medium text-gray-700">Номер телефона</label>
                            <input type="tel" id="phoneNumber" v-model="form.phoneNumber" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700">Адрес</label>
                            <textarea id="address" v-model="form.address" required
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="supplierComments" class="block text-sm font-medium text-gray-700">Комментарий</label>
                            <input type="text" id="supplierComments" v-model="form.supplierComments" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <button v-if="!isEdit" type="submit" @click.prevent="responseSuppliers()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить поставщика
                        </button>
                        <button v-else type="submit" @click.prevent="updateSuppliersToServ()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Изменить
                        </button>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5">
                    <h3 class="text-lg font-medium mb-4">Список поставщиков</h3>
                    <!-- Таблица для десктопной версии -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Имя
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Адрес
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Телефон
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Комментарий
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in suppliers.value" :key="item.id">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/150?img=1" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ item.name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    jane.cooper@example.com
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ item.address }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.phone }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.comments }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                        <a @click="updateSuppliers(item.id)" class="text-indigo-600 hover:text-indigo-900">Ред.</a>
                                        <a @click="deleteSuppliers(item.id)" class="ml-2 text-red-600 hover:text-red-900">Удал.</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Мобильное представление -->
                    <div class="sm:hidden">
                        <div v-for="item in suppliers.value" :key="item.id" class="bg-white shadow overflow-hidden sm:rounded-lg mb-4 p-4">
                            <div class="flex items-center mb-2">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/150?img=1" alt="">
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        {{ item.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        jane.cooper@example.com
                                    </p>
                                </div>
                            </div>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Адрес: {{ item.address }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Телефон: {{ item.phone }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Комментарий: {{ item.comments }}
                            </p>
                            <div class="mt-2">
                                <a @click="updateSuppliers(item.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">Редактировать</a>
                                <a @click="deleteSuppliers(item.id)" class="text-red-600 hover:text-red-900">Удалить</a>
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
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0.8;
    background-color: #dddddd;
    display: flex;
    justify-content: center;
    align-items: center;
    left: 0;
    top: 0;
}

.loader {
    position: relative;
    width: 50px;
    height: 50px;
    border-top: 5px solid #000;
    border-radius: 50%;
    animation: preload 1.5s ease 0s infinite;
}

.loader::after {
    content: '';
    width: 60px;
    height: 60px;
    border-top: 5px solid #000;
    border-radius: 50%;
    position: absolute;
    left: -10px;
    top: -10px;
    animation: preload 1.5s ease 0.5s infinite;
}

@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

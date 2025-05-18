<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, watch } from 'vue';
import NotificationToast from '@/Components/NotificationToast.vue';
import useNotifications from '@/Composables/useNotifications';
import axios from 'axios';
import IMask from 'imask';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const form = reactive({
    supplierName: null,
    address: null,
    supplierComments: null,
    phoneNumber: null
});

let isEdit = ref(false);
let isEditId = ref(0);

const { notifications, showNotification, closeNotification } = useNotifications();

let isLoaded = ref(false);
let isLoading = ref(false);

const suppliers = reactive({});

function getSuppliers() {
    isLoading.value = true;
    axios.get('/get-suppliers').then((response) => {
        suppliers.value = response.data.suppliers;
        isLoaded.value = true;
        isLoading.value = false;
    }).catch(error => {
        console.error('Ошибка при получении поставщиков:', error);
        showNotification('Ошибка при получении поставщиков', 'mred', 'Ошибка');
        isLoading.value = false;
    });
}
getSuppliers();

function deleteSuppliers(ids) {
    if (confirm('Вы действительно хотите удалить запись?')) {
        axios.post('/delete-suppliers', { supplier_id: ids }).then((response) => {
            showNotification(response.data.status, 'mgreen', 'Успех');
            getSuppliers();
        }).catch(error => {
            console.error('Ошибка при удалении поставщика:', error);
            showNotification('Ошибка при удалении поставщика', 'mred', 'Ошибка');
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
    showNotification(message, color, color === 'mgreen' ? 'Успех' : 'Ошибка');
}

function responseSuppliers() {
    if (!form.supplierName) {
        showNotification('Введите имя поставщика', 'mred', 'Ошибка');
        return;
    }
    
    axios.post('/add-suppliers', {
        csrf: csrf,
        supplierName: form.supplierName,
        address: form.address,
        supplierComments: form.supplierComments,
        phoneNumber: cleanPhoneNumber(form.phoneNumber)
    }).then((response) => {
        if (response.data.isOk) {
            clearSuppliers();
            showNotification(response.data.status, 'mgreen', 'Успех');
            getSuppliers();
        } else {
            showNotification(response.data.status, 'mred', 'Ошибка');
        }
    }).catch(error => {
        console.error('Ошибка при добавлении поставщика:', error);
        showNotification('Ошибка при добавлении поставщика', 'mred', 'Ошибка');
    });
}

function updateSuppliersToServ() {
    axios.post('/update-suppliers', {
        csrf: csrf,
        supplierId: isEditId.value,
        supplierName: form.supplierName,
        address: form.address,
        supplierComments: form.supplierComments,
        phoneNumber: cleanPhoneNumber(form.phoneNumber)
    }).then((response) => {
        if (response.data.isOk) {
            clearSuppliers();
            showNotification(response.data.status, 'mgreen', 'Успех');
            getSuppliers();
        } else {
            showNotification(response.data.status, 'mred', 'Ошибка');
        }
    });
}

function cleanPhoneNumber(phoneNumber) {
    return phoneNumber.replace(/\D/g, '').slice(0, 11);
}

const phoneNumberMask = {
  mask: '+{7}(000)000-00-00',
  lazy: false,
  definitions: {
    '_': /[0-9]/
  }
};

function onPhoneInput(e) {
  const input = e.target;
  const value = input.value.replace(/\D/g, '').slice(0, 11);
  const formattedValue = formatPhoneNumber(value);
  form.phoneNumber = formattedValue;
  
  setTimeout(() => {
    input.setSelectionRange(formattedValue.length, formattedValue.length);
  }, 0);
}

function formatPhoneNumber(value) {
  if (!value) return '';
  const match = value.match(/^(\d{1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})$/);
  if (!match) return value;
  return `+${match[1]}${match[2] ? `(${match[2]}` : ''}${match[3] ? `)${match[3]}` : ''}${match[4] ? `-${match[4]}` : ''}${match[5] ? `-${match[5]}` : ''}`;
}
</script>

<template>
    <AppLayout title="Поставщики">
        <NotificationToast 
            :notifications="notifications"
            @close="closeNotification"
        />
        
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Поставщики
            </h2>
        </template>

        <div class="py-6 sm:py-12" v-if="!isLoading">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5 mb-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Добавить нового поставщика</h3>
                    <form>
                        <input type="hidden" name="_token" :value="csrf">
                        <div class="mb-4">
                            <label for="supplierName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Имя поставщика</label>
                            <input type="text" id="supplierName" v-model="form.supplierName" required
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                        </div>

                        <div class="mb-4">
                            <label for="phoneNumber" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Номер телефона</label>
                            <input type="tel" id="phoneNumber" v-model="form.phoneNumber" @input="onPhoneInput" required
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
                        </div>

                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Адрес</label>
                            <textarea id="address" v-model="form.address" required
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="supplierComments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Комментарий</label>
                            <input type="text" id="supplierComments" v-model="form.supplierComments" required
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200" />
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

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-5">
                    <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Список поставщиков</h3>
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">
                                        Имя
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">
                                        Адрес
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">
                                        Телефон
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">
                                        Комментарий
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/6">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in suppliers.value" :key="item.id">
                                    <td class="px-3 py-2">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200 break-words">
                                                    {{ item.name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-sm text-gray-900 dark:text-gray-200 break-words">{{ item.address }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatPhoneNumber(item.phone) }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 break-words">
                                        {{ item.comments }}
                                    </td>
                                    <td class="px-3 py-2 text-sm font-medium">
                                        <a @click="updateSuppliers(item.id)" class="text-indigo-600 hover:text-indigo-900">Редактировать</a>
                                        <a @click="deleteSuppliers(item.id)" class="ml-2 text-red-600 hover:text-red-900">Удалить</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="sm:hidden">
                        <div v-for="item in suppliers.value" :key="item.id" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-4 p-4">
                            <div class="flex items-center mb-2">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200">
                                        {{ item.name }}
                                    </h3>
                                </div>
                            </div>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Адрес: {{ item.address }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                Телефон: {{ formatPhoneNumber(item.phone) }}
                            </p>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
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
        <div v-else class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 backdrop-blur-sm">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-500 dark:border-blue-400"></div>
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

.table-fixed {
    table-layout: fixed;
}

tbody td {
    vertical-align: top;
}

.break-words {
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}
</style>

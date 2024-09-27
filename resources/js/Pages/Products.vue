<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

import { ref, reactive } from 'vue'
//import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { Link } from '@inertiajs/vue3';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

const form = reactive({
    name: null,
    price: null,
})

// если редактируем
let isEdit = ref(false);
let isEditId = ref(0);
// открыто ли модальное окно 
let isOpenModal = ref(false);
// сообщение от сервера
let messageResponse = ref('');
let messageResponseColor = ref('');
// загруженны ли данные с сервера
let isLoaded = ref(false);

function closemessageResponse(){
    isOpenModal.value = false;
    messageResponse.value = '';
    messageResponseColor.value = '';
}

const products = reactive({})

function  getProducts(){
    axios({
            method: 'get',
            url: '/get-products',
        }).then((response) => {
            products.value = response.data.products;
            isLoaded.value = true;
    })
}
getProducts();


function updateTable(mes, isok){
    isEdit = false;
    isEditId = 0;
    isOpenModal.value = true;
    messageResponse.value = mes;
    if (isok) {
        messageResponseColor.value = 'mgreen';
    } else {
        messageResponseColor.value = 'mred';
    }
    
    getProducts();
    setTimeout(closemessageResponse, 2000);
}

function clearProducts(){
    form.name = '';
    form.price = '';
}

function responseProducts() {
    axios({
            method: 'post',
            url: '/add-products',
            data: {
                csrf: csrf,
                name: form.name,
                price: form.price,
        }
        }).then((response) => {
            if (response.data.isOk){
                clearProducts() 
                updateTable(response.data.status, response.data.isOk)
            } else {
                isOpenModal.value = true;
                messageResponse.value = response.data.status;
                messageResponseColor.value = 'mred';
                setTimeout(closemessageResponse, 2000);
            }
            
        })
}

function updateProductsToServ() {
     
    axios({
            method: 'post',
            url: '/update-products',
            data: {
                csrf: csrf,
                id: isEditId,
                name: form.name,
                price: form.price
        }
        }).then((response) => {
            if (response.data.isOk){
                clearProducts() 
                updateTable(response.data.status, response.data.isOk)
            } else {
                isOpenModal.value = true;
                messageResponse.value = response.data.status;
                messageResponseColor.value = 'mred';
                setTimeout(closemessageResponse, 2000);
            }
           
        })

}

function updateProducts(ids){
    let b = products.value.find((el) => el.id == ids);
    form.name = b.name;
    form.price = b.price;
    isEdit = true;
    isEditId = ids;
}

function deleteProducts(ids){
    let a = confirm('Вы действительно хотите удалить запись?');
    if (a == true) {
        axios.post('/delete-products', {
            id: ids
        }
        ).then((response) => {
            isOpenModal.value = true;
            messageResponse.value = response.data.status
            getProducts();
            setTimeout(closemessageResponse, 2000);
        })
    }
}

</script>

<template>
    <AppLayout title="Products">
        <div v-if = "!isLoaded"  class="preload">
            <div class="preload2"></div>
        </div>
        <div class="modalMessage" :class="messageResponseColor" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Продукты
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    
                    
                  
                    <h3 class="text-lg font-medium mb-4">Добавить новый продукт</h3>
                    <form>
                        <input type="hidden" name="_token" :value="csrf">
                        <div class="mb-4">
                            <label for="productName" class="block text-sm font-medium text-gray-700">Название продукта</label>
                            <input type="text" id="productName" v-model="form.name" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                
                        <div class="mb-4">
                            <label for="productPrice" class="block text-sm font-medium text-gray-700">Цена продукта</label>
                            <input type="text" id="productPrice" v-model="form.price" required
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        
                        <button v-if="!isEdit" type="submit" @click.prevent ="responseProducts()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить поставщика
                        </button>
                        <button v-else type="submit" @click.prevent="updateProductsToServ()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Изменить
                        </button>
                        
                    </form>
                    
                    

                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-4">Список поставщиков</h3>
                    



<table v-if = "isLoaded" class="min-w-full divide-y divide-gray-200 overflow-x-auto">
    <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Название
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Цена
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Действия
            </th>
        </tr>
    </thead>
    
        
 
    <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="item in products.value" :key="item.id">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                    
                    <Link :href="'/get-product-by-id/'+item.id" >
                        {{ item.name }}
                    </Link>
                    
                
                </div> 
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ item.price }}</div>
            </td>
           
            <td class="px-6 py-4 whitespace-nowrap  text-sm font-medium">
                <a @click="updateProducts(item.id)" :data="item.id" class="text-indigo-600 hover:text-indigo-900">Редактировать</a>
                <a @click="deleteProducts(item.id)" :data="item.id" class="ml-2 text-red-600 hover:text-red-900">Удалить</a>
            </td>
        </tr>
        </tbody>
        </table>   

                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style>

</style>
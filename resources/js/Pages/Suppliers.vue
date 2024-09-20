<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

import { ref } from 'vue'
import { reactive } from 'vue'
//import { useForm } from '@inertiajs/vue3'
import axios from 'axios'


let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

const form = reactive({
    supplierName: null,
    address: null,
    supplierComments: null,
    phoneNumber: null
})

let isEdit = ref(false);
let isEditId = ref(0);
let isOpenModal = ref(false);
let messageResponse = ref('');


function closemessageResponse(){
    isOpenModal.value = false;
}

const suppliers = reactive({})
function  getSuppliers(){
    axios({
            method: 'get',
            url: '/get-suppliers',
        }).then((response) => {
            suppliers.value = response.data.suppliers
    })
}
getSuppliers();


function deleteSuppliers(ids){

    let a = confirm('Вы действительно хотите удалить запись?');
    if (a == true) {
        axios.post('/delete-suppliers', {
            suppliers_id: ids
        }
        ).then((response) => {
            isOpenModal.value = true;
            messageResponse.value = response.data.status
            getSuppliers();
            setTimeout(closemessageResponse, 2000);
        })
    }

}

function updateSuppliers(ids){
    let b = suppliers.value.find((el) => el.id == ids);
    form.supplierName = b.name;
    form.supplierComments = b.comments;
    form.address = b.address;
    form.phoneNumber = b.phone;
    isEdit = true;
    isEditId = ids;
}

function updateTable(mes){
    isEdit = false;
    isEditId = 0;
    isOpenModal.value = true;
    messageResponse.value = mes;
    getSuppliers();
    form.supplierName = '';
    form.supplierComments = '';
    form.address = '';
    form.phoneNumber = null;
    setTimeout(closemessageResponse, 2000);

}


const submitForm = () => {
    if (isEdit) {
        axios({
            method: 'post',
            url: '/update-suppliers',
            data: {
                csrf: csrf,
                supplierId: isEditId,
                supplierName: form.supplierName,
                address: form.address,
                supplierComments: form.supplierComments,
                phoneNumber: form.phoneNumber
        }
        }).then((response) => {
            updateTable(response.data.status)
        })
    } else  {
        axios({
            method: 'post',
            url: '/add-suppliers',
            data: {
                csrf: csrf,
                supplierName: form.supplierName,
                address: form.address,
                supplierComments: form.supplierComments,
                phoneNumber: form.phoneNumber
        }
        }).then((response) => {
            updateTable(response.data.status)
        })
    }
};

</script>

<template>
    <AppLayout title="Suppliers">


        <div class="modalMessage" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Поставщики
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    
                    
                  
                    <h3 class="text-lg font-medium mb-4">Добавить нового поставщика</h3>
                    <form @submit.prevent="submitForm" method="POST" >
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

                        
                        <button v-if="!isEdit" type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить поставщика
                        </button>
                        <button v-else type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Изменить
                        </button>
                        
                    </form>
                    
                    

                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-4">Список поставщиков</h3>
                    


<table class="min-w-full divide-y divide-gray-200 overflow-x-auto">
    <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Имя
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Адрес
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Номер телефона
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Комментарий
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="item in suppliers.value" :key="item.id">
            <td class="px-6 py-4 whitespace-nowrap">
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
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ item.address }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.phone }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.comments }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap  text-sm font-medium">
                <a @click="updateSuppliers(item.id)" :data="item.id" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                <a @click="deleteSuppliers(item.id)" :data="item.id" class="ml-2 text-red-600 hover:text-red-900">Delete</a>
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

.modalMessage{
    position: fixed;
    top: 10%;
    border: 1px solid #ccc;
    box-shadow: 0px 0px 20px #444;
    background-color: rgb(0, 95, 13);
    padding: 20px 40px;
    color: #ccc;
}
a {
    cursor: pointer;
}




</style>
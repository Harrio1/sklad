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

let isOpenModal = ref(false);
let messageResponse = ref('');


function closemessageResponse(){
    isOpenModal.value = false;
}

const suppliers = reactive({

})


axios({
        method: 'get',
        url: '/get-suppliers',
    }).then((response) => {
        suppliers.value = response.data.suppliers
})



const submitForm = () => {
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
        isOpenModal.value = true;
        messageResponse.value = response.data.success;
        setTimeout(closemessageResponse, 2000);
    })
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

                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить поставщика
                        </button>
                       
                        
                    </form>
                    


                </div>
            </div>
        </div>
    </AppLayout>
</template>



<style>

.modalMessage{
    position: absolute;
    top: 10%;
    border: 1px solid #ccc;
    box-shadow: 0px 0px 20px #444;
    background-color: rgb(0, 95, 13);
    padding: 20px 40px;
    color: #ccc;
}




</style>
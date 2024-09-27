<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

import { ref, reactive } from 'vue'
//import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { Link } from '@inertiajs/vue3';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

const { product, nomenclatures } = defineProps({
    product: Object,
    nomenclatures: Object,
});

const form = reactive({
    product: product.id,
    nomenclature: null,
    count: null,
});





function addNomenclature(){
    console.log(form);

    // отправить
}

</script>


<template>
    <AppLayout title="Product">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    
                    <h3 class="text-lg font-medium mb-4">{{ product.name }}</h3>
                    {{ product.price }}

                    <hr>

                    <h3 class="text-lg font-medium mb-4">Добавить номенклатуру к продукту</h3>
                    <form @submit.prevent>
                        <div class="mb-4">
                            <label for="nomenclature" class="block text-sm font-medium text-gray-700">Номенклатура</label>
                            <select id="nomenclature" v-model="form.nomenclature" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="item in nomenclatures" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </select>
                            <div class="mb-4">
                                <label for="nomen_count" class="block text-sm font-medium text-gray-700">Количество</label>
                                <input type="text" id="nomen_count" v-model="form.count" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <button type="submit" @click.prevent ="addNomenclature()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Добавить номенклатуру
                            </button>
                        </div>



                    </form>


                </div>
            </div>
        </div>


    </AppLayout>
</template>
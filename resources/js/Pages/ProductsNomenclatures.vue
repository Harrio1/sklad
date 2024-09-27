<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

import { ref, reactive } from 'vue'
//import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { Link } from '@inertiajs/vue3';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')


defineProps({
    product: Object,
 });

</script>


<template>
    <AppLayout title="Product">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    
                    
                  
                    <h3 class="text-lg font-medium mb-4">Добавить новый продукт</h3>

        <div v-for="item in product" :key="item">
            {{ item }}
        </div>

        </div>
        </div>
        </div>


    </AppLayout>
</template>
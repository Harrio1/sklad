<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Welcome" />

    <div class="relative flex justify-center items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div v-if="canLogin" class="flex flex-col items-center p-6 text-center z-10">
            <img v-if="$page.props.auth.user" :src="$page.props.auth.user.profile_photo_url" alt="" class="mb-4 rounded-full w-16 h-16">

            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn">Dashboard</Link>

            <template v-else>
                <Link :href="route('login')" class="btn">Log in</Link>
                <Link v-if="canRegister" :href="route('register')" class="btn mt-4">Register</Link>
            </template>
        </div>
    </div>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    font-weight: 600;
    color: #4a5568;
    background-color: #4499eeb9;
    border-radius: 0.375rem;
    text-decoration: none;
    transition: background-color 0.3s, color 0.3s;
}

.btn:hover {
    background-color: #e2e8f0;
    color: #2d3748;
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>

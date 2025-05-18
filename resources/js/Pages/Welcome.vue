<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const isDarkMode = ref(false);

// Загружаем тему из localStorage при монтировании компонента
onMounted(() => {
    // Получаем сохраненную тему
    const savedTheme = localStorage.getItem('darkMode');
    
    // Применяем тему, если она сохранена
    if (savedTheme) {
        isDarkMode.value = savedTheme === 'true';
        applyTheme(isDarkMode.value);
    }
});

// Функция для применения темы
const applyTheme = (dark) => {
    if (dark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};
</script>

<template>
    <Head title="Система управления складом" />

    <div class="relative min-h-screen bg-gradient-to-b from-gray-100 to-blue-50 dark:from-gray-900 dark:to-gray-800 selection:bg-blue-500 selection:text-white">
        <div class="absolute top-0 left-0 right-0 h-32 bg-blue-500 dark:bg-blue-700 rounded-b-[30%] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col items-center justify-center min-h-screen px-6 py-12">
            <div class="text-center mb-6 md:mb-10">
                <div class="flex items-center justify-center mb-4">
                    <div class="flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-600 to-blue-400 dark:from-blue-700 dark:to-blue-500 rounded-xl shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-2">Система управления складом</h1>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Эффективный инструмент для контроля запасов, отслеживания поставок и управления номенклатурой
                </p>
            </div>
            
            <div v-if="canLogin" class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg max-w-md w-full mb-8 md:order-last">
                <div v-if="$page.props.auth.user" class="flex flex-col items-center">
                    <img :src="$page.props.auth.user.profile_photo_url" alt="Фото профиля" class="mb-4 rounded-full w-20 h-20 border-4 border-blue-100 dark:border-blue-900">
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Добро пожаловать, <span class="font-semibold">{{ $page.props.auth.user.name }}</span>!</p>
                    <Link :href="route('dashboard')" class="btn-primary w-full text-center">
                        Перейти в панель управления
                    </Link>
                </div>

                <template v-else>
                    <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-white mb-6">Вход в систему</h2>
                    <div class="space-y-4">
                        <Link :href="route('login')" class="btn-primary w-full text-center block">
                            Войти в аккаунт
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="btn-secondary w-full text-center block">
                            Зарегистрироваться
                        </Link>
                    </div>
                </template>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl w-full md:mb-12">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Управление номенклатурой</h2>
                    <p class="text-gray-600 dark:text-gray-300">Легко добавляйте, редактируйте и отслеживайте номенклатуру вашего склада</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Контроль поставок</h2>
                    <p class="text-gray-600 dark:text-gray-300">Управляйте поставками, поставщиками и следите за движением товаров</p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Аналитика и отчеты</h2>
                    <p class="text-gray-600 dark:text-gray-300">Получайте подробные отчеты о товарообороте и состоянии склада</p>
                </div>
            </div>
        </div>
        
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-blue-500 dark:bg-blue-700 rounded-t-[30%] opacity-20"></div>
        
        <div class="absolute bottom-0 left-0 right-0 text-center p-4 pb-6 text-sm text-gray-600 dark:text-gray-400">
            <p>© {{ new Date().getFullYear() }} taknenado СУС. Все права защищены.</p>
        </div>
    </div>
</template>

<style>
.btn-primary {
    @apply py-3 px-6 font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50;
}

.btn-secondary {
    @apply py-3 px-6 font-semibold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-gray-600 border border-blue-200 dark:border-blue-800 rounded-lg transition-all duration-300 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50;
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
    100% {
        transform: translateY(0px);
    }
}

.float-animation {
    animation: float 4s ease-in-out infinite;
}
</style>

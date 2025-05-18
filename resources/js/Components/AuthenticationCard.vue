<script setup>
import { onMounted, ref } from 'vue';

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
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg dark:text-gray-200">
            <slot />
        </div>
    </div>
</template>

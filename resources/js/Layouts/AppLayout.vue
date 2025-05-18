<script setup>
import { ref, watch, provide, onMounted, onBeforeMount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationToast from '@/Components/NotificationToast.vue';
import useNotifications from '@/Composables/useNotifications';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const isDarkMode = ref(false);

// Инициализируем useNotifications как реактивный объект
const notificationsService = useNotifications();

// Предоставляем доступ к службе уведомлений во всем приложении
provide('notifications', notificationsService);

// Функция для применения темы непосредственно к HTML элементу
const applyDarkMode = (value) => {
    // Применяем класс dark напрямую к HTML элементу
    if (value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    
    // Сохраняем выбор в localStorage
    localStorage.setItem('darkMode', value ? 'true' : 'false');
};

// Функция переключения темы
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    applyDarkMode(isDarkMode.value);
};

// Инициализируем тему при загрузке страницы
onBeforeMount(() => {
    // Проверяем localStorage при первой загрузке
    const storedTheme = localStorage.getItem('darkMode');
    
    // Устанавливаем тему на основе сохраненного значения
    if (storedTheme !== null) {
        isDarkMode.value = storedTheme === 'true';
        // Сразу применяем тему 
        applyDarkMode(isDarkMode.value);
    }
});

onMounted(() => {
    // Принудительное применение темы после полной загрузки DOM
    applyDarkMode(isDarkMode.value);
});

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title">
            <!-- Метатег для темной темы -->
            <meta name="color-scheme" :content="isDarkMode ? 'dark' : 'light'">
        </Head>

        <NotificationToast 
            :notifications="Array.isArray(notificationsService.notifications) ? notificationsService.notifications : Array.isArray(notificationsService.notifications.value) ? notificationsService.notifications.value : []"
            @close="notificationsService.closeNotification"
        />

        <Banner />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <nav>
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-gray-800 dark:to-gray-900 shadow-lg">
                    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex h-16 items-center justify-between">
                            <div class="shrink-0 flex items-center ml-2 sm:ml-8">
                                <Link :href="route('dashboard')" class="flex items-center">
                                    <div class="flex items-center justify-center w-28 sm:w-32 h-10 bg-white dark:bg-gray-700 rounded-lg shadow-md border-2 border-blue-300 dark:border-gray-600">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 sm:w-6 h-5 sm:h-6 text-blue-600 dark:text-gray-300 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <span class="text-blue-600 dark:text-gray-200 text-base sm:text-lg font-bold ml-1 sm:ml-2 mr-1 sm:mr-2">Склад</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <div class="hidden sm:flex-1 sm:flex sm:justify-center">
                                <div class="hidden space-x-4 sm:-my-px sm:flex text-sm">
                                    <NavLink :href="route('suppliers')" :active="route().current('suppliers')" class="text-white hover:text-gray-200 px-1 flex flex-col items-center justify-center text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="text-xs">Поставщики</span>
                                    </NavLink>
                                    <NavLink :href="route('nomenclature')" :active="route().current('nomenclature')" class="text-white hover:text-gray-200 px-1 flex flex-col items-center justify-center text-center border-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <span class="text-xs">Номенклатура</span>
                                    </NavLink>
                                    <NavLink :href="route('supplies')" :active="route().current('supplies')" class="text-white hover:text-gray-200 px-1 flex flex-col items-center justify-center text-center border-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                                        </svg>
                                        <span class="text-xs">Поставки</span>
                                    </NavLink>
                                    <NavLink :href="route('products')" :active="route().current('products')" class="text-white hover:text-gray-200 px-1 flex flex-col items-center justify-center text-center border-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        <span class="text-xs">Продукты</span>
                                    </NavLink>
                                    <NavLink :href="route('products_nomenclatures')" :active="route().current('products_nomenclatures')" class="text-white hover:text-gray-200 px-1 flex flex-col items-center justify-center text-center border-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span class="text-xs whitespace-normal">Номенклатура<br>продукта</span>
                                    </NavLink>
                                    <NavLink :href="route('orders')" :active="route().current('orders')" class="text-white hover:text-gray-200 px-1 flex flex-col items-center justify-center text-center border-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span class="text-xs">Заказы</span>
                                    </NavLink>
                                    <NavLink :href="route('turnover')" :active="route().current('turnover')" class="text-white hover:text-gray-200 px-1 flex flex-col items-center justify-center text-center border-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <span class="text-xs">Товарооборот</span>
                                    </NavLink>
                                </div>
                            </div>

                            <div class="flex items-center mr-2 sm:mr-8">
                                <div class="sm:hidden">
                                    <button @click="showingNavigationDropdown = ! showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-blue-600 dark:hover:bg-gray-700 focus:outline-none focus:bg-blue-600 dark:focus:bg-gray-700 transition duration-150 ease-in-out">
                                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                            <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                            <path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="mx-4 relative hidden sm:block" v-if="$page.props.jetstream.hasTeamFeatures">
                                    <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:bg-indigo-700 active:bg-indigo-700 transition ease-in-out duration-150">
                                                    {{ $page.props.auth.user.current_team.name }}

                                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <div class="bg-gray-50 dark:bg-gray-800">
                                                <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-300">
                                                    Manage Team
                                                </div>

                                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')" 
                                                    class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                                                    active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                        </svg>
                                                        Team Settings
                                                    </div>
                                                </ResponsiveNavLink>

                                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')" 
                                                    class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                                                    active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Create New Team
                                                    </div>
                                                </ResponsiveNavLink>

                                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                    <div class="border-t border-gray-200 dark:border-gray-600" />

                                                    <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-300">
                                                        Switch Teams
                                                    </div>

                                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                        <form @submit.prevent="switchToTeam(team)">
                                                            <ResponsiveNavLink as="button">
                                                                <div class="flex items-center">
                                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>

                                                                    <div>{{ team.name }}</div>
                                                                </div>
                                                            </ResponsiveNavLink>
                                                        </form>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>

                                <div class="mx-4 relative hidden sm:block">
                                    <Dropdown align="center" width="48">
                                        <template #trigger>
                                            <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                            </button>

                                            <span v-else class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:bg-indigo-700 active:bg-indigo-700 transition ease-in-out duration-150">
                                                    {{ $page.props.auth.user.name }}

                                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <div class="bg-gray-50 dark:bg-gray-800 py-1">
                                                <DropdownLink :href="route('profile.show')" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        Профиль
                                                    </div>
                                                </DropdownLink>

                                                <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <div class="flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                        </svg>
                                                        API Tokens
                                                    </div>
                                                </DropdownLink>

                                                <div class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out cursor-pointer" @click="toggleDarkMode">
                                                    <div class="flex items-center">
                                                        <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                                        </svg>
                                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                                        </svg>
                                                        {{ isDarkMode ? 'Светлая тема' : 'Темная тема' }}
                                                    </div>
                                                </div>

                                                <form @submit.prevent="logout">
                                                    <DropdownLink as="button" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <div class="flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                            </svg>
                                                            Выход
                                                        </div>
                                                    </DropdownLink>
                                                </form>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden bg-white dark:bg-gray-800 shadow-lg">
                    <div class="bg-gray-50 dark:bg-gray-800 pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('suppliers')" :active="route().current('suppliers')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Поставщики
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('nomenclature')" :active="route().current('nomenclature')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Номенклатура
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('supplies')" :active="route().current('supplies')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                                </svg>
                                Поставки
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('products')" :active="route().current('products')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Продукты
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('products_nomenclatures')" :active="route().current('products_nomenclatures')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Номенклатура продукта
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('orders')" :active="route().current('orders')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                Заказы
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('turnover')" :active="route().current('turnover')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Товарооборот
                            </div>
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="h-10 w-10 rounded-full object-cover border border-gray-300 dark:border-gray-600" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800 dark:text-white">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500 dark:text-gray-300">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')" 
                                class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                                active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Профиль
                                </div>
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')" 
                                class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                                active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    API Tokens
                                </div>
                            </ResponsiveNavLink>

                            <div class="border-l-4 border-transparent text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center px-3 py-2 text-sm cursor-pointer" @click="toggleDarkMode">
                                <div class="flex items-center">
                                    <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                    {{ isDarkMode ? 'Светлая тема' : 'Темная тема' }}
                                </div>
                            </div>

                            <form @submit.prevent="logout" class="w-full">
                                <ResponsiveNavLink as="button" 
                                    class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left border-l-4 border-transparent">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Выход
                                    </div>
                                </ResponsiveNavLink>
                            </form>

                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2" />

                                <div class="block px-4 py-2 text-xs text-gray-600 dark:text-gray-300 font-medium">
                                    Manage Team
                                </div>

                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')" 
                                    class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                                    active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')" 
                                    class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent"
                                    active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium border-l-4 border-blue-500">
                                    Create New Team
                                </ResponsiveNavLink>

                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2" />

                                    <div class="block px-4 py-2 text-xs text-gray-600 dark:text-gray-300 font-medium">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)" class="w-full">
                                            <ResponsiveNavLink as="button" 
                                                class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-500 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="bg-gray-50 dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

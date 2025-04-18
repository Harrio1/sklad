<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const theme = ref(localStorage.getItem('theme') || 'light');
const isDarkMode = ref(theme.value === 'dark');

watch(theme, (newTheme) => {
    document.body.classList.toggle('dark', newTheme === 'dark');
    localStorage.setItem('theme', newTheme);
});

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    isDarkMode.value = !isDarkMode.value;
    document.documentElement.classList.toggle('dark', isDarkMode.value);
};

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
    <div :class="{ 'dark': isDarkMode }">
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <nav>
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-gray-800 dark:to-gray-900 shadow-lg">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex">
                                <div class="shrink-0 flex items-center">
                                    <Link :href="route('dashboard')" class="text-white text-xl font-bold">
                                        Фабрика
                                    </Link>
                                </div>

                                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="text-white hover:text-gray-200">
                                        Главная
                                    </NavLink>
                                    <NavLink :href="route('suppliers')" :active="route().current('suppliers')" class="text-white hover:text-gray-200">
                                        Поставщики
                                    </NavLink>
                                    <NavLink :href="route('nomenclature')" :active="route().current('nomenclature')" class="text-white hover:text-gray-200">
                                        Номенклатура
                                    </NavLink>
                                    <NavLink :href="route('supplies')" :active="route().current('supplies')" class="text-white hover:text-gray-200">
                                        Поставки
                                    </NavLink>
                                    <NavLink :href="route('products')" :active="route().current('products')" class="text-white hover:text-gray-200">
                                        Продукты
                                    </NavLink>
                                    <NavLink :href="route('products_nomenclatures')" :active="route().current('products_nomenclatures')" class="text-white hover:text-gray-200">
                                        Номенклатура продукта
                                    </NavLink>
                                    <NavLink :href="route('orders')" :active="route().current('orders')" class="text-white hover:text-gray-200">
                                        Заказы
                                    </NavLink>
                                    <NavLink :href="route('turnover')" :active="route().current('turnover')" class="text-white hover:text-gray-200">
                                        Товарооборот
                                    </NavLink>
                                </div>
                            </div>

                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <div class="ms-3 relative">
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
                                            <div class="w-60">
                                                <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-300">
                                                    Manage Team
                                                </div>

                                                <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                                    Team Settings
                                                </DropdownLink>

                                                <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                                    Create New Team
                                                </DropdownLink>

                                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                    <div class="border-t border-gray-200 dark:border-gray-600" />

                                                    <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-300">
                                                        Switch Teams
                                                    </div>

                                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                        <form @submit.prevent="switchToTeam(team)">
                                                            <DropdownLink as="button">
                                                                <div class="flex items-center">
                                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>

                                                                    <div>{{ team.name }}</div>
                                                                </div>
                                                            </DropdownLink>
                                                        </form>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>

                                <div class="ms-3 relative">
                                    <Dropdown align="right" width="48">
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
                                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-gray-50 dark:bg-gray-800">
                                                <div class="bg-gray-50 dark:bg-gray-800 rounded-md shadow-lg overflow-hidden">
                                                    <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-300">
                                                        Настройки
                                                    </div>

                                                    <DropdownLink :href="route('profile.show')" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        Профиль
                                                    </DropdownLink>

                                                    <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        API Tokens
                                                    </DropdownLink>

                                                    <button @click="toggleTheme" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        {{ isDarkMode ? 'Светлая тема' : 'Темная тема' }}
                                                    </button>

                                                    <form @submit.prevent="logout">
                                                        <DropdownLink as="button" class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            Выход
                                                        </DropdownLink>
                                                    </form>
                                                </div>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>

                            <div class="-me-2 flex items-center sm:hidden">
                                <button class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-indigo-700 focus:outline-none focus:bg-indigo-700 focus:text-white transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                    <svg
                                        class="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="bg-gray-50 dark:bg-gray-800 pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                            Главная
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('suppliers')" :active="route().current('suppliers')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                            Поставщики
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('nomenclature')" :active="route().current('nomenclature')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                            Номенклатура
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('supplies')" :active="route().current('supplies')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                            Поставки
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('products')" :active="route().current('products')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                            Продукты
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('products_nomenclatures')" :active="route().current('products_nomenclatures')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                            Номенклатура продукта
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('orders')" :active="route().current('orders')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                            Заказы
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('turnover')" :active="route().current('turnover')" 
                            class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                            active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                            Товарооборот
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
                                class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                                active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                                Профиль
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')" 
                                class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                                active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                                API Tokens
                            </ResponsiveNavLink>
                            
                            <ResponsiveNavLink as="button" @click="toggleTheme" 
                                class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
                                <span v-if="isDarkMode" class="mr-2">☀️</span>
                                <span v-else class="mr-2">🌙</span>
                                {{ isDarkMode ? 'Светлая тема' : 'Темная тема' }}
                            </ResponsiveNavLink>

                            <form method="POST" @submit.prevent="logout" class="w-full">
                                <ResponsiveNavLink as="button" 
                                    class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left">
                                    Выход
                                </ResponsiveNavLink>
                            </form>

                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2" />

                                <div class="block px-4 py-2 text-xs text-gray-600 dark:text-gray-300 font-medium">
                                    Manage Team
                                </div>

                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')" 
                                    class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                                    active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')" 
                                    class="text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                                    active-class="text-blue-600 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/30 font-medium">
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

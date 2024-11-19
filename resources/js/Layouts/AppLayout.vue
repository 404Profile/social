<script setup>
import {ref} from 'vue';
import {Head, Link, router} from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import ToastNotification from "@/Components/ToastNotification.vue";
import AsideNavigation from "@/Components/AsideNavigation.vue";
import AsideButton from "@/Components/AsideButton.vue";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const isOpenModalMenu = ref(false);

const openModalMenu = () => {
    isOpenModalMenu.value = true;
    document.body.style.overflow = 'hidden';
}

const closeModalMenu = () => {
    isOpenModalMenu.value = false;
    document.body.style.overflow = null;
}
</script>

<template>
    <div>
        <Head :title="title"/>

        <Banner/>

        <ToastNotification/>

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50 overflow-x-clip">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto hover:text-amber-400"/>
                                </Link>
                            </div>

                            <div class="lg:hidden flex items-center pl-4">
                                <button v-if="isOpenModalMenu" class="cursor-pointer focus:outline-none"
                                        @click="closeModalMenu">
                                    <svg class="size-6 stroke-2" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>

                                <button v-else class="cursor-pointer focus:outline-none" @click="openModalMenu">
                                    <svg class="size-6 stroke-2" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center ms-6">
                            <ThemeSwitcher/>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos"
                                                class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img :alt="$page.props.auth.user.name"
                                                 :src="$page.props.auth.user.profile_photo_url"
                                                 class="h-8 w-8 rounded-full object-cover">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150"
                                                type="button">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" fill="none"
                                                     stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.5 8.25l-7.5 7.5-7.5-7.5" stroke-linecap="round"
                                                          stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Управление аккаунтом
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Профиль
                                        </DropdownLink>

                                        <div class="border-t border-gray-200 dark:border-gray-600"/>

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Выход
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}"
                     class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :active="route().current('dashboard')" :href="route('dashboard')">
                            Приборная панель
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img :alt="$page.props.auth.user.name"
                                     :src="$page.props.auth.user.profile_photo_url"
                                     class="h-10 w-10 rounded-full object-cover">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :active="route().current('profile.show')" :href="route('profile.show')">
                                Профиль
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Выход
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="max-w-8xl mx-auto px-4 sm:px-6 md:px-8">
                <!-- Left Menu -->
                <AsideNavigation/>

                <!-- Page Content -->
                <main class="lg:pl-[19.5rem] py-8">
                    <slot/>
                </main>
            </div>
        </div>

        <div v-if="isOpenModalMenu" class="lg:hidden">
            <div class="lg:hidden fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-40"
                 @click="closeModalMenu"></div>

            <div
                class="fixed z-50 inset-0 top-[4rem] right-auto w-[19rem] pb-10 pl-8 pr-6 overflow-y-auto bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <div class="py-8">
                    <ul class="-mx-2 flex flex-col gap-y-6">
                        <li>
                            <ul class="flex flex-col gap-y-2">
                                <li>
                                    <AsideButton :active="route().current('dashboard')" :href="route('dashboard')">
                                        <svg aria-hidden="true" class="size-6" data-slot="icon"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                        </svg>
                                        Приборная панель
                                    </AsideButton>
                                </li>

                                <li>
                                    <AsideButton :active="route().current('userProfile', $page.props.auth.user.id)"
                                                 :href="route('userProfile', $page.props.auth.user.id)">
                                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5"
                                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                        Мой профиль
                                    </AsideButton>
                                </li>
                            </ul>
                        </li>

                        <li class="flex flex-col gap-y-1">
                            <div class="flex items-center gap-x-3 px-2 py-2">
                                <span
                                    class="flex-1 text-sm font-medium leading-6 text-gray-500 dark:text-gray-400 select-none">
                                    Пользователи
                                </span>
                            </div>

                            <ul class="flex flex-col gap-y-2">
                                <li>
                                    <AsideButton :active="route().current('friends.index', $page.props.auth.user.id)"
                                                 :href="route('friends.index', $page.props.auth.user.id)">
                                        <svg class="size-6" fill="none" stroke="currentColor"
                                             stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                        Мои друзья
                                    </AsideButton>
                                </li>

                                <li>
                                    <AsideButton :active="route().current('friends.allUsers')"
                                                 :href="route('friends.allUsers')">
                                        <svg class="size-6" fill="none" stroke="currentColor"
                                             stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                        Все пользователи
                                    </AsideButton>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <ul class="flex flex-col gap-y-2">
                                <li class="mt-3">
                                    <AsideButton :active="route().current('threads.index')"
                                                 :href="route('threads.index')">
                                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5"
                                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                        Мессенджер
                                    </AsideButton>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</template>

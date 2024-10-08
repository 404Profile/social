<script setup>
import AsideButton from "@/Components/AsideButton.vue";
import {ref} from "vue";

const isUserAsideButtonsOpen = ref(true);
</script>

<template>
    <div>
        <aside
            class="hidden lg:block fixed z-50 inset-0 top-[4rem] right-auto w-[19rem] pb-10 pl-8 pr-6 py-8 overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700">
            <nav>
                <ul class="-mx-2 flex flex-col gap-y-2">
                    <li>
                        <AsideButton :active="route().current('dashboard')" :href="route('dashboard')">
                            <svg aria-hidden="true" class="h-6 w-6" data-slot="icon"
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
                            <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                 stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                            Мой профиль
                        </AsideButton>
                    </li>

                    <li class="flex flex-col gap-y-1">
                        <div class="flex items-center gap-x-3 px-2 py-2 cursor-pointer"
                             @click="isUserAsideButtonsOpen = !isUserAsideButtonsOpen">
                            <span
                                class="flex-1 text-sm font-medium leading-6 text-gray-500 dark:text-gray-400 select-none">
                                Пользователи
                            </span>

                            <button
                                class="relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 -m-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-amber-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-amber-500"
                                type="button">

                                <svg :class="isUserAsideButtonsOpen ? 'rotate-180' : ''" class="h-5 w-5"
                                     data-slot="icon" fill="currentColor" viewBox="0 0 20 20"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z"
                                          fill-rule="evenodd">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <ul v-show="isUserAsideButtonsOpen" class="flex flex-col gap-y-2">
                            <li>
                                <AsideButton :active="route().current('friends.index', $page.props.auth.user.id)"
                                             :href="route('friends.index', $page.props.auth.user.id)">
                                    <svg aria-hidden="true" class="h-6 w-6" data-slot="icon"
                                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </svg>
                                    Мои друзья
                                </AsideButton>
                            </li>

                            <li>
                                <AsideButton :active="route().current('friends.allUsers')"
                                             :href="route('friends.allUsers')">
                                    <svg aria-hidden="true" class="h-6 w-6" data-slot="icon"
                                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </svg>
                                    Все пользователи
                                </AsideButton>
                            </li>
                        </ul>

                    </li>
                </ul>
            </nav>
        </aside>
    </div>
</template>

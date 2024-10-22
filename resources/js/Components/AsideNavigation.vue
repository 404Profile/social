<script setup>
import AsideButton from "@/Components/AsideButton.vue";
import {ref} from "vue";

const isAsideButtonsOpen = ref(true);
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

                    <li class="flex flex-col gap-y-1">
                        <div class="flex items-center gap-x-3 px-2 py-2 cursor-pointer"
                             @click="isAsideButtonsOpen = !isAsideButtonsOpen">
                            <span
                                class="flex-1 text-sm font-medium leading-6 text-gray-500 dark:text-gray-400 select-none">
                                Пользователи
                            </span>

                            <button
                                class="relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 -m-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-amber-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-amber-500"
                                type="button">

                                <svg :class="isAsideButtonsOpen ? 'rotate-180' : ''" class="h-5 w-5"
                                     data-slot="icon" fill="currentColor" viewBox="0 0 20 20"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z"
                                          fill-rule="evenodd">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <ul v-show="isAsideButtonsOpen" class="flex flex-col gap-y-2">
                            <li>
                                <AsideButton :active="route().current('friends.index', $page.props.auth.user.id)"
                                             :href="route('friends.index', $page.props.auth.user.id)">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor"
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
                </ul>
            </nav>
        </aside>
    </div>
</template>

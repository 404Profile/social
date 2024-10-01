<script setup>
import {ref, watch, watchEffect} from 'vue';
import {usePage} from "@inertiajs/vue3";

const page = usePage();
const show = ref(false);
const type = ref('success');
const message = ref(null);

watchEffect(async () => {
    type.value = Object.keys(page.props.flash).find(key => page.props.flash?.[key] !== null) || null;
    message.value = page.props.flash?.[type.value] || null;
    show.value = true

    setTimeout(() => {
        show.value = false;
    }, 10000);
});
</script>

<template>
    <transition name="toast">
        <div v-if="show && message" class="fixed inset-0 top-14 z-40 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end">
            <div class="w-full max-w-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-800 rounded-lg shadow-lg cursor-pointer pointer-events-auto hover:-translate-1">
                <div class="relative overflow-hidden rounded-lg shadow-xs">
                    <div class="px-4 py-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 pr-0.5">
                                <div v-if="type === 'notice'">
                                    <div class="w-10 h-10">
                                        <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                </div>
                                <div v-else-if="type === 'warning'">
                                    <div class="w-10 h-10">
                                        <svg class="w-10 h-10 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    </div>
                                </div>
                                <div v-else-if="type === 'success'">
                                    <div class="w-10 h-10">
                                        <svg class="w-10 h-10 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                </div>
                                <div v-else-if="type === 'error'">
                                    <div class="w-10 h-10">
                                        <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 w-0 pl-3.5 ml-1 border-l border-gray-100">
                                <div class="text-sm font-medium leading-5 text-gray-900 dark:text-gray-100">
                                    <div v-if="type === 'notice'">
                                        <span>Уведомление</span>
                                    </div>
                                    <div v-else-if="type === 'warning'">
                                        <span>Предупреждение</span>
                                    </div>
                                    <div v-else-if="type === 'success'">
                                        <span>Успех</span>
                                    </div>
                                    <div v-else-if="type === 'error'">
                                        <span>Ошибка</span>
                                    </div>
                                </div>
                                <p class="text-sm leading-5 text-gray-500 dark:text-gray-300">
                                    {{ message }}
                                </p>
                            </div>
                            <div class="flex self-start flex-shrink-0 ml-4">
                                <button @click="show = false" class="inline-flex -mt-1 text-gray-400 transition duration-150 ease-in-out rounded-full focus:outline-none focus:text-gray-500">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 transition-all ease-out"
                         :class="{ 'bg-indigo-400' : type === 'notice', 'bg-yellow-400' : type === 'warning', 'bg-green-400' : type === 'success', 'bg-red-400' : type === 'error' }">
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active {
    transition: all 0.3s;
}

.toast-enter, .toast-leave-to {
    transform: translateY(-2px);
    opacity: 0;
}

.toast-enter-end, .toast-leave {
    transform: translateY(0);
    opacity: 1;
}
</style>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {Link, useForm, usePage} from "@inertiajs/vue3";
import {onBeforeUnmount, onMounted, ref, watch} from "vue";
import axios from "axios";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    thread: Object,
});

const loadedMessages = ref([]);
const users = ref([]);
const activeUser = ref(null);
const typingTimer = ref(null);
const messages = ref([]);
const messagesContainer = ref(null);
const isUsersTyping = ref(false);
const isUsersTypingTimer = ref(null);
const usersTyping = ref([]);
const page = usePage();
const firstFetchedMessages = ref(false);
const loadingMessages = ref(false);
const fetchedPage = ref(2);
const lastPage = ref(0);

const sendTypingEvent = () => {
    Echo.private(`chat.${props.thread.id}`).whisper("typing", {
        userID: page.props.auth.user.id,
        fullName: page.props.auth.user.fullName,
    });
};

const findUserIndex = (userID) => {
    return usersTyping.value.findIndex(user => user.userID === userID);
}

const moveUserToEnd = (index) => {
    const user = usersTyping.value.splice(index, 1)[0];
    usersTyping.value.push(user);
}

onMounted(() => {
    document.body.style.overflow = 'hidden';
    // scrollToBottom();

    Echo.private(`chat.${props.thread.id}`)
        .listen("NewChatMessageEvent", (response) => {
            messages.value.push(response.message);
        })
        .listenForWhisper("typing", (response) => {
            isUsersTyping.value = true;

            if (!usersTyping.value.length) {
                usersTyping.value.push(response);
            }

            const userIndex = findUserIndex(response.userID);

            if (userIndex === -1) {
                usersTyping.value.push(response);
            } else {
                moveUserToEnd(userIndex);
            }

            if (isUsersTypingTimer.value) {
                clearTimeout(isUsersTypingTimer.value);
            }

            isUsersTypingTimer.value = setTimeout(() => {
                usersTyping.value.splice(0, 1);
                if (!usersTyping.value.length) {
                    isUsersTyping.value = false;
                }
            }, 3000);
        });

    axios.get('/threads/' + props.thread.id + '/fetchMessages?page=1').then(response => {
        lastPage.value = response.data.last_page;
        messages.value.unshift(...response.data.data.reverse());
        scrollToBottom();
    }).finally(() => {
        firstFetchedMessages.value = true;
    });
});

watch(firstFetchedMessages, () => {
    if (firstFetchedMessages.value && !loadingMessages.value) {
        messagesContainer.value.addEventListener('scroll', () => {
            if (lastPage.value >= fetchedPage.value) {
                if (messagesContainer.value.scrollTop === 0) {
                    let oldHeight = messagesContainer.value.scrollHeight;
                    axios.get('/threads/' + props.thread.id + '/fetchMessages?page=' + fetchedPage.value).then(response => {
                        messages.value.unshift(...response.data.data.reverse());
                    }).finally(() => {
                        let newHeight = messagesContainer.value.scrollHeight;
                        fetchedPage.value++;
                        messagesContainer.value.scrollTo({
                            top: newHeight - oldHeight,
                        });
                        loadingMessages.value = false;
                    });
                }
            }
        });
    }
});

onBeforeUnmount(() => {
    document.body.style.overflow = null;
    Echo.leave(`thread.${props.thread.id}`);
});

const scrollToBottom = () => {
    setTimeout(function () {
        const scrolling = messagesContainer.value.clientHeight;
        messagesContainer.value.scrollTo({
            top: scrolling,
        });
    }, 0);
}

const form = useForm({
    body: '',
});

const filterDate = (value) => {
    if (value) {
        const date = new Date(value);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day} в ${hours}:${minutes}`;
    }
}

const sendMessage = () => {
    form.processing = true;
    axios.post(route('threads.message', props.thread.id), {
        body: form.body,
    }).then(response => {
        form.reset();
    }).catch(error => {
        form.processing = false;
        form.errors.body = error.response.data.errors.body[0];
    });
}

watch(form.errors, () => {
    setTimeout(function () {
        form.errors.body = null;
    }, 5000);
}, {deep: true});

function checkKey(event) {
    if (event.key === "Enter" && event.ctrlKey) {
        sendMessage();
    }
}
</script>

<template>
    <AppLayout
        :title="thread.type === '1' ? 'Переписка с ' + thread.titleThread : thread.type === '2' ? 'Переписка в ' + thread.titleThread : 'Переписка'">

        <div class="scroll-container">
            <div>
                <div
                    class="w-full grid grid-cols-[auto,1fr,auto] items-center justify-start gap-4 rounded-t-lg border-b border-slate-300 dark:border-slate-600 dark:bg-slate-800 bg-slate-100 px-3 h-min">
                    <div class="w-12 h-12 mr-4 relative flex flex-shrink-0 my-1">
                        <img :alt="thread.titleThread" :src="thread.imageThread"
                             class="shadow-md rounded-full w-full h-full object-cover">
                    </div>
                    <div class="w-full">
                        <div>
                            {{ thread.titleThread }}
                        </div>

                        <div v-if="thread.type === '2'">
                            123 участников
                        </div>
                    </div>

                    <Link :href="route('threads.index')"
                          class="block rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 dark:bg-gray-800">
                        <svg class="size-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor"
                             stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="m11.25 9-3 3m0 0 3 3m-3-3h7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </Link>
                </div>
            </div>

            <div id="container" ref="messagesContainer"
                 class="content border-x border-b border-slate-300 dark:border-slate-600 px-4 pt-4 relative space-y-1.5 -mb-12">
                <template v-for="(message, index) in messages" :id="index" :key="message">
                    <div
                        v-if="message.type === 101 || message.type === 102 || message.type === 103 || message.type === 104 || message.type === 200 || message.type === 201 || message.type === 202 || message.type === 301 || message.type === 302 || message.type === 400 || message.type === 401 || message.type === 402 || message.type === 403 || message.type === 404">

                        <p class="p-2 text-center text-sm text-gray-500">
                            {{ message.body }}
                        </p>
                    </div>

                    <div v-else-if="message.user_id !== $page.props.auth.user.id" class="flex flex-row justify-start">
                        <div class="w-8 h-8 relative hidden sm:flex flex-shrink-0 mr-4">
                            <img :alt="message.user.fullName"
                                 :src="message.user.profile_photo_url"
                                 class="shadow-md rounded-full w-full h-full object-cover">
                        </div>

                        <div class="text-sm text-gray-700 grid grid-flow-row gap-2">
                            <div class="flex items-center group">
                                <div class="flex flex-col">
                                    <div
                                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg inline-block rounded-bl-none bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-300 break-all">
                                        <div
                                            class="flex items-center justify-between text-xs border-b border-gray-400 space-x-4">
                                            <span>
                                                {{ message.user.fullName }}
                                            </span>

                                            <div class="flex items-center space-x-1 pb-1 relative">
                                                <button
                                                    class="rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-300 dark:hover:bg-gray-400 focus:outline-none">
                                                    <svg class="size-6" fill="none" stroke="currentColor"
                                                         stroke-width="1.5" viewBox="0 0 24 24"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"/>
                                                    </svg>
                                                </button>

                                                <div
                                                    class="absolute bg-gray-200 border-2 border-gray-300 top-5 min-w-36 rounded-lg p-1.5 z-10 ltr:origin-top-right rtl:origin-top-left end-0">
                                                    <div class="flex-col space-y-1.5 font-semibold">
                                                        <button class="block hover:text-gray-900">
                                                            <span>
                                                                Редактировать
                                                            </span>
                                                        </button>

                                                        <button class="block">
                                                            <span>
                                                                Удалить
                                                            </span>
                                                        </button>

                                                        <button class="block">
                                                            <span>
                                                                Ответить
                                                            </span>
                                                        </button>

                                                        <button class="block">
                                                            <span>
                                                                Отреагировать
                                                            </span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="message.type === 0" class="text-base">
                                            {{ message.body }}
                                        </p>

                                        <img v-else-if="message.type === 1" :src="message.fullUrl" alt="404"
                                             class="rounded-md w-[300px] h-[150px]">

                                        <div v-else-if="message.type === 2">
                                            <a>{{ message.fullUrl }}</a>
                                        </div>

                                        <audio v-else-if="message.type === 3" controls>
                                            <source :src="message.fullUrl" type="audio/x-wav">
                                        </audio>

                                        <video v-else-if="message.type === 4">
                                            <source :src="message.fullUrl">
                                        </video>

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-1">
                                                <svg class="w-4 h-4" fill="currentColor"
                                                     viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path clip-rule="evenodd"
                                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                                          fill-rule="evenodd"/>
                                                </svg>

                                                <span>{{ message.sentAt }}</span>
                                            </div>
                                            <template v-if="message.edited">
                                                <div>
                                                    <span class="pl-2">
                                                        изм. {{ filterDate(message.updated_at) }}
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    class="hidden group-hover:block flex flex-shrink-0 focus:outline-none mx-2 block rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-700 bg-gray-800 w-8 h-8 p-2"
                                    type="button">
                                    <svg class="w-full h-full fill-current" viewBox="0 0 20 20">
                                        <path
                                            d="M10.001,7.8C8.786,7.8,7.8,8.785,7.8,10s0.986,2.2,2.201,2.2S12.2,11.215,12.2,10S11.216,7.8,10.001,7.8z M3.001,7.8C1.786,7.8,0.8,8.785,0.8,10s0.986,2.2,2.201,2.2S5.2,11.214,5.2,10S4.216,7.8,3.001,7.8z M17.001,7.8 C15.786,7.8,14.8,8.785,14.8,10s0.986,2.2,2.201,2.2S19.2,11.215,19.2,10S18.216,7.8,17.001,7.8z"></path>
                                    </svg>
                                </button>
                                <button
                                    class="hidden group-hover:block flex flex-shrink-0 focus:outline-none mx-2 block rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-700 bg-gray-800 w-8 h-8 p-2"
                                    type="button">
                                    <svg class="w-full h-full fill-current" viewBox="0 0 20 20">
                                        <path
                                            d="M19,16.685c0,0-2.225-9.732-11-9.732V2.969L1,9.542l7,6.69v-4.357C12.763,11.874,16.516,12.296,19,16.685z"></path>
                                    </svg>
                                </button>
                                <button
                                    class="hidden group-hover:block flex flex-shrink-0 focus:outline-none mx-2 block rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-700 bg-gray-800 w-8 h-8 p-2"
                                    type="button">
                                    <svg class="w-full h-full fill-current" viewBox="0 0 24 24">
                                        <path
                                            d="M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-3.54-4.46a1 1 0 0 1 1.42-1.42 3 3 0 0 0 4.24 0 1 1 0 0 1 1.42 1.42 5 5 0 0 1-7.08 0zM9 11a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm6 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="message.user_id === $page.props.auth.user.id" class="flex flex-row justify-end">
                        <div class="messages text-sm text-white grid grid-flow-row gap-2">
                            <div class="flex items-center flex-row-reverse group">
                                <div class="flex flex-col">
                                    <div
                                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg inline-block rounded-br-none bg-blue-600 text-white break-all">
                                        <div class="flex items-center text-xs pb-1">
                                            <svg class="w-4 h-4" fill="currentColor"
                                                 viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path clip-rule="evenodd"
                                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                                      fill-rule="evenodd"/>
                                            </svg>
                                            <span>{{ message.sentAt }}</span>
                                        </div>
                                        <p v-if="message.type === 0" class="text-base">
                                            {{ message.body }}
                                        </p>

                                        <img v-else-if="message.type === 1" :src="message.fullUrl" alt="404"
                                             class="rounded-md h-44 w-auto">

                                        <a v-else-if="message.type === 2" :href="message.fullUrl"
                                           class="flex items-center" target="_blank">
                                            <svg class="w-5 h-5" fill="none"
                                                 stroke="currentColor" stroke-width="1.5"
                                                 viewBox="0 0 24 24"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                            <span class="pl-2 underline underline-offset-2 decoration-pink-500">
                                                {{ message.body }}
                                            </span>
                                        </a>

                                        <audio v-else-if="message.type === 3" controls>
                                            <source :src="message.fullUrl" type="audio/x-wav">
                                        </audio>

                                        <video v-else-if="message.type === 4" class="h-60" controls
                                               preload="metadata">
                                            <source :src="message.fullUrl">
                                        </video>
                                    </div>
                                </div>
                                <button
                                    class="hidden group-hover:block flex flex-shrink-0 focus:outline-none mx-2 block rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-700 bg-gray-800 w-8 h-8 p-2"
                                    type="button">
                                    <svg class="w-full h-full fill-current" fill="none"
                                         stroke="currentColor"
                                         stroke-width="3" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button
                                    class="hidden group-hover:block flex flex-shrink-0 focus:outline-none mx-2 block rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-700 bg-gray-800 w-8 h-8 p-2"
                                    type="button">
                                    <svg class="w-full h-full fill-current" viewBox="0 0 20 20">
                                        <path
                                            d="M19,16.685c0,0-2.225-9.732-11-9.732V2.969L1,9.542l7,6.69v-4.357C12.763,11.874,16.516,12.296,19,16.685z"></path>
                                    </svg>
                                </button>
                                <button
                                    class="hidden group-hover:block flex flex-shrink-0 focus:outline-none mx-2 block rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-700 bg-gray-800 w-8 h-8 p-2"
                                    type="button">
                                    <svg class="w-full h-full" fill="none" stroke="currentColor"
                                         stroke-width="3" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div ref="scrollToMe"></div>
            </div>
            <div class="sticky bottom-3 min-h-14">
                <section class="flex flex-col flex-auto">
                    <div
                        class="relative flex-none border-x border-t border-slate-300 dark:border-slate-600 pt-2">
                        <div class="flex flex-row items-center px-4 pb-2">

                            <div class="flex space-x-1 text-blue-500">
                                <button class="hover:scale-125 hover:text-blue-700">
                                    <svg class="size-6" fill="none" stroke="currentColor"
                                         stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                    </svg>
                                </button>

                                <button class="hover:scale-125 hover:text-blue-700">
                                    <svg class="size-6" fill="none" stroke="currentColor"
                                         stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="relative flex-grow">
                                <div class="min-h-6 text-sm">
                                    <template v-if="isUsersTyping">
                                        <template v-if="usersTyping.length === 2">
                                            <span v-for="(n, i) in 1">
                                                {{ usersTyping[i] }} {{ i === 1 ? '' : ', ' }}
                                            </span>
                                            <span>... печатают</span>
                                        </template>
                                        <template v-else>
                                            <template v-for="(user, index) in usersTyping" :key="user">
                                                {{ user.fullName }} {{ index === (usersTyping.length - 1) ? '' : ', ' }}
                                            </template>
                                            <span>{{ usersTyping.length === 1 ? 'печатает' : 'печатают' }}</span>
                                        </template>
                                    </template>
                                </div>

                                <textarea
                                    v-model="form.body"
                                    class="rounded-md min-h-14 max-h-20 py-2 pl-3 pr-12 w-full border border-gray-800 focus:border-gray-700 bg-gray-200 dark:bg-gray-800 focus:bg-gray-300 dark:focus:bg-gray-900 focus:outline-none text-black dark:text-gray-200 font-medium focus:shadow-md selection:bg-fuchsia-500 selection:text-white placeholder-gray-500 dark:placeholder-gray-400 transition duration-300 ease-in"
                                    placeholder="Начните писать..."
                                    type="text"
                                    @keydown="sendTypingEvent"
                                    @keyup.enter="checkKey($event)"/>

                                <div class="absolute top-0 right-4 h-full flex items-center pb-2">
                                    <button
                                        class="mr-3 inline-flex items-center flex-shrink-0 focus:outline-none p-1 hover:bg-blue-100 text-blue-500 focus:outline-none mr-0 rounded-full"
                                        type="button">
                                        <svg class="size-6 stroke-2" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button
                                class="inline-flex flex-shrink-0 items-center focus:outline-none mx-2 block text-blue-600 hover:text-blue-700 rounded-full p-2 text-blue-500 focus:outline-none mx-0"
                                type="button"
                                @click="sendMessage">
                                <svg class="w-6 h-6 fill-current" fill="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                                </svg>
                            </button>
                        </div>

                        <div v-if="form.errors.body" class="absolute -bottom-1 w-full text-center">
                            <InputError :message="form.errors.body" class="mt-2" @click="form.errors.body = null"/>
                        </div>

                    </div>

                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style>
.scroll-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
}

.content {
    flex: 1;
    overflow-y: auto;
    margin-bottom: 120px;
}

.content::-webkit-scrollbar {
    width: 5px;
    height: 4px;
}

.content::-webkit-scrollbar-button {
    width: 0;
    height: 0;
}

.content::-webkit-scrollbar-thumb {
    background: #2563eb;
    border: 0 none #ffffff;
    border-radius: 50px;
}

.content::-webkit-scrollbar-thumb:hover {
    background: #1d4ed8;
}

.content::-webkit-scrollbar-thumb:active {
    background: #1e40af;
}

.content::-webkit-scrollbar-track {
    background: #9ca3af;
    border: 0 none #ffffff;
    border-radius: 50px;
}

.content::-webkit-scrollbar-track:hover {
    background: #6b7280;
}

.content::-webkit-scrollbar-track:active {
    background: #4b5563;
}

.content::-webkit-scrollbar-corner {
    background: transparent;
}
</style>

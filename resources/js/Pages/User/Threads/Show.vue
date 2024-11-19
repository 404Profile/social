<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {Link, useForm, usePage} from "@inertiajs/vue3";
import {onBeforeUnmount, onMounted, ref, watch} from "vue";
import axios from "axios";
import InputError from "@/Components/InputError.vue";
import Options from "@/Pages/User/Threads/Options.vue";
import {onClickOutside} from '@vueuse/core'

const props = defineProps({
    thread: Object,
});

const users = ref([]);
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

    Echo.private(`chat.${props.thread.id}`)
        .listen("NewChatMessageEvent", (response) => {
            messages.value.push(response.message);
            scrollToBottom();
            console.log(response.message);
        })
        .listen("MessageUpdatedEvent", (response) => {
            const updatedMessage = response.message;

            const index = messages.value.findIndex(m => m.id === updatedMessage.id);
            if (index !== -1) {
                messages.value[index] = updatedMessage;
            }
        })
        .listen("DeleteChatMessageEvent", (response) => {
            const deletedMessage = response.message;

            const index = messages.value.findIndex(m => m.id === deletedMessage.id);
            if (index !== -1) {
                messages.value.splice(index, 1);
            }
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
    Echo.leave(`chat.${props.thread.id}`);
});

const scrollToBottom = () => {
    setTimeout(function () {
        const container = messagesContainer.value;
        const scrolling = container.scrollHeight - container.clientHeight;
        container.scrollTo({
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
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${hours}:${minutes}`;
    }
}

const filterText = (text) => {
    if (!text) {
        return '';
    }

    const maxLength = 20;

    if (text.length <= maxLength) {
        return text;
    }

    return text.slice(0, maxLength) + '...';
}

watch(form.errors, () => {
    setTimeout(function () {
        form.errors.body = null;
    }, 5000);
}, {deep: true});

const isOptionsModalOpen = ref(false);
const messageForOptionModal = ref(null);

const openOptionsModal = (message) => {
    messageForOptionModal.value = message;
    isOptionsModalOpen.value = true;
}

const closeOptionsModal = () => {
    messageForOptionModal.value = null;
    isOptionsModalOpen.value = false;
}

const isEditedMessage = ref(false);
const editedMessage = ref(null);

const editMessage = (message) => {
    isEditedMessage.value = true;
    editedMessage.value = message;
    form.body = message.body;
}

const resetEditMessage = () => {
    isEditedMessage.value = false;
    if (editedMessage.value.body === form.body) {
        form.body = '';
    }
    editedMessage.value = null;
}

const repliedMessage = ref(null);
const isRepliedMessage = ref(false);

const replyToMessage = (message) => {
    isRepliedMessage.value = true;
    repliedMessage.value = message;
}

const resetReplyMessage = () => {
    isRepliedMessage.value = false;
    repliedMessage.value = null;
}

const reactToMessage = (message) => {
    console.log(message);
}

const isOpenOptionsModal = ref(false);

const closeOptionsThreadModal = () => {
    isOpenOptionsModal.value = false;
}

const optionsModal = ref(null)

onClickOutside(optionsModal, event => closeOptionsModal());

const checkKey = (event) => {
    if (event.key === "Enter" && event.ctrlKey) {
        sendData();
    }
}

const sendData = () => {
    if (isEditedMessage.value) {
        updateMessage();
    } else if (isRepliedMessage.value) {
        sendRepliedMessage();
    } else {
        sendMessage();
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

const updateMessage = () => {
    form.processing = true;
    axios.put(route('threads.updateMessage', props.thread.id), {
        body: form.body,
        message: editedMessage.value['id'],
    }).then(response => {
        form.reset();
        resetEditMessage();
    }).catch(error => {
        form.processing = false;
        form.errors.body = error.response.data.errors.body[0];
    });
}

const sendRepliedMessage = () => {
    form.processing = true;
    axios.post(route('threads.message', props.thread.id), {
        body: form.body,
        reply_to_id: repliedMessage.value.id,
    }).then(response => {
        form.reset();
        resetReplyMessage();
    }).catch(error => {
        form.processing = false;
        form.errors.body = error.response.data.errors.body[0];
    });
}

const deleteMessage = (message) => {
    form.processing = true;
    axios.delete(route('threads.deleteMessage', message))
        .then(response => {
            form.processing = false;
        })
        .catch(error => {
            form.processing = false;
            form.errors.body = error.response.data.errors.body[0];
        });
}

const isLoadingPeople = ref(false);

const getPeople = () => {
    isLoadingPeople.value = true;

    if (props.thread.type === 3) {
        axios.get(route('threads.getPeople', props.thread.id)).then(() => {
            isLoadingPeople.value = false;
        });
    }
}
</script>

<template>
    <AppLayout
        :title="thread.type === 1 ? 'Переписка с ' + thread.titleThread : thread.type === 2 ? 'Переписка в ' + thread.titleThread : 'Переписка'">

        <div class="scroll-container">
            <div>
                <div
                    class="w-full grid grid-cols-[auto,1fr,auto] items-center justify-start gap-4 rounded-t-lg border-b border-slate-300 dark:border-slate-600 dark:bg-slate-800 bg-slate-100 px-3 h-min">
                    <div class="flex items-center w-full cursor-pointer" @click="isOpenOptionsModal = true">
                        <div class="w-12 h-12 mr-4 relative flex flex-shrink-0 my-1">
                            <img :alt="thread.titleThread" :src="thread.imageThread"
                                 class="shadow-md rounded-full w-full h-full object-cover">
                        </div>
                        <div class="w-full">
                            <div>
                                {{ thread.titleThread }}
                            </div>

                            <div v-if="thread.type === 2">
                                {{
                                    thread.participantsCount + ' ' + (thread.participantsCount === 1 ? 'участник' : thread.participantsCount >= 2 && thread.participantsCount <= 4 ? 'участника' : 'участников')
                                }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
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
            </div>

            <div id="container" ref="messagesContainer"
                 :class="isEditedMessage ? 'mb-[110px]' : 'mb-[120px]'"
                 class="content border-x border-b border-slate-300 dark:border-slate-600 px-4 pt-4 pb-4 relative space-y-1.5"
            >
                <template v-for="(message, index) in messages" :id="index" :key="message">
                    <template
                        v-if="message.type === 101 || message.type === 102 || message.type === 103 || message.type === 104 || message.type === 200 || message.type === 201 || message.type === 202 || message.type === 301 || message.type === 302">
                        <p class="p-2 text-center text-sm text-gray-500">
                            {{ message.body }}
                        </p>
                    </template>

                    <template v-else-if="message.type === 400">
                        <div
                            class="sm:flex sm:flex-row sm:justify-start w-full">
                            <Link :href="route('userProfile', message.user.id)">
                                <div class="text-sm text-gray-700 grid grid-flow-row gap-2">
                                    <div class="sm:flex sm:items-center w-full">
                                        <div class="flex flex-col sm:min-w-72">
                                            <div
                                                class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg inline-block rounded-bl-none bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-300 break-all">
                                                <div class="min-h-72">
                                                    <div>
                                                        <img :alt="message.user.fullName"
                                                             :src="message.user.profile_photo_url"
                                                             class="w-full h-auto rounded-md"/>
                                                    </div>

                                                    <div class="flex justify-center">
                                                        <span class="text-xl font-semibold">
                                                            {{ message.user.fullName }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </template>

                    <template v-else-if="message.user_id !== $page.props.auth.user.id">
                        <div
                            class="sm:flex sm:flex-row sm:justify-start w-full">
                            <div class="w-8 h-8 relative hidden sm:flex flex-shrink-0 mr-4">
                                <img :alt="message.user.fullName"
                                     :src="message.user.profile_photo_url"
                                     class="shadow-md rounded-full w-full h-full object-cover">
                            </div>

                            <div class="text-sm text-gray-700 grid grid-flow-row gap-2">
                                <div class="sm:flex sm:items-center w-full">
                                    <div class="flex flex-col sm:min-w-64">
                                        <div
                                            class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg inline-block rounded-bl-none bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-300 break-all">
                                            <div
                                                class="flex items-center justify-between text-xs border-b border-gray-400 space-x-4">
                                            <span>
                                                {{ message.user.fullName }}
                                            </span>

                                                <div class="flex items-center space-x-1 pb-1 relative">
                                                    <button
                                                        class="open-button-dropdown rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-300 dark:hover:bg-gray-400 focus:outline-none"
                                                        @click="isOptionsModalOpen ? closeOptionsModal : openOptionsModal(message)">
                                                        <svg class="size-6" fill="none" stroke="currentColor"
                                                             stroke-width="1.5" viewBox="0 0 24 24"
                                                             xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>

                                                    <template
                                                        v-if="isOptionsModalOpen && message.id === messageForOptionModal.id">

                                                        <transition
                                                            enter-active-class="transition ease-out duration-200"
                                                            enter-from-class="transform opacity-0 scale-95"
                                                            enter-to-class="transform opacity-100 scale-100"
                                                            leave-active-class="transition ease-in duration-75"
                                                            leave-from-class="transform opacity-100 scale-100"
                                                            leave-to-class="transform opacity-0 scale-95">
                                                            <div ref="optionsModal"
                                                                 class="z-50 absolute bg-gray-200 dark:bg-slate-800 border-2 border-gray-300 dark:border-slate-600 top-5 min-w-36 rounded-lg p-1.5 z-10 ltr:origin-top-right rtl:origin-top-left end-0">
                                                                <div class="flex-col space-y-1.5 font-semibold">
                                                                    <template
                                                                        v-if="message.user_id === page.props.auth.user.id || thread.authUserAdminType">
                                                                        <button
                                                                            class="block focus:outline-none hover:text-gray-900 dark:hover:text-white"
                                                                            @click="deleteMessage(message)">
                                                                        <span>
                                                                            Удалить
                                                                        </span>
                                                                        </button>
                                                                    </template>

                                                                    <button
                                                                        class="block focus:outline-none hover:text-gray-900 dark:hover:text-white"
                                                                        @click="replyToMessage(message)">
                                                                    <span>
                                                                        Ответить
                                                                    </span>
                                                                    </button>

                                                                    <button
                                                                        class="block focus:outline-none hover:text-gray-900 dark:hover:text-white"
                                                                        @click="reactToMessage(message)">
                                                                    <span>
                                                                        Отреагировать
                                                                    </span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </transition>
                                                    </template>
                                                </div>
                                            </div>

                                            <template v-if="message.reply_to_id">
                                                <template v-if="message.reply_to">
                                                    <div
                                                        class="bg-gray-300 dark:bg-slate-900 border-l-2 border-blue-500 pl-2 py-0.5">
                                                        <div>{{ message.reply_to.user.fullName }}</div>
                                                        <div>{{ message.reply_to.body }}</div>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <div
                                                        class="bg-gray-300 dark:bg-slate-900 border-l-2 border-blue-500 pl-2 py-0.5">
                                                        <p>Сообщение удалено</p>
                                                    </div>
                                                </template>
                                            </template>

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
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="message.user_id === $page.props.auth.user.id">
                        <div
                            class="sm:flex sm:flex-row sm:justify-end w-full">
                            <div class="messages text-sm text-white grid grid-flow-row gap-2">
                                <div class="sm:flex sm:items-center sm:flex-row-reverse w-full">
                                    <div class="flex flex-col sm:min-w-64">
                                        <div
                                            class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg inline-block rounded-br-none bg-blue-600 text-white break-all">
                                            <div
                                                class="flex items-center justify-between text-xs border-b border-gray-400 space-x-4">
                                                <span>{{ message.user.fullName }}</span>
                                                <div class="flex items-center space-x-1 pb-1 relative">
                                                    <button
                                                        class="open-button-dropdown rounded-full hover:bg-blue-700 dark:hover:bg-blue-700 focus:outline-none"
                                                        @click="isOptionsModalOpen ? closeOptionsModal : openOptionsModal(message)">
                                                        <svg class="size-6" fill="none" stroke="currentColor"
                                                             stroke-width="1.5" viewBox="0 0 24 24"
                                                             xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>

                                                    <template
                                                        v-if="isOptionsModalOpen && message.id === messageForOptionModal.id">

                                                        <transition
                                                            enter-active-class="transition ease-out duration-200"
                                                            enter-from-class="transform opacity-0 scale-95"
                                                            enter-to-class="transform opacity-100 scale-100"
                                                            leave-active-class="transition ease-in duration-75"
                                                            leave-from-class="transform opacity-100 scale-100"
                                                            leave-to-class="transform opacity-0 scale-95">
                                                            <div ref="optionsModal"
                                                                 class="z-50 absolute bg-gray-200 dark:bg-slate-800 border-2 border-gray-300 dark:border-slate-600 top-5 min-w-36 rounded-lg p-1.5 z-10 ltr:origin-top-right rtl:origin-top-left end-0">
                                                                <div class="flex-col space-y-1.5 font-semibold">
                                                                    <button
                                                                        class="block focus:outline-none hover:text-gray-900 dark:hover:text-white"
                                                                        @click="editMessage(message)">
                                                                    <span>
                                                                        Редактировать
                                                                    </span>
                                                                    </button>

                                                                    <button
                                                                        class="block focus:outline-none hover:text-gray-900 dark:hover:text-white"
                                                                        @click="deleteMessage(message)">
                                                                    <span>
                                                                        Удалить
                                                                    </span>
                                                                    </button>

                                                                    <button
                                                                        class="block focus:outline-none hover:text-gray-900 dark:hover:text-white"
                                                                        @click="replyToMessage(message)">
                                                                    <span>
                                                                        Ответить
                                                                    </span>
                                                                    </button>

                                                                    <button
                                                                        class="block focus:outline-none hover:text-gray-900 dark:hover:text-white"
                                                                        @click="reactToMessage(message)">
                                                                    <span>
                                                                        Отреагировать
                                                                    </span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </transition>
                                                    </template>
                                                </div>
                                            </div>

                                            <template v-if="message.reply_to_id">
                                                <template v-if="message.reply_to">
                                                    <div class="bg-blue-700 border-l-2 border-blue-900 pl-2 py-0.5">
                                                        <div>{{ message.reply_to.user.fullName }}</div>
                                                        <div>{{ message.reply_to.body }}</div>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <div class="bg-blue-700 border-l-2 border-blue-900 pl-2 py-0.5">
                                                        <p>Сообщение удалено</p>
                                                    </div>
                                                </template>
                                            </template>

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

                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-1">
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
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
            </div>

            <template v-if="isEditedMessage">
                <div class="sticky bottom-[125px]">
                    <div class="my-2 flex items-center">
                        <button @click="resetEditMessage">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <span>
                            Ред. сообщение:
                            {{
                                editedMessage.body.length > 10 ? editedMessage.body.substring(0, 10) + '...' : editedMessage.body
                            }}
                        </span>
                    </div>
                </div>
            </template>

            <template v-if="isRepliedMessage">
                <div class="sticky bottom-[125px]">
                    <div class="my-2 flex items-center">
                        <button @click="resetReplyMessage">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <span>
                            Ответ на:
                            {{
                                repliedMessage.body.length > 10 ? repliedMessage.body.substring(0, 10) + '...' : repliedMessage.body
                            }}
                        </span>
                    </div>
                </div>
            </template>

            <template v-if="thread.type === 1 || thread.type === 2">
                <div class="sticky bottom-3 min-h-14">
                    <section class="flex flex-col flex-auto">
                        <div
                            class="relative flex-none border-x border-t border-slate-300 dark:border-slate-600 pt-2">
                            <div class="flex flex-row items-center px-4 pb-2">

                                <template v-if="1 !== 1">
                                    <div class="flex space-x-1 text-blue-500">
                                        <button class="hover:scale-125 hover:text-blue-700">
                                            <svg class="size-6" fill="none" stroke="currentColor"
                                                 stroke-width="1.5" viewBox="0 0 24 24"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                        </button>

                                        <button class="hover:scale-125 hover:text-blue-700">
                                            <svg class="size-6" fill="none" stroke="currentColor"
                                                 stroke-width="1.5" viewBox="0 0 24 24"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>

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
                                                    {{ user.fullName }}
                                                    {{ index === (usersTyping.length - 1) ? '' : ', ' }}
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
                                    @click="sendData">
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
            </template>

            <template v-else-if="thread.type === 3">
                <div class="sticky bottom-3 min-h-14">
                    <section class="flex flex-col flex-auto">
                        <div
                            class="relative flex-none border-x border-t border-slate-300 dark:border-slate-600 pt-2">
                            <div class="flex flex-row items-center px-4 pb-2">
                                <button
                                    :disabled="isLoadingPeople"
                                    class="disables:cursor-not-allowed inline-flex flex-shrink-0 items-center focus:outline-none mx-2 block text-blue-600 hover:text-blue-700 rounded-full p-2 text-blue-500 focus:outline-none mx-0"
                                    type="button"
                                    @click="getPeople">
                                    <svg class="w-6 h-6 fill-current" fill="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                                    </svg>
                                </button>
                            </div>

                        </div>

                    </section>
                </div>
            </template>
        </div>

        <template v-if="isOpenOptionsModal">
            <Options :thread="thread" @close="closeOptionsThreadModal"/>
        </template>

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
    //margin-bottom: 120px;
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

<script setup>
import {Link, router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref} from "vue";
import ActionMessage from "@/Components/ActionMessage.vue";
import axios from "axios";

const props = defineProps({
    thread: Object,
});

const form = useForm({
    title: props.thread.title,
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

const newTitleThread = ref('');

const updateThread = () => {
    form.processing = true;
    form.put(route('threads.update', props.thread.id), {
        preserveScroll: true,
        onSuccess: () => {
            newTitleThread.value = form.title;
            form.processing = false;
        },
        onError: () => {
            form.processing = false;
        },
    });
}

const leaveGroup = () => {
    router.post(route('threads.leaveParticipant', props.thread.id), {
        preserveScroll: true,
    });
}

const isOpenAddFriendModal = ref(false);
const friends = ref([]);
const isLoadingFriends = ref(false);

const openAddFriendModal = () => {
    isOpenAddFriendModal.value = true;
    isLoadingFriends.value = true;

    axios.get(route('threads.fetchFriends', props.thread.id)).then(response => {
        friends.value = response.data;
    }).finally(() => {
        isLoadingFriends.value = false;
    });
}

const closeAddFriendModal = () => {
    isOpenAddFriendModal.value = false;
    friends.value = [];
}

const addParticipant = (user) => {
    axios.post(route('threads.addFriendsToThread', props.thread.id), {
        userID: user.id,
    });

    closeAddFriendModal();
}
</script>

<template>
    <div>
        <div class="relative z-50">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full justify-center p-4 text-center items-center">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl h-full min-h-[500px]">
                        <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="w-full">
                                <div>
                                    <div class="flex items-center w-full">
                                        <div class="w-16 h-16 mr-4 relative flex flex-shrink-0 my-1">
                                            <img :alt="thread.titleThread" :src="thread.imageThread"
                                                 class="shadow-md rounded-full w-full h-full object-cover">
                                        </div>
                                        <div class="w-full">
                                            <div>
                                                <div v-if="thread.authUserAdminType">
                                                    <input id="title"
                                                           v-model="form.title"
                                                           class="border-gray-300 dark:border-gray-500 bg-transparent dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block w-full sm:w-3/4"
                                                           required
                                                           type="text">
                                                    <InputError :message="form.errors.title" class="mt-2"/>
                                                </div>

                                                <p v-else>{{ thread.titleThread }}</p>
                                            </div>

                                            <div v-if="thread.type === '2'">
                                                <span class="dark:text-gray-400">
                                                    {{
                                                        thread.participantsCount + ' ' + (thread.participantsCount === 1 ? 'участник' : thread.participantsCount >= 2 && thread.participantsCount <= 4 ? 'участника' : 'участников')
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <button
                                                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:scale-110 focus:outline-none"
                                                @click="close">
                                                <svg class="size-7 stroke-2" fill="none" stroke="currentColor"
                                                     stroke-width="1.5" viewBox="0 0 24 24"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <template v-if="thread.titleThread !== form.title && form.title !== newTitleThread">
                                        <div class="flex items-center space-x-1 mt-2">
                                            <PrimaryButton :class="{ 'opacity-25': form.processing }"
                                                           :disabled="form.processing" @click="updateThread">
                                                Сохранить
                                            </PrimaryButton>
                                        </div>
                                    </template>

                                    <ActionMessage :on="form.recentlySuccessful" class="me-3 mt-2">
                                        Сохранено.
                                    </ActionMessage>

                                    <div class="mt-2">
                                        <button @click="openAddFriendModal">
                                            <span>Добавить друзей в беседу</span>
                                        </button>
                                    </div>

                                    <div class="mt-2">
                                        <template v-for="user in thread.users">
                                            <div
                                                class="flex justify-between py-5">
                                                <div class="flex min-w-0 gap-x-4">
                                                    <Link :href="route('userProfile', user.id)">
                                                        <div class="flex items-center gap-x-4">
                                                            <img :alt="user.fullName"
                                                                 :src="user.profile_photo_url"
                                                                 class="h-12 w-12 flex-none rounded-full bg-gray-50">
                                                            <div class="min-w-0 flex-auto">
                                                                <p class="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-300">
                                                                    {{ user.fullName }}
                                                                </p>
                                                                <p class="mt-1 truncate text-xs leading-5 text-gray-500 dark:text-gray-400">
                                                                    {{ user.email }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </Link>

                                                    <template v-if="user.pivot.admin === 2">
                                                        <div class="flex items-center">
                                                            <svg class="size-6" fill="none"
                                                                 stroke="currentColor" stroke-width="1.5"
                                                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                                <path
                                                                    d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                </div>

                                                <template v-if="thread.authUserAdminType">
                                                    <template v-if="$page.props.auth.user.id !== user.id">
                                                        <div class="flex items-end">
                                                            <button
                                                                class="cursor-pointer hover:scale-110 focus:outline-none">
                                                                <svg class="size-6" fill="none"
                                                                     stroke="currentColor" stroke-width="1.5"
                                                                     viewBox="0 0 24 24"
                                                                     xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"/>
                                                                    <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                                                          stroke-linecap="round"
                                                                          stroke-linejoin="round"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </template>

                                                <template v-if="user.id === $page.props.auth.user.id">
                                                    <div class="flex items-center">
                                                        <button
                                                            class="cursor-pointer hover:scale-110 focus:outline-none"
                                                            @click="leaveGroup">
                                                            <svg class="size-6" fill="none"
                                                                 stroke="currentColor" stroke-width="1.5"
                                                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template v-if="isOpenAddFriendModal">
        <div class="fixed inset-0 z-[60] bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <div class="fixed z-[60] inset-0 w-screen">
            <div class="flex min-h-full justify-center p-4 text-center items-center">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl h-full min-h-[500px]">
                    <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4 w-full sm:max-w-4xl h-full min-h-[500px]">
                        <p>Ваши друзья</p>

                        <template v-for="friend in friends" :key="friend">
                            <div class="flex justify-between py-5">
                                <div class="flex min-w-0 gap-x-4">
                                    <div class="flex items-center gap-x-4">
                                        <img :alt="friend.fullName"
                                             :src="friend.profile_photo_url"
                                             class="h-12 w-12 flex-none rounded-full bg-gray-50">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-300">
                                                {{ friend.fullName }}
                                            </p>
                                            <p class="mt-1 truncate text-xs leading-5 text-gray-500 dark:text-gray-400">
                                                {{ friend.email }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <button class="cursor-pointer hover:scale-110 focus:outline-none"
                                            @click="addParticipant(friend)">
                                        <svg class="size-6" fill="none" stroke="currentColor"
                                             stroke-width="1.5" viewBox="0 0 24 24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>
            </div>
        </div>
    </template>

</template>

<script setup>
import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import axios from "axios";
import Like from "@/Pages/User/Profile/Like.vue";
import Comment from "@/Pages/User/Profile/Comment.vue";

const props = defineProps({
    user: Object,
    media: Object,
    counterMedia: Number,
});

const form = useForm({
    body: '',
});

const isOpenFullMedia = ref(false);
const currentElement = ref([]);
const currentIndex = ref(0);

const openFullMedia = (element, index) => {
    isOpenFullMedia.value = true;
    currentElement.value = element;
    currentIndex.value = index;
    document.body.style.overflow = "hidden";
}

const closeFullMedia = () => {
    isOpenFullMedia.value = false;
    currentElement.value = [];
    currentIndex.value = null;
    document.body.style.overflow = null;
}

const nextMedia = () => {
    if (currentIndex.value === props.counterMedia - 1) {
        currentIndex.value = 0;
    } else {
        currentIndex.value += 1;
    }
    getNewMedia();
}

const backMedia = () => {
    if (currentIndex.value === 0) {
        currentIndex.value = props.counterMedia - 1;
    } else {
        currentIndex.value -= 1;
    }
    getNewMedia();
}

const getNewMedia = () => {
    axios.post(route('getMedia'), {
        index: currentIndex.value,
        user: props.user,
    }).then(response => {
        currentElement.value = response.data.newMedia;
    })
}
</script>

<template>
    <div class="py-3">
        <div class="mx-auto max-w-2xl lg:max-w-7xl">

            <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">

                <template v-for="(element, index) in media" :key="element">
                    <div class="group flex items-center cursor-pointer" @click="openFullMedia(element, index)">
                        <div
                            class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-lg bg-gray-200 xl:aspect-h-8 xl:aspect-w-7 shadow dark:shadow-0">
                            <img
                                :src="element.fullUrl"
                                alt="404"
                                class="h-full w-full object-cover object-center group-hover:opacity-90">
                        </div>
                    </div>
                </template>

            </div>
        </div>

        <template v-if="isOpenFullMedia">
            <div class="fixed z-50 inset-0 bg-gray-100 dark:bg-gray-700">
                <div class="fixed top-4 left-4">
                    <button
                        class="bg-amber-500 px-2 py-1 text-white font-semibold text-sm rounded block text-center sm:inline-block block"
                        @click="closeFullMedia">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 h-full overflow-y-auto md:overflow-hidden">
                    <div class="md:col-span-2 flex items-center justify-center">

                        <div class="flex">
                            <div class="flex w-min items-center px-1 cursor-pointer dark:text-gray-300">
                                <button class="h-full focus:outline-none" @click="backMedia">
                                    <svg class="size-6 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M21 16.811c0 .864-.933 1.406-1.683.977l-7.108-4.061a1.125 1.125 0 0 1 0-1.954l7.108-4.061A1.125 1.125 0 0 1 21 8.689v8.122ZM11.25 16.811c0 .864-.933 1.406-1.683.977l-7.108-4.061a1.125 1.125 0 0 1 0-1.954l7.108-4.061a1.125 1.125 0 0 1 1.683.977v8.122Z"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex-initial w-full">
                                <img :src="currentElement.fullUrl"
                                     alt="404"
                                     class="w-full max-w-full min-w-full pt-4 md:pt-0">
                            </div>
                            <div class="flex w-min items-center px-1 cursor-pointer dark:text-gray-300">
                                <button class="h-full focus:outline-none" @click="nextMedia">
                                    <svg class="size-6 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M3 8.689c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 0 1 0 1.954l-7.108 4.061A1.125 1.125 0 0 1 3 16.811V8.69ZM12.75 8.689c0-.864.933-1.406 1.683-.977l7.108 4.061a1.125 1.125 0 0 1 0 1.954l-7.108 4.061a1.125 1.125 0 0 1-1.683-.977V8.69Z"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>

                    <div class="m-4 relative">
                        <div class="w-full border-b-2 border-gray-300 dark:border-gray-500">
                            <div class="inline-flex">
                                <div class="w-10 h-10 mb-2">
                                    <img
                                        :src="currentElement.post.user.profile_photo_url"
                                        alt="404"
                                        class="w-full h-full rounded-full">
                                </div>
                                <div class="flex items-center pl-4 dark:text-gray-300">
                                    {{ currentElement.post.user.fullName }}
                                </div>
                            </div>

                            <div class="mb-2 pl-2 flex">
                                <Like :media="currentElement"/>

                                <div
                                    class="flex mr-2 text-gray-700 dark:text-gray-300 text-sm mr-4">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path
                                            d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2">
                                        </path>
                                    </svg>
                                    <span>{{ currentElement.comments?.length }}</span>
                                </div>
                            </div>
                        </div>

                        <Comment :media="currentElement"/>

                    </div>
                </div>
            </div>
        </template>

    </div>
</template>

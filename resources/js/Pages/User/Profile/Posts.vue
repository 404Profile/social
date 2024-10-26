<script setup>

import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import Like from "@/Pages/User/Profile/Like.vue";
import Comment from "@/Pages/User/Profile/Comment.vue";

const props = defineProps({
    posts: Object,
});

const form = useForm({
    body: '',
});

const isOpenFullMedia = ref(false);
const currentElement = ref([]);
const currentIndex = ref(null);
const mediaLength = ref(null);
const currentPostMedia = ref([]);

const openFullMedia = (post, index) => {
    currentPostMedia.value = post;
    mediaLength.value = currentPostMedia.value.media.length;
    isOpenFullMedia.value = true;
    currentIndex.value = index;
    currentElement.value = currentPostMedia.value.media[currentIndex.value];
    document.body.style.overflow = "hidden";
}

const closeFullMedia = () => {
    currentPostMedia.value = null;
    mediaLength.value = null;
    isOpenFullMedia.value = false;
    currentElement.value = [];
    currentIndex.value = null;
    if (!isOpenFullPost.value) {
        document.body.style.overflow = null;
    }
}

const nextMedia = () => {
    if (currentIndex.value + 1 === mediaLength.value) {
        currentIndex.value = 0;
    } else {
        currentIndex.value += 1;
    }

    currentElement.value = currentPostMedia.value.media[currentIndex.value];
}

const backMedia = () => {
    if (currentIndex.value === 0) {
        currentIndex.value = (mediaLength.value - 1);
    } else {
        currentIndex.value -= 1;
    }

    currentElement.value = currentPostMedia.value.media[currentIndex.value];
}

const isOpenFullPost = ref(false);
const currentPost = ref([]);

const openFullPost = (post) => {
    isOpenFullPost.value = true;
    currentPost.value = post;
    document.body.style.overflow = 'hidden';
}

const closeFullPost = () => {
    isOpenFullPost.value = false;
    currentPost.value = [];
    document.body.style.overflow = null;
}

const clickedPost = (post) => {
    const clickedElement = event.target.closest('[data-page]');
    if (clickedElement) {
        const page = clickedElement.getAttribute('data-page');
        if (page !== 'like') {
            const pageParts = page.split('-');
            if (pageParts[0] === 'media' && !isNaN(parseInt(pageParts[1]))) {
                const index = parseInt(pageParts[1]);
                openFullMedia(post, index);
            } else {
                openFullPost(post);
            }
        }
    }
}
</script>

<template>
    <div>
        <template v-for="post in posts" :key="post">
            <div class="relative bg-slate-50 rounded-xl overflow-hidden dark:bg-slate-800/25 max-w-3xl mx-auto mb-6"
                 data-page="post" @click="clickedPost(post)">
                <div class="relative rounded-xl overflow-auto p-8">
                    <div class="flex w-full">
                        <div>
                            <img :alt="post.user.fullName"
                                 :src="post.user.profile_photo_url"
                                 :title="post.user.fullName"
                                 class="w-12 h-12 rounded-full object-cover mr-4 shadow">
                        </div>
                        <div class="w-full">
                            <div>
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-300 -mt-1">
                                        {{ post.user.fullName }}
                                    </h2>
                                    <small class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ post.timeAgo }}
                                    </small>
                                </div>

                                <p class="mt-3 text-gray-700 dark:text-gray-300 text-sm">
                                    {{ post.body }}
                                </p>
                            </div>

                            <template v-if="post.media?.length">
                                <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <div v-for="(element, index) in post.media" :key="element"
                                         class="flex items-center">
                                        <div class="py-2 w-auto h-auto">
                                            <div :data-page="'media-' + index"
                                                 class="max-w-96 hover:opacity-80 cursor-pointer">
                                                <img :alt="'Изображение ' + post.user.fullName" :src="element.fullUrl"
                                                     class="px-1 h-full w-full"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="dark:text-gray-400">
                                    Медиафайлов: {{ post.media.length }}
                                </div>
                            </template>

                            <div class="mt-4 flex items-center">
                                <div data-page="like">
                                    <Like :post="post"/>
                                </div>

                                <div class="flex mr-2 text-gray-700 dark:text-gray-300 text-sm mr-4">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"/>
                                    </svg>
                                    <span>{{ post.comments?.length }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="absolute inset-0 pointer-events-none border border-black/5 rounded-xl dark:border-white/5"></div>
            </div>
        </template>

        <template v-if="isOpenFullMedia">
            <div class="fixed z-50 inset-0 bg-gray-100 dark:bg-gray-700">
                <div class="fixed top-4 left-4">
                    <button
                        class="bg-amber-500 px-2 py-1 text-white font-semibold text-sm rounded block text-center sm:inline-block block"
                        @click="closeFullMedia">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div
                    class="grid grid-cols-1 md:grid-cols-3 h-full overflow-y-auto md:overflow-hidden">
                    <div class="md:col-span-2 flex items-center justify-center">

                        <div class="flex">
                            <div
                                class="flex w-min items-center px-1 cursor-pointer dark:text-gray-300">
                                <button class="h-full focus:outline-none" @click="backMedia">
                                    <svg class="size-6 stroke-2" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24"
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
                            <div
                                class="flex w-min items-center px-1 cursor-pointer dark:text-gray-300">
                                <button class="h-full focus:outline-none" @click="nextMedia">
                                    <svg class="size-6 stroke-2" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24"
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
                        <div
                            class="w-full border-b-2 border-gray-300 dark:border-gray-500">
                            <div class="inline-flex">
                                <div class="w-10 h-10 mb-2">
                                    <img
                                        :src="currentPostMedia.user.profile_photo_url"
                                        alt="404"
                                        class="w-full h-full rounded-full">
                                </div>
                                <div class="flex items-center pl-4 dark:text-gray-300">
                                    {{ currentPostMedia.user.fullName }}
                                </div>
                            </div>

                            <div class="mb-2 pl-2 flex">
                                <Like :media="currentPostMedia.media[0]"/>

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

        <template v-if="isOpenFullPost && !isOpenFullMedia">
            <div class="fixed z-50 inset-0 bg-gray-100 dark:bg-gray-700 h-screen w-screen pt-5 overflow-y-auto">
                <div class="fixed top-4 left-4 z-50">
                    <button
                        class="bg-amber-500 px-2 py-1 text-white font-semibold text-sm rounded block text-center sm:inline-block block"
                        @click="closeFullPost">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>

                <div
                    class="relative bg-slate-50 rounded-xl overflow-hidden dark:bg-slate-800/25 max-w-7xl mx-auto mb-6"
                    data-page="post">
                    <div class="relative rounded-xl overflow-auto p-8">
                        <div class="flex w-full">
                            <div>
                                <img :alt="currentPost.user.fullName"
                                     :src="currentPost.user.profile_photo_url"
                                     :title="currentPost.user.fullName"
                                     class="w-12 h-12 rounded-full object-cover mr-4 shadow">
                            </div>
                            <div class="w-full">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-300 -mt-1">
                                            {{ currentPost.user.fullName }}
                                        </h2>
                                        <small class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ currentPost.timeAgo }}
                                        </small>
                                    </div>
                                    <p class="mt-3 text-gray-700 dark:text-gray-300 text-sm">
                                        {{ currentPost.body }}
                                    </p>
                                </div>

                                <template v-if="currentPost.media?.length">
                                    <div
                                        class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                        <div v-for="(element, index) in currentPost.media" :key="element"
                                             class="flex items-center">
                                            <div class="py-2 w-auto h-auto">
                                                <div :data-page="'media-' + index"
                                                     class="max-w-96 hover:opacity-80 cursor-pointer"
                                                     @click="openFullMedia(currentPost, index)">
                                                    <img :alt="'Изображение ' + currentPost.user.fullName"
                                                         :src="element.fullUrl"
                                                         class="px-1 h-full w-full"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="dark:text-gray-400">
                                        Медиафайлов: {{ currentPost.media.length }}
                                    </div>
                                </template>

                                <div class="mt-4 flex items-center">
                                    <Like :post="currentPost"/>

                                    <div class="flex mr-2 text-gray-700 dark:text-gray-300 text-sm mr-4">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path
                                                d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"/>
                                        </svg>
                                        <span>{{ currentPost.comments?.length }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 pointer-events-none border border-black/5 rounded-xl dark:border-white/5"></div>
                </div>

                <Comment :post="currentPost"/>

            </div>
        </template>

    </div>
</template>

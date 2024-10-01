<script setup>
import {ref} from "vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import axios from "axios";

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

const storeLikedMedia = (media) => {
    axios.post(route('liked'), {
        media: media,
    }).then(response => {
        const updatedMedia = props.media.find(m => m.id === media.id);

        if (updatedMedia) {
            updatedMedia.liked = response.data.liked;
            updatedMedia.isUserLikedMedia = response.data.isUserLikedMedia;
        }
    })
}

const storeLikedComment = (currentElement, comment) => {
    axios.post(route('liked'), {
        comment: comment,
    }).then(response => {
        const updatedComment = currentElement.comments.find(c => c.id === comment.id);

        if (updatedComment) {
            updatedComment.liked = response.data.liked;
            updatedComment.isUserLikedComment = response.data.isUserLikedComment;
        }
    })
}

const getNewMedia = () => {
    axios.post(route('getMedia'), {
        index: currentIndex.value,
        user: props.user,
    }).then(response => {
        currentElement.value = response.data.newMedia;
    })
}

const createComment = () => {
    axios.post(route('createComment'), {
        mediaId: currentElement.value['id'],
        body: form.body,
    }).then(response => {
        currentElement.value.comments = [response.data.comment, ...currentElement.value.comments];
        form.reset('body');
    })
}
</script>

<template>
    <div class="py-3">
        <div class="mx-auto max-w-2xl lg:max-w-7xl">

            <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">

                <template v-for="(element, index) in media">
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
                                <div
                                    :class="currentElement.isUserLikedMedia ? 'text-rose-500' : 'text-gray-700 dark:text-gray-300'"
                                    class="flex mr-2 text-sm mr-4 cursor-pointer"
                                    @click="storeLikedMedia(currentElement)">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"/>
                                    </svg>
                                    <span>{{ currentElement.liked }}</span>
                                </div>

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

                        <div class="w-full py-4">
                            <div class="px-4 py-2 bg-white rounded-t-lg dark:bg-gray-800 mb-2">
                                <label class="sr-only" for="comment">Комментарий</label>
                                <textarea id="body"
                                          v-model="form.body"
                                          class="w-full h-20 min-h-14 max-h-20 px-0 text-sm text-gray-900 bg-white border-0 dark:bg-gray-800 focus:ring-0 dark:text-white dark:placeholder-gray-400"
                                          placeholder="Прокомментировать..."
                                          required rows="3"/>

                                <InputError :message="form.errors.body" class="mt-2"/>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    class="inline-flex items-center py-2.5 px-4 text-xs font-medium text-center text-white bg-amber-600 rounded-lg focus:ring-1 focus:ring-amber-700 hover:bg-amber-700"
                                    type="submit"
                                    @click="createComment">
                                    Опубликовать
                                </button>
                            </div>
                        </div>

                        <div
                            class="md:fixed md:top-[17rem] md:right-0 md:bottom-5 overflow-y-auto block md:w-1/3">
                            <template v-for="comment in currentElement.comments">
                                <div
                                    class="relative bg-slate-50 rounded-xl overflow-hidden dark:bg-slate-800/25 max-w-3xl mx-auto mx-4 mb-6">
                                    <div class="relative rounded-xl overflow-auto p-5">
                                        <div class="flex w-full">
                                            <div>
                                                <img :alt="comment.user.fullName"
                                                     :src="comment.user.profile_photo_url"
                                                     :title="comment.user.fullName"
                                                     class="w-12 h-12 rounded-full object-cover mr-4 shadow">
                                            </div>
                                            <div class="w-full">
                                                <div>
                                                    <div class="sm:flex md:block 2xl:flex items-center justify-between">
                                                        <h2
                                                            class="text-lg font-semibold text-gray-900 dark:text-gray-300 -mt-1">
                                                            {{ comment.user.fullName }}
                                                        </h2>
                                                        <small class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ comment.timeAgo }}
                                                        </small>
                                                    </div>
                                                    <p class="mt-3 text-gray-700 dark:text-gray-300 text-sm">
                                                        {{ comment.body }}
                                                    </p>
                                                </div>

                                                <div class="mt-4 flex items-center">
                                                    <div
                                                        :class="comment.isUserLikedComment ? 'text-rose-500' : 'text-gray-700 dark:text-gray-300'"
                                                        class="flex mr-2 text-sm mr-4 cursor-pointer"
                                                        @click="storeLikedComment(currentElement, comment)">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                             viewBox="0 0 24 24">
                                                            <path
                                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"></path>
                                                        </svg>
                                                        <span>{{ comment.liked }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </template>

    </div>
</template>

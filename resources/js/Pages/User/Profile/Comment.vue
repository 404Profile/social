<script setup>
import axios from "axios";
import {ref} from "vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import Like from "@/Pages/User/Profile/Like.vue";

const props = defineProps({
    post: Object,
    media: Object,
});

const form = useForm({
    body: '',
});

const currentElement = ref(props.media ? props.media
    : props.post ? props.post
        : null);

const createComment = () => {
    const dataToSend = ref({
        post: [],
        media: [],
        body: '',
    });

    if (props.media) {
        dataToSend.value.media = props.media;
    } else if (props.post) {
        dataToSend.value.post = props.post;
    } else {
        dataToSend.value.post = null;
        dataToSend.value.media = null;
    }

    dataToSend.value.body = form.body;

    axios.post(route('createComment'), dataToSend.value).then(response => {
        currentElement.value.comments = [response.data.comment, ...currentElement.value.comments];
        form.reset('body');
    })
}

const deleteComment = (comment) => {
    if (confirm('Точно удалить данный комментарий?')) {
        const deletedCommentIndex = currentElement.value.comments.findIndex(c => c.id === comment.id);

        if (deletedCommentIndex !== -1) {
            axios.delete(route('deleteComment', comment), {}).then(response => {
                currentElement.value.comments.splice(deletedCommentIndex, 1);
            });
        }
    }
}
</script>

<template>
    <div>
        <div :class="post ? 'max-w-6xl mx-auto px-4': ''" class="w-full py-4">
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
            :class="post ? 'w-full max-w-7xl mx-auto px-4' : 'md:fixed md:top-[17rem] md:right-0 md:bottom-5 overflow-y-auto block md:w-1/3'">
            <template v-for="comment in currentElement.comments" :key="comment">
                <div
                    :class="post ? 'w-full' : 'max-w-3xl'"
                    class="relative bg-slate-50 rounded-xl overflow-hidden dark:bg-slate-800/25 mx-auto mx-4 mb-6">
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
                                    <Like :comment="comment"/>

                                    <template v-if="comment.user_id === $page.props.auth.user.id">
                                        <button class="text-rose-500 cursor-pointer focus:outline-none"
                                                @click="deleteComment(comment)">
                                            <svg class="size-5" fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.5" viewBox="0 0 24 24"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

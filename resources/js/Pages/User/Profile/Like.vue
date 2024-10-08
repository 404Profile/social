<script setup>
import axios from "axios";
import {ref} from "vue";

const props = defineProps({
    media: Object,
    comment: Object,
    post: Object,
})

const currentElement = ref(props.media ? props.media
    : props.comment ? props.comment
        : props.post ? props.post
            : null);

const likedElement = () => {
    const dataToSend = ref({
        post: [],
        comment: [],
        media: []
    });

    if (props.media) {
        dataToSend.value.media = props.media;
    } else if (props.comment) {
        dataToSend.value.comment = props.comment;
    } else if (props.post) {
        dataToSend.value.post = props.post;
    } else {
        dataToSend.value.post = null;
        dataToSend.value.comment = null;
        dataToSend.value.media = null;
    }

    axios.post(route('liked'), dataToSend.value).then(response => {
        const updatedElement = props.media ? props.media
            : props.comment ? props.comment
                : props.post ? props.post
                    : null;

        if (updatedElement) {
            updatedElement.liked = response.data.liked;
            if (props.media) {
                updatedElement.isUserLikedMedia = response.data.isUserLikedMedia;
            } else if (props.comment) {
                updatedElement.isUserLikedComment = response.data.isUserLikedComment;
            } else if (props.post) {
                updatedElement.isUserLikedPost = response.data.isUserLikedPost;
            }
        }
    });
}
</script>

<template>
    <div>
        <div
            :class="(media ? currentElement.isUserLikedMedia : post ? currentElement.isUserLikedPost : comment ? currentElement.isUserLikedComment : null) ? 'text-rose-500' : 'text-gray-700 dark:text-gray-300'"
            class="flex mr-2 text-sm mr-4 cursor-pointers select-none"
            @click="likedElement(currentElement)">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2"/>
            </svg>
            <span>{{ currentElement.liked }}</span>
        </div>
    </div>
</template>

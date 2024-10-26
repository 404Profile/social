<script setup>
import {useForm} from "@inertiajs/vue3";
import {computed, ref} from "vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    user: Object,
})

const imageUrls = ref([]);
const mediaInput = ref(null);
const MAX_IMAGES = 5;

const form = useForm({
    body: '',
    owner_user_id: props.user.id,
    files: [],
});

const createPost = () => {
    form.post(route("storePost"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body', 'files');
            imageUrls.value = [];
        },
    });
}

const onRemoveImage = (index) => {
    form.files.splice(index, 1);
    imageUrls.value.splice(index, 1);
};

const canAddMoreImages = computed(() => form.files.length < MAX_IMAGES);

const onFileChange = (e) => {
    const selectedFiles = e.target.files;
    for (let i = 0; i < selectedFiles.length; i++) {
        if (form.files.length < MAX_IMAGES) {
            form.files.push(selectedFiles[i]);
            const imageUrl = URL.createObjectURL(selectedFiles[i]);
            imageUrls.value.push(imageUrl);
        } else {
            return;
        }
    }
};

const selectNewMedia = () => {
    mediaInput.value.click();
};
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="w-full mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
            <div class="px-4 py-2 bg-white rounded-t-lg dark:bg-gray-800">
                <label class="sr-only" for="body">Новая публикация</label>
                <textarea id="body"
                          v-model="form.body"
                          class="w-full h-28 min-h-14 max-h-96 px-0 text-sm text-gray-900 bg-white border-0 dark:bg-gray-800 focus:ring-0 dark:text-white dark:placeholder-gray-400"
                          placeholder="Новая публикация..."
                          required rows="4"/>

                <InputError :message="form.errors.body" class="mt-2"/>
            </div>
            <div class="flex items-center justify-between px-3 py-2 border-t dark:border-gray-600">
                <button
                    class="inline-flex items-center py-2.5 px-4 text-xs font-medium text-center text-white bg-amber-600 rounded-lg focus:ring-1 focus:ring-amber-700 hover:bg-amber-700"
                    type="submit"
                    @click="createPost">
                    Опубликовать
                </button>
                <div class="flex ps-0 space-x-1 rtl:space-x-reverse sm:ps-2">
                    <button
                        class="inline-flex justify-center items-center p-2 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600"
                        type="button">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 12 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 6v8a5 5 0 1 0 10 0V4.5a3.5 3.5 0 1 0-7 0V13a2 2 0 0 0 4 0V6"
                                  stroke="currentColor" stroke-linejoin="round"
                                  stroke-width="2"/>
                        </svg>
                        <span class="sr-only">Attach file</span>
                    </button>
                    <button
                        class="inline-flex justify-center items-center p-2 text-gray-500 rounded cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600"
                        type="button"
                        @click.prevent="selectNewMedia">
                        <svg aria-hidden="true" class="w-4 h-4" fill="currentColor"
                             viewBox="0 0 20 18" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18 0H2a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2Zm-5.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm4.376 10.481A1 1 0 0 1 16 15H4a1 1 0 0 1-.895-1.447l3.5-7A1 1 0 0 1 7.468 6a.965.965 0 0 1 .9.5l2.775 4.757 1.546-1.887a1 1 0 0 1 1.618.1l2.541 4a1 1 0 0 1 .028 1.011Z"/>
                        </svg>
                        <span class="sr-only">Upload image</span>
                    </button>
                </div>
            </div>

            <input ref="mediaInput" :disabled="!canAddMoreImages" accept="image/*" hidden multiple type="file"
                   @change="onFileChange">

            <div class="px-3 pt-2 border-t dark:border-gray-600">
                <p class="text-gray-600 dark:text-gray-300">
                    Файлов: {{ form.files.length }} из {{ MAX_IMAGES }}
                </p>
                <InputError :message="form.errors.files" class="mt-2"/>
            </div>
            <div
                class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 md:grid-cols-4 lg:gap-y-0 lg:grid-cols-5 px-3 py-2">
                <div v-for="(file, index) in form.files" :key="index">
                    <div class="px-1">
                        <div class="relative flex justify-center">
                            <img :src="imageUrls[index]" alt="404" class="preview w-auto h-20"/>

                            <button
                                class="absolute bottom-0 inset-x-1/2 cursor-pointer stroke-2 text-red-500 hover:text-red-600"
                                @click="onRemoveImage(index)">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</template>

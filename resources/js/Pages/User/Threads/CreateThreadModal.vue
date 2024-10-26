<script setup>
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";

const form = useForm({
    title: '',
})

const isOpenCreateThreadModal = ref(false);

const openCreateThreadModal = () => {
    isOpenCreateThreadModal.value = true;
    document.body.style.overflow = 'hidden';
}

const closeCreateThreadModal = () => {
    isOpenCreateThreadModal.value = false;
    document.body.style.overflow = null;
}

const createThread = () => {
    form.post(route('threads.store'), {
        onFinish: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <div>
        <div class="pb-4">
            <button class="flex" @click="openCreateThreadModal">
                Создать чат
                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5"
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <div v-show="isOpenCreateThreadModal" class="relative z-50">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full justify-center p-4 text-center items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-full sm:w-full sm:max-w-lg">
                        <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="">
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
                                        Создать новую беседу
                                    </h3>
                                    <div class="mt-2">
                                        <InputLabel for="title" value="Название беседы"/>
                                        <TextInput
                                            id="title"
                                            v-model="form.title"
                                            class="mt-1 block w-full"
                                            required
                                            type="title"
                                        />
                                        <InputError :message="form.errors.title" class="mt-2"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-slate-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                                type="button"
                                @click="createThread">
                                Создать беседу
                            </button>
                            <button
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                type="button"
                                @click="closeCreateThreadModal">
                                Отменить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {Link} from "@inertiajs/vue3";
import CreateThreadModal from "@/Pages/User/Threads/CreateThreadModal.vue";

const props = defineProps({
    threads: Object,
})

const filterText = (text) => {
    if (!text) {
        return '';
    }

    const maxLength = 60;

    if (text.length <= maxLength) {
        return text;
    }

    return text.slice(0, maxLength) + '...';
}
</script>

<template>
    <AppLayout title="Чаты">

        <CreateThreadModal/>

        <div class="max-w-7xl sm:max-w-3xl mx-auto flex flex-col space-y-4">

            <template v-for="thread in threads" :key="thread">
                <Link :href="route('threads.show', thread.id)">
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-md sm:rounded-lg py-3 px-2 sm:px-6">
                        <div class="flex">
                            <div class="flex items-center">
                                <div class="min-w-16">
                                    <img :src="thread.imageThread" alt="Изображение беседы"
                                         class="w-auto h-16 rounded-full">
                                </div>
                                <div class="pl-3">
                                    <div>
                                        <p class="text-lg font-bold">{{ filterText(thread.titleThread) }}</p>
                                    </div>

                                    <div>
                                        <p>
                                            {{ filterText(thread.latestMessage?.body) }}
                                        </p>
                                        <p>{{ thread.lastMessageTimeAgo }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>
            </template>

        </div>
    </AppLayout>
</template>


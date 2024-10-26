<script setup>
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    users: Object,
});
</script>

<template>
    <div class="max-w-6xl mx-auto">
        <ul class="divide-y divide-gray-100" role="list">
            <template v-for="user in users" :key="user">
                <Link :href="route('userProfile', user)">
                    <li class="flex justify-between gap-x-6 py-5">
                        <div class="flex min-w-0 gap-x-4">
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
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 text-gray-900 dark:text-gray-400">
                                {{ user.age }} лет, {{ user.gender }}
                            </p>
                        </div>
                    </li>
                </Link>
            </template>
        </ul>
    </div>
</template>

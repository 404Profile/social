<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import UserCard from "@/Pages/User/Friends/UserCard.vue";

const props = defineProps({
    user: Object,
    followers: Object,
});
</script>

<template>
    <AppLayout :title="'Подписчики' + user.fullName">
        <div class="max-w-7xl mx-auto flex">
            <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                Подписчики {{ user.fullName }} ({{ followers.length }})
            </h1>
        </div>

        <UserCard :users="followers"/>
    </AppLayout>
</template>

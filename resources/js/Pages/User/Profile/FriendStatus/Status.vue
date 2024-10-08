<script setup>
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import DangerButton from "@/Components/DangerButton.vue";
import Loading from "@/Pages/User/Profile/FriendStatus/Loading.vue";

const props = defineProps({
    user: Object,
    friendRequestSentTo: Number,
    friendRequestReceivedFrom: Number,
    isFriendsWith: Number,
});

const loading = ref(false);

const addFriend = () => {
    loading.value = true;
    router.post(route('friends.store', props.user.id), {
        user: props.user,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
        },
    });
};

const deleteFriend = () => {
    loading.value = true;
    router.delete(route('friends.destroy', props.user.id), {
        user: props.user,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
        },
    });
}

const acceptFriend = () => {
    loading.value = true;
    router.patch(route('friends.update', props.user.id), {
        user: props.user,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
        },
    });
}

const ignoreFriend = () => {
    loading.value = true;
    router.get(route('friends.deny', props.user.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
        },
    });
}

const canselRequest = () => {
    loading.value = true;
    router.delete(route('friends.canselRequest', props.user.id), {
        user: props.user,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <div>
        <template v-if="friendRequestReceivedFrom">
            <div class="space-x-2">
                <button
                    class="bg-amber-500 px-1 py-0.5 text-white font-semibold text-sm rounded block text-center sm:inline-block block"
                    @click="acceptFriend">
                    Принять запрос
                </button>

                <button
                    class="bg-rose-500 px-1 py-0.5 text-white font-semibold text-sm rounded block text-center sm:inline-block block"
                    @click="ignoreFriend">
                    Отклонить запрос
                </button>
            </div>
        </template>

        <template v-else-if="friendRequestSentTo">
            <div class="block sm:flex">
                <h3 class="font-semibold text-md text-gray-800 dark:text-white leading-tight">
                    Ожидание
                </h3>

                <button class="cursor-pointer text-rose-500 pl-1 hover:scale-110" @click="canselRequest">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </template>

        <template v-else-if="isFriendsWith">
            <template v-if="loading">
                <Loading/>
                Загрузка...
            </template>

            <template v-else>
                <DangerButton class="py-0.5" @click="deleteFriend">
                    Удалить из друзей
                </DangerButton>
            </template>
        </template>

        <template v-else-if="$page.props.auth.user.id !== user.id">
            <button
                class="bg-amber-500 px-2 py-1 text-white font-semibold text-sm rounded block text-center sm:inline-block block"
                @click="addFriend">
                Добавить в друзья
            </button>
        </template>
    </div>
</template>

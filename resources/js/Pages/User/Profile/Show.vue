<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref} from "vue";
import CreatePost from "@/Pages/User/Profile/CreatePost.vue";
import Posts from "@/Pages/User/Profile/Posts.vue";
import AllMedia from "@/Pages/User/Profile/AllMedia.vue";
import Status from "@/Pages/User/Profile/FriendStatus/Status.vue";
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    user: Object,
    media: Object,
    counterMedia: Number,
    counterPosts: Number,
    friendsCount: Number,
    followersCount: Number,
    isFriendsWith: Number,
    friendRequestSentTo: Number,
    friendRequestReceivedFrom: Number,
    followingCount: Number,
});

const isShowFullAbout = ref(false);
</script>

<template>
    <AppLayout :title="'Профиль ' + user.fullName">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-wrap items-center p-4 md:py-8">

                <div class="md:w-3/12 md:ml-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        class="w-20 h-20 md:w-40 md:h-40 rounded-full bg-gradient-to-tr from-amber-600 to-red-600 p-0.5">
                        <div class="w-full h-full rounded-full bg-white  flex items-center justify-center">
                            <div class="w-full h-full">
                                <img :alt="'Фотография ' + user.fullName"
                                     :src="user.profile_photo_url"
                                     class="w-full h-full rounded-full p-[2px] bg-white dark:bg-slate-900">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-8/12 md:w-7/12 ml-4">
                    <div class="md:flex md:flex-wrap md:items-center mb-4">
                        <h2 class="text-gray-700 dark:text-gray-300 text-3xl inline-block font-light md:mr-2 mb-2 sm:mb-0">
                            {{ user.fullName }}
                        </h2>

                        <span v-if="user.email_verified_at"
                              class="inline-block text-amber-500 relative mr-6 text-xl transform -translate-y-2 stroke-2">
                            <svg class="size-6" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <template v-if="user.id !== $page.props.auth.user.id">
                            <Status :friendRequestReceivedFrom="friendRequestReceivedFrom"
                                    :friendRequestSentTo="friendRequestSentTo"
                                    :isFriendsWith="isFriendsWith"
                                    :user="user"/>
                        </template>
                    </div>

                    <ul class="hidden md:flex space-x-8 mb-4 text-gray-600 dark:text-gray-300">
                        <li>
                            <span class="font-semibold dark:text-white">
                                {{ user.posts.length }}
                            </span>
                            {{
                                `публикаци${user.posts.length % 10 === 1 && user.posts.length % 100 !== 11 ? 'я' : user.posts.length % 10 >= 2 && user.posts.length % 10 <= 4 && (user.posts.length % 100 < 10 || user.posts.length % 100 >= 20) ? 'и' : 'й'}`
                            }}
                        </li>

                        <li>
                            <Link :href="route('friends.followers', user)">
                                <span class="font-semibold dark:text-white">{{ followersCount }}</span>
                                {{
                                    followersCount === 1 ? 'подписчик' : followersCount < 5 ? 'подписчика' : 'подписчиков'
                                }}
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('friends.following', user)">
                                <span class="font-semibold dark:text-white">{{ followingCount }}</span>
                                {{ followingCount === 1 ? 'подписка' : followingCount < 5 ? 'подписки' : 'подписок' }}
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('friends.index', user)">
                                <span class="font-semibold dark:text-white">{{ friendsCount }}</span>
                                {{ friendsCount === 1 ? 'друг' : friendsCount < 5 ? 'друга' : 'друзей' }}
                            </Link>
                        </li>
                    </ul>

                    <div v-if="user.about" class="hidden md:block text-gray-600 dark:text-gray-300">
                        <p>{{ user.about }}</p>
                    </div>

                </div>
            </div>

            <div>
                <div v-if="user.about" class="md:hidden text-sm my-2 text-gray-600 dark:text-gray-300">
                    <p v-if="user.about.length < 100">{{ user.about }}</p>
                    <div v-else>
                        <p v-if="isShowFullAbout">{{ user.about }}</p>
                        <p v-else>{{ user.about.substring(0, 100) + "..." }}</p>
                        <button class="text-amber-600 cursor-pointer pt-2 focus:outline-none"
                                @click="isShowFullAbout = !isShowFullAbout">
                            {{ isShowFullAbout ? 'Скрыть информацию' : 'Показать полностью' }}
                        </button>
                    </div>
                </div>

                <div class="w-full">
                    <CreatePost :user="user"/>
                </div>
            </div>

            <div class="px-px md:px-3">
                <ul class="flex md:hidden justify-around space-x-8 border-t text-center p-2 text-gray-600 dark:text-gray-300 leading-snug text-sm">
                    <li>
                        <span class="font-semibold text-gray-800 dark:text-white block">
                            {{ user.posts.length }}
                        </span>
                        {{
                            `публикаци${user.posts.length % 10 === 1 && user.posts.length % 100 !== 11 ? 'я' : user.posts.length % 10 >= 2 && user.posts.length % 10 <= 4 && (user.posts.length % 100 < 10 || user.posts.length % 100 >= 20) ? 'и' : 'й'}`
                        }}
                    </li>

                    <li>
                        <Link :href="route('friends.followers', user)">
                            <span class="font-semibold text-gray-800 dark:text-white block">{{ followersCount }}</span>
                            {{ followersCount === 1 ? 'подписчик' : followersCount < 5 ? 'подписчика' : 'подписчиков' }}
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('friends.following', user)">
                            <span class="font-semibold text-gray-800 dark:text-white block">{{ followingCount }}</span>
                            {{ followingCount === 1 ? 'подписка' : followingCount < 5 ? 'подписки' : 'подписок' }}
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('friends.index', user)">
                            <span class="font-semibold text-gray-800 dark:text-white block">{{ friendsCount }}</span>
                            {{ friendsCount === 1 ? 'друг' : friendsCount < 5 ? 'друга' : 'друзей' }}
                        </Link>
                    </li>
                </ul>

                <ul class="flex items-center justify-around md:justify-center space-x-12 uppercase tracking-widest font-semibold text-xs text-gray-600 border-t border-gray-300">
                    <li class="border-t border-amber-500 -mt-px text-gray-700">
                        <a class="inline-block p-3 dark:text-gray-300" href="#">
                            <span>медиафайлы ({{ counterMedia }})</span>
                        </a>
                    </li>
                </ul>

                <AllMedia :counterMedia="counterMedia" :media="media" :user="user"/>

                <ul class="pb-4 flex items-center justify-around md:justify-center space-x-12 uppercase tracking-widest font-semibold text-xs text-gray-600 border-t border-gray-300 mt-5">
                    <li class="border-t border-amber-500 -mt-px text-gray-700">
                        <a class="inline-block p-3 dark:text-gray-300" href="#">
                            <span>публикации ({{ counterPosts }})</span>
                        </a>
                    </li>
                </ul>

                <Posts :posts="user.posts"/>

            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
    transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
}
</style>

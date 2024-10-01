<script setup>
import {useDark, useToggle} from '@vueuse/core'

const isDark = useDark();
const toggleDark = useToggle(isDark);
</script>

<template>
    <div class="relative">
        <transition name="slide-up">
            <button v-if="isDark"
                    class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-lg p-1.5 absolute -top-5 right-0"
                    @click="toggleDark()">
                <span>🌞</span>
            </button>
            <button v-else
                    class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-lg p-1.5 absolute -top-5 right-0"
                    @click="toggleDark()">
                <span>🌚</span>
            </button>
        </transition>
    </div>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.25s ease-out;
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(30px);
}

.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-30px);
}
</style>

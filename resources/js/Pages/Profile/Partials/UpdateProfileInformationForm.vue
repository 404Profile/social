<script setup>
import {ref} from 'vue';
import {Link, router, useForm} from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    surname: props.user.surname,
    private: !!props.user.private,
    age: props.user.age + '',
    country: props.user.country,
    city: props.user.city,
    gender: props.user.gender,
    about: props.user.about,
    email: props.user.email,
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.about = form.about?.replace(/\s\s+/g, ' ') || '';

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            Информация профиля
        </template>

        <template #description>
            Обновите информацию профиля вашей учетной записи и адрес электронной почты.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input
                    id="photo"
                    ref="photoInput"
                    class="hidden"
                    type="file"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="Фотография"/>

                <!-- Current Profile Photo -->
                <div v-show="! photoPreview" class="mt-2">
                    <img :alt="user.name" :src="user.profile_photo_url" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                    />
                </div>

                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewPhoto">
                    Выбрать новую фотографию
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.profile_photo_path"
                    class="mt-2"
                    type="button"
                    @click.prevent="deletePhoto"
                >
                    Удалить фотографию
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2"/>
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Имя"/>
                <TextInput
                    id="name"
                    v-model="form.name"
                    autocomplete="name"
                    class="mt-1 block w-full"
                    required
                    type="text"
                />
                <InputError :message="form.errors.name" class="mt-2"/>
            </div>

            <!-- Surname -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="surname" value="Фамилия"/>
                <TextInput
                    id="surname"
                    v-model="form.surname"
                    autocomplete="surname"
                    class="mt-1 block w-full"
                    required
                    type="text"
                />
                <InputError :message="form.errors.surname" class="mt-2"/>
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="Email"/>
                <TextInput
                    id="email"
                    v-model="form.email"
                    autocomplete="username"
                    class="mt-1 block w-full"
                    required
                    type="email"
                />
                <InputError :message="form.errors.email" class="mt-2"/>

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                    <p class="text-sm mt-2 dark:text-white">
                        Ваш адрес электронной почты не подтвержден.

                        <Link
                            :href="route('verification.send')"
                            as="button"
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            method="post"
                            @click.prevent="sendEmailVerification"
                        >
                            Нажмите здесь, чтобы повторно отправить электронное письмо с подтверждением.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent"
                         class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                        На ваш электронный адрес была отправлена новая ссылка для подтверждения.
                    </div>
                </div>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <p class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                    Показывать профиль для других пользователей
                </p>
                <div class="relative flex gap-x-3">
                    <div class="flex h-6 items-center">
                        <input id="private"
                               v-model="form.private"
                               :checked="form.private"
                               class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-600"
                               type="checkbox">
                    </div>
                    <div class="flex items-center leading-6">
                        <InputLabel :value="form.private ? 'Не показывать' : 'Показывать'" for="private"/>
                    </div>
                </div>
                <InputError :message="form.errors.private" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="age" value="Возраст"/>
                <TextInput
                    id="age"
                    v-model="form.age"
                    autocomplete="age"
                    class="mt-1 block w-min"
                    type="number"
                />

                <InputError :message="form.errors.age" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="country" value="Страна проживания"/>
                <TextInput
                    id="country"
                    v-model="form.country"
                    autocomplete="country"
                    class="mt-1 block w-full"
                    type="text"
                />

                <InputError :message="form.errors.country" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="city" value="Город проживания"/>
                <TextInput
                    id="city"
                    v-model="form.city"
                    autocomplete="city"
                    class="mt-1 block w-full"
                    type="text"
                />

                <InputError :message="form.errors.city" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="gender" value="Пол"/>
                <select id="gender" v-model="form.gender"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-min min-w-16">
                    <option value="M">М</option>
                    <option value="F">Ж</option>
                </select>

                <InputError :message="form.errors.location" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel :value="'Обо мне (Макс: 255 символов. Осталось ' + (255 - form.about?.length || 255) + ')'"
                            for="about"/>
                <textarea
                    id="about"
                    v-model="form.about"
                    :class="(255 - form.about?.length || 255) < 0 ? 'border-red-500 focus:ring-red-600 dark:focus:ring-red-600 focus:border-red-500 dark:focus:border-red-600' : 'border-gray-300 dark:border-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600'"
                    autocomplete="about"
                    class="dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm mt-1 block w-full resize-y caret-amber-500"
                    rows="4"
                />

                <InputError :message="form.errors.about" class="mt-2"/>
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Сохранено.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Сохранить
            </PrimaryButton>
        </template>
    </FormSection>
</template>

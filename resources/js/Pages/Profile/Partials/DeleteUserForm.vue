<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title>
            Удалить учётную запись
        </template>

        <template #description>
            Удалите свою учетную запись навсегда.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                После того как ваша учётная запись будет удалена, все её ресурсы и данные будут уничтожены навсегда.
                Пожалуйста, загрузите любую информацию, которую хотите сохранить, перед тем как закрыть свою учётную
                запись.
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmUserDeletion">
                    Удалить учётную запись
                </DangerButton>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title>
                    Удалить учётную запись
                </template>

                <template #content>
                    Вы уверены, что хотите удалить свою учётную запись? После удаления все ваши ресурсы и данные будут
                    безвозвратно утрачены. Пожалуйста, введите ваш пароль, чтобы подтвердить своё намерение навсегда
                    избавиться от учётной записи.

                    <div class="mt-4">
                        <TextInput
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-3/4"
                            placeholder="Пароль"
                            autocomplete="current-password"
                            @keyup.enter="deleteUser"
                        />

                        <InputError :message="form.errors.password" class="mt-2"/>
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        Отменить
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Удалить учётную запись
                    </DangerButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>

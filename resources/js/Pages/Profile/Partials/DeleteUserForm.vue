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
            <span class="text-gray-900 dark:text-gray-100">Удаление аккаунта</span>
        </template>

        <template #description>
            <span class="text-gray-600 dark:text-gray-400">Навсегда удалить ваш аккаунт.</span>
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                После удаления аккаунта все его ресурсы и данные будут безвозвратно удалены. Перед удалением аккаунта, пожалуйста, скачайте любые данные или информацию, которые вы хотите сохранить.
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmUserDeletion">
                    Удалить аккаунт
                </DangerButton>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title>
                    <span class="text-gray-900 dark:text-gray-100">Удаление аккаунта</span>
                </template>

                <template #content>
                    <div class="text-gray-700 dark:text-gray-300">
                        Вы уверены, что хотите удалить свой аккаунт? После удаления аккаунта все его ресурсы и данные будут безвозвратно удалены. Пожалуйста, введите свой пароль, чтобы подтвердить, что вы хотите навсегда удалить свой аккаунт.

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

                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        Отмена
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Удалить аккаунт
                    </DangerButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>

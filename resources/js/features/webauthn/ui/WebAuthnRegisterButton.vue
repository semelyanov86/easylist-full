<script setup lang="ts">
import { Button } from '@shared/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@shared/ui/dialog';
import { Input } from '@shared/ui/input';
import { KeyRound } from 'lucide-vue-next';
import { ref } from 'vue';

import { useWebAuthn } from '../model/useWebAuthn';

const emit = defineEmits<{
    registered: [];
}>();

const { register, loading, error } = useWebAuthn();

const showDialog = ref<boolean>(false);
const alias = ref<string>('');

const handleRegister = async (): Promise<void> => {
    const success = await register(alias.value);

    if (success) {
        showDialog.value = false;
        alias.value = '';
        emit('registered');
    }
};

const openDialog = (): void => {
    error.value = null;
    alias.value = '';
    showDialog.value = true;
};
</script>

<template>
    <div>
        <Button @click="openDialog">
            <KeyRound class="size-4" />
            Добавить ключ безопасности
        </Button>

        <Dialog :open="showDialog" @update:open="showDialog = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Регистрация ключа безопасности</DialogTitle>
                    <DialogDescription>
                        Введите название для ключа и следуйте инструкциям
                        браузера.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <Input
                        v-model="alias"
                        placeholder="Например: YubiKey 5"
                        :disabled="loading"
                    />

                    <p v-if="error" class="text-sm text-destructive">
                        {{ error }}
                    </p>
                </div>

                <DialogFooter>
                    <Button
                        variant="outline"
                        :disabled="loading"
                        @click="showDialog = false"
                    >
                        Отмена
                    </Button>
                    <Button :disabled="loading" @click="handleRegister">
                        {{ loading ? 'Регистрация...' : 'Зарегистрировать' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

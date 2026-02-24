<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import Heading from '@shared/components/Heading.vue';
import { Badge } from '@shared/ui/badge';
import { Button } from '@shared/ui/button';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

import { disable, enable } from '@/routes/two-factor';

import { useTwoFactorAuth } from '../model/useTwoFactorAuth';
import TwoFactorRecoveryCodes from './TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from './TwoFactorSetupModal.vue';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Двухфакторная аутентификация"
            description="Управление настройками двухфакторной аутентификации"
        />

        <div
            v-if="!twoFactorEnabled"
            class="flex flex-col items-start justify-start space-y-4"
        >
            <Badge variant="destructive">Отключена</Badge>

            <p class="text-muted-foreground">
                При включении двухфакторной аутентификации вам будет предложено
                ввести безопасный PIN-код при входе. Этот код можно получить из
                приложения-аутентификатора с поддержкой TOTP на вашем телефоне.
            </p>

            <div>
                <Button v-if="hasSetupData" @click="showSetupModal = true">
                    <ShieldCheck />Продолжить настройку
                </Button>
                <Form
                    v-else
                    v-bind="enable.form()"
                    @success="showSetupModal = true"
                    #default="{ processing }"
                >
                    <Button type="submit" :disabled="processing">
                        <ShieldCheck />Включить 2FA</Button
                    ></Form
                >
            </div>
        </div>

        <div v-else class="flex flex-col items-start justify-start space-y-4">
            <Badge variant="default">Включена</Badge>

            <p class="text-muted-foreground">
                Двухфакторная аутентификация включена. При входе вам будет
                предложено ввести безопасный случайный PIN-код, который можно
                получить из приложения-аутентификатора с поддержкой TOTP на
                вашем телефоне.
            </p>

            <TwoFactorRecoveryCodes />

            <div class="relative inline">
                <Form v-bind="disable.form()" #default="{ processing }">
                    <Button
                        variant="destructive"
                        type="submit"
                        :disabled="processing"
                    >
                        <ShieldBan />
                        Отключить 2FA
                    </Button>
                </Form>
            </div>
        </div>

        <TwoFactorSetupModal
            v-model:isOpen="showSetupModal"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />
    </div>
</template>

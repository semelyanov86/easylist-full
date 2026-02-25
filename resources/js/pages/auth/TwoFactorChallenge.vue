<script setup lang="ts">
import { useWebAuthn } from '@features/webauthn/model/useWebAuthn';
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import type { TwoFactorConfigContent } from '@shared/types';
import { Button } from '@shared/ui/button';
import { Input } from '@shared/ui/input';
import { InputOTP, InputOTPGroup, InputOTPSlot } from '@shared/ui/input-otp';
import { AuthLayout } from '@widgets/auth';
import { Fingerprint } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import { store } from '@/routes/two-factor/login';

type Props = {
    availableMethods?: string[];
    intendedUrl?: string;
};

const props = withDefaults(defineProps<Props>(), {
    availableMethods: () => ['totp'],
    intendedUrl: '/dashboard',
});

const hasTotp = computed<boolean>(() =>
    props.availableMethods.includes('totp'),
);
const hasWebAuthn = computed<boolean>(() =>
    props.availableMethods.includes('webauthn'),
);

type ActiveMethod = 'totp' | 'webauthn' | 'recovery';

const activeMethod = ref<ActiveMethod>(
    props.availableMethods.includes('webauthn') ? 'webauthn' : 'totp',
);

const {
    authenticate,
    loading: webAuthnLoading,
    error: webAuthnError,
} = useWebAuthn();

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (activeMethod.value === 'recovery') {
        return {
            title: 'Код восстановления',
            description:
                'Введите один из ваших резервных кодов восстановления для подтверждения доступа.',
            buttonText: 'использовать код аутентификации',
        };
    }

    if (activeMethod.value === 'webauthn') {
        return {
            title: 'Ключ безопасности',
            description:
                'Используйте ваш аппаратный ключ безопасности для подтверждения входа.',
            buttonText: 'использовать код аутентификации',
        };
    }

    return {
        title: 'Двухфакторная аутентификация',
        description: 'Введите код из вашего приложения-аутентификатора.',
        buttonText: 'использовать код восстановления',
    };
});

const code = ref<string>('');

const switchToMethod = (
    method: ActiveMethod,
    clearErrors?: () => void,
): void => {
    activeMethod.value = method;
    clearErrors?.();
    code.value = '';
    webAuthnError.value = null;
};

const handleWebAuthnAuth = async (): Promise<void> => {
    await authenticate(props.intendedUrl);
};
</script>

<template>
    <AuthLayout
        :title="authConfigContent.title"
        :description="authConfigContent.description"
    >
        <Head title="Двухфакторная аутентификация" />

        <div class="space-y-6">
            <!-- WebAuthn -->
            <template v-if="activeMethod === 'webauthn'">
                <div class="space-y-5">
                    <div
                        class="flex flex-col items-center justify-center gap-4"
                    >
                        <div
                            class="flex size-16 items-center justify-center rounded-full border border-border bg-muted"
                        >
                            <Fingerprint class="size-8 text-muted-foreground" />
                        </div>

                        <p
                            v-if="webAuthnError"
                            class="text-center text-sm text-destructive"
                        >
                            {{ webAuthnError }}
                        </p>

                        <Button
                            class="w-full"
                            :disabled="webAuthnLoading"
                            @click="handleWebAuthnAuth"
                        >
                            {{
                                webAuthnLoading
                                    ? 'Ожидание ключа...'
                                    : 'Использовать ключ безопасности'
                            }}
                        </Button>
                    </div>

                    <div
                        v-if="hasTotp"
                        class="text-center text-sm text-muted-foreground"
                    >
                        <span>или </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-border underline-offset-4 transition-colors duration-300 ease-out hover:decoration-foreground"
                            @click="switchToMethod('totp')"
                        >
                            использовать код аутентификации
                        </button>
                    </div>
                </div>
            </template>

            <!-- TOTP -->
            <template v-else-if="activeMethod === 'totp'">
                <Form
                    v-bind="store.form()"
                    class="space-y-5"
                    reset-on-error
                    @error="code = ''"
                    #default="{ errors, processing, clearErrors }"
                >
                    <input type="hidden" name="code" :value="code" />
                    <div
                        class="flex flex-col items-center justify-center gap-3 text-center"
                    >
                        <div class="flex w-full items-center justify-center">
                            <InputOTP
                                id="otp"
                                v-model="code"
                                :maxlength="6"
                                :disabled="processing"
                                autofocus
                            >
                                <InputOTPGroup>
                                    <InputOTPSlot
                                        v-for="index in 6"
                                        :key="index"
                                        :index="index - 1"
                                    />
                                </InputOTPGroup>
                            </InputOTP>
                        </div>
                        <InputError :message="errors.code" />
                    </div>
                    <Button type="submit" class="w-full" :disabled="processing">
                        Продолжить
                    </Button>
                    <div class="text-center text-sm text-muted-foreground">
                        <span>или </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-border underline-offset-4 transition-colors duration-300 ease-out hover:decoration-foreground"
                            @click="
                                () => switchToMethod('recovery', clearErrors)
                            "
                        >
                            {{ authConfigContent.buttonText }}
                        </button>
                        <template v-if="hasWebAuthn">
                            <span> / </span>
                            <button
                                type="button"
                                class="text-foreground underline decoration-border underline-offset-4 transition-colors duration-300 ease-out hover:decoration-foreground"
                                @click="
                                    () =>
                                        switchToMethod('webauthn', clearErrors)
                                "
                            >
                                использовать ключ безопасности
                            </button>
                        </template>
                    </div>
                </Form>
            </template>

            <!-- Recovery -->
            <template v-else>
                <Form
                    v-bind="store.form()"
                    class="space-y-5"
                    reset-on-error
                    #default="{ errors, processing, clearErrors }"
                >
                    <Input
                        name="recovery_code"
                        type="text"
                        placeholder="Введите код восстановления"
                        autofocus
                        required
                    />
                    <InputError :message="errors.recovery_code" />
                    <Button type="submit" class="w-full" :disabled="processing">
                        Продолжить
                    </Button>

                    <div class="text-center text-sm text-muted-foreground">
                        <span>или </span>
                        <button
                            type="button"
                            class="text-foreground underline decoration-border underline-offset-4 transition-colors duration-300 ease-out hover:decoration-foreground"
                            @click="
                                () =>
                                    switchToMethod(
                                        hasTotp ? 'totp' : 'webauthn',
                                        clearErrors,
                                    )
                            "
                        >
                            {{ authConfigContent.buttonText }}
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>

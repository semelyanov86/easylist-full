<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import Heading from '@shared/components/Heading.vue';
import type { WebAuthnCredential } from '@shared/types';
import { ref } from 'vue';

import WebAuthnKeyList from './WebAuthnKeyList.vue';
import WebAuthnRegisterButton from './WebAuthnRegisterButton.vue';

type Props = {
    credentials: WebAuthnCredential[];
};

const props = defineProps<Props>();

const localCredentials = ref<WebAuthnCredential[]>([...props.credentials]);

const handleDelete = (credentialId: string): void => {
    localCredentials.value = localCredentials.value.filter(
        (c) => c.id !== credentialId,
    );
};

const handleRegistered = (): void => {
    router.reload({ only: ['webauthnCredentials'] });
};
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Аппаратные ключи безопасности"
            description="Управление ключами безопасности (YubiKey, FIDO2) для двухфакторной аутентификации"
        />

        <WebAuthnKeyList
            :credentials="localCredentials"
            @delete="handleDelete"
        />

        <WebAuthnRegisterButton @registered="handleRegistered" />
    </div>
</template>

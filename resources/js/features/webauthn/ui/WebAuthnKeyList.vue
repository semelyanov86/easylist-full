<script setup lang="ts">
import type { WebAuthnCredential } from '@shared/types';
import { Button } from '@shared/ui/button';
import { Fingerprint, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

type Props = {
    credentials: WebAuthnCredential[];
};

defineProps<Props>();

const emit = defineEmits<{
    delete: [credentialId: string];
}>();

const deletingId = ref<string | null>(null);

const getXsrfToken = (): string => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    return match?.[1] ? decodeURIComponent(match[1]) : '';
};

const handleDelete = async (credentialId: string): Promise<void> => {
    deletingId.value = credentialId;

    try {
        const response = await fetch(`/webauthn/${credentialId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
        });

        if (response.ok) {
            emit('delete', credentialId);
        }
    } finally {
        deletingId.value = null;
    }
};

const formatDate = (dateString: string | null): string => {
    if (!dateString) {
        return '—';
    }

    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <div v-if="credentials.length > 0" class="space-y-3">
        <div
            v-for="credential in credentials"
            :key="credential.id"
            class="flex items-center justify-between gap-4 rounded-lg border border-border bg-card p-4"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-muted"
                >
                    <Fingerprint class="size-5 text-muted-foreground" />
                </div>
                <div>
                    <p class="text-sm font-medium text-foreground">
                        {{ credential.alias || 'Без названия' }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Добавлен {{ formatDate(credential.created_at) }}
                    </p>
                </div>
            </div>
            <Button
                variant="ghost"
                size="icon"
                :disabled="deletingId === credential.id"
                @click="handleDelete(credential.id)"
            >
                <Trash2 class="size-4 text-destructive" />
            </Button>
        </div>
    </div>
    <p v-else class="text-sm text-muted-foreground">
        Нет зарегистрированных ключей безопасности.
    </p>
</template>

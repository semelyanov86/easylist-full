<script setup lang="ts">
import type { ApiToken } from '@shared/types';
import { Button } from '@shared/ui/button';
import { Key, Trash2 } from 'lucide-vue-next';

type Props = {
    tokens: ApiToken[];
};

defineProps<Props>();

const emit = defineEmits<{
    delete: [token: ApiToken];
}>();

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <div v-if="tokens.length > 0" class="space-y-3">
        <div
            v-for="token in tokens"
            :key="token.id"
            class="flex items-center justify-between rounded-lg border border-border p-4"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-9 items-center justify-center rounded-md bg-muted"
                >
                    <Key class="size-4 text-muted-foreground" />
                </div>
                <div>
                    <p class="text-sm font-medium text-foreground">
                        {{ token.name }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Создан {{ formatDate(token.created_at) }}
                        <template v-if="token.last_used_at">
                            &middot; Использован
                            {{ formatDate(token.last_used_at) }}
                        </template>
                    </p>
                </div>
            </div>

            <Button
                variant="ghost"
                size="icon"
                @click="emit('delete', token)"
                data-test="delete-token-button"
            >
                <Trash2 class="size-4 text-muted-foreground" />
            </Button>
        </div>
    </div>

    <p v-else class="text-sm text-muted-foreground">
        У вас пока нет API-токенов.
    </p>
</template>

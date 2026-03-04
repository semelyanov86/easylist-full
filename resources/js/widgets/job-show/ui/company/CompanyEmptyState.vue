<script setup lang="ts">
import { Badge } from '@shared/ui/badge';
import { Building2, Loader2, Sparkles } from 'lucide-vue-next';

type Props = {
    isPremium: boolean;
    loading: boolean;
    error: string | null;
};

defineProps<Props>();

const emit = defineEmits<{
    analyze: [];
}>();
</script>

<template>
    <div
        class="flex flex-col items-center gap-5 rounded-xl border border-dashed border-border bg-card py-16 shadow-sm"
    >
        <div
            class="flex size-14 items-center justify-center rounded-2xl bg-muted"
        >
            <Building2 class="size-7 text-muted-foreground/50" />
        </div>
        <div class="max-w-xs text-center">
            <p class="font-medium text-foreground">
                Информация о компании отсутствует
            </p>
            <p class="mt-1 text-sm text-muted-foreground">
                Запустите ИИ-анализ для получения данных о компании
            </p>
        </div>
        <div v-if="!isPremium" class="text-center">
            <Badge variant="secondary" class="text-xs">
                Доступно в Premium
            </Badge>
        </div>
        <button
            v-else
            type="button"
            :disabled="loading"
            class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-5 py-2.5 text-sm font-medium text-foreground shadow-sm transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
            @click="emit('analyze')"
        >
            <Loader2 v-if="loading" class="size-4 animate-spin" />
            <Sparkles v-else class="size-4 text-primary" />
            {{ loading ? 'Анализируем...' : 'Загрузить анализ' }}
        </button>
        <p v-if="error" class="text-xs text-destructive">
            {{ error }}
        </p>
    </div>
</template>

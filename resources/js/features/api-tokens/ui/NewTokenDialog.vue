<script setup lang="ts">
import { Button } from '@shared/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@shared/ui/dialog';
import { useClipboard } from '@vueuse/core';
import { Check, Copy } from 'lucide-vue-next';

type Props = {
    token: string | null;
};

defineProps<Props>();

const isOpen = defineModel<boolean>('isOpen', { required: true });

const { copy, copied } = useClipboard();
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>API-токен создан</DialogTitle>
                <DialogDescription>
                    Скопируйте токен сейчас. Он больше не будет показан.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <div
                    class="flex items-stretch overflow-hidden rounded-lg border border-border"
                >
                    <input
                        type="text"
                        readonly
                        :value="token"
                        class="w-full bg-background p-3 font-mono text-sm text-foreground"
                        data-test="new-token-value"
                    />
                    <button
                        @click="copy(token || '')"
                        class="border-l border-border px-3 hover:bg-muted"
                        data-test="copy-token-button"
                    >
                        <Check v-if="copied" class="size-4 text-green-500" />
                        <Copy v-else class="size-4" />
                    </button>
                </div>

                <p class="text-sm text-amber-600 dark:text-amber-400">
                    Сохраните токен в безопасном месте. После закрытия этого
                    окна вы не сможете увидеть его снова.
                </p>
            </div>

            <DialogFooter>
                <DialogClose as-child>
                    <Button>Закрыть</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

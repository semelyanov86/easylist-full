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
import axios from 'axios';
import { Check, Copy, Loader2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { share } from '@/routes/jobs';

type Props = {
    jobId: number;
    uuid: string | null;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    shared: [uuid: string];
}>();

const isOpen = defineModel<boolean>('isOpen', { required: true });

const localUuid = ref<string | null>(props.uuid);
const loading = ref(false);

const shareUrl = computed((): string => {
    if (!localUuid.value) {
        return '';
    }

    return `${window.location.origin}/job/view/${localUuid.value}`;
});

const { copy, copied } = useClipboard();

const generateShareLink = async (): Promise<void> => {
    if (localUuid.value) {
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post<{ uuid: string }>(
            share(props.jobId).url,
        );
        localUuid.value = response.data.uuid;
        emit('shared', response.data.uuid);
    } finally {
        loading.value = false;
    }
};

watch(isOpen, (opened) => {
    if (opened) {
        generateShareLink();
    }
});

watch(
    () => props.uuid,
    (newUuid) => {
        localUuid.value = newUuid;
    },
);
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Поделиться вакансией</DialogTitle>
                <DialogDescription>
                    Скопируйте ссылку и отправьте её.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <div
                    v-if="loading"
                    class="flex items-center justify-center gap-2 py-4 text-sm text-muted-foreground"
                >
                    <Loader2 class="size-4 animate-spin" />
                    Генерация ссылки...
                </div>
                <div
                    v-else-if="shareUrl"
                    class="flex items-stretch overflow-hidden rounded-lg border border-border"
                >
                    <input
                        type="text"
                        readonly
                        :value="shareUrl"
                        class="w-full bg-background p-3 font-mono text-sm text-foreground"
                        data-test="share-url-input"
                    />
                    <button
                        class="border-l border-border px-3 hover:bg-muted"
                        data-test="copy-share-url-button"
                        @click="copy(shareUrl)"
                    >
                        <Check v-if="copied" class="size-4 text-green-500" />
                        <Copy v-else class="size-4" />
                    </button>
                </div>
            </div>

            <DialogFooter>
                <DialogClose as-child>
                    <Button>Закрыть</Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

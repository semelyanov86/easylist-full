<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import type { ApiToken } from '@shared/types';
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

import ApiTokenController from '@/actions/App/Http/Controllers/Settings/ApiTokenController';

type Props = {
    token: ApiToken | null;
};

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Dialog
        :open="token !== null"
        @update:open="
            (open: boolean) => {
                if (!open) emit('close');
            }
        "
    >
        <DialogContent>
            <Form
                v-if="token"
                v-bind="ApiTokenController.destroy.form(token.id)"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ processing }"
            >
                <DialogHeader>
                    <DialogTitle>Удалить API-токен?</DialogTitle>
                    <DialogDescription>
                        Токен «{{ token.name }}» будет удалён безвозвратно. Все
                        приложения, использующие этот токен, потеряют доступ к
                        API.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="emit('close')">
                            Отмена
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                        data-test="confirm-delete-token-button"
                    >
                        Удалить токен
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>

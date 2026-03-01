<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import { Send } from 'lucide-vue-next';

import JobCommentController from '@/actions/App/Http/Controllers/JobCommentController';

type Props = {
    jobId: number;
};

defineProps<Props>();
</script>

<template>
    <Form
        v-bind="JobCommentController.store.form(jobId)"
        reset-on-success
        :options="{ preserveScroll: true }"
        class="flex flex-col gap-2"
        v-slot="{ errors, processing }"
    >
        <textarea
            name="body"
            rows="2"
            placeholder="Написать комментарий..."
            required
            class="w-full min-w-0 resize-none rounded-lg border border-input bg-transparent px-3 py-2.5 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
        />
        <InputError :message="errors.body" />
        <div class="flex justify-end">
            <Button type="submit" size="sm" :disabled="processing">
                <Send class="size-3.5" />
                <span>Отправить</span>
            </Button>
        </div>
    </Form>
</template>

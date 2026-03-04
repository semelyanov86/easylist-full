<script setup lang="ts">
import type { CompanyReviews } from '@entities/company-info';
import { Badge } from '@shared/ui/badge';
import { Star, ThumbsDown, ThumbsUp } from 'lucide-vue-next';

type Props = {
    reviews: CompanyReviews;
};

defineProps<Props>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div class="flex items-center gap-2 border-b border-border px-5 py-3">
            <Star class="size-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-foreground">Отзывы</h3>
            <Badge
                v-if="reviews.source"
                variant="outline"
                class="ml-auto text-xs"
            >
                {{ reviews.source }}
            </Badge>
        </div>
        <div class="p-5">
            <div v-if="reviews.rating" class="mb-4 flex items-center gap-3">
                <div
                    class="flex size-12 items-center justify-center rounded-xl bg-muted"
                >
                    <span class="text-lg font-bold text-foreground">{{
                        reviews.rating
                    }}</span>
                </div>
                <div>
                    <div class="flex gap-0.5">
                        <Star
                            v-for="i in 5"
                            :key="i"
                            class="size-3.5"
                            :class="
                                i <= Math.round(reviews.rating)
                                    ? 'fill-amber-400 text-amber-400'
                                    : 'text-muted-foreground/20'
                            "
                        />
                    </div>
                    <p
                        v-if="reviews.total_reviews"
                        class="mt-0.5 text-xs text-muted-foreground"
                    >
                        {{ reviews.total_reviews }} отзывов
                    </p>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div v-if="reviews.pros && reviews.pros.length > 0">
                    <div
                        class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-green-600 dark:text-green-400"
                    >
                        <ThumbsUp class="size-3" />
                        Плюсы
                    </div>
                    <ul class="space-y-1">
                        <li
                            v-for="pro in reviews.pros"
                            :key="pro"
                            class="flex items-start gap-2 text-muted-foreground"
                        >
                            <span
                                class="mt-1.5 block size-1 shrink-0 rounded-full bg-green-500 dark:bg-green-400"
                            />
                            {{ pro }}
                        </li>
                    </ul>
                </div>
                <div v-if="reviews.cons && reviews.cons.length > 0">
                    <div
                        class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-red-600 dark:text-red-400"
                    >
                        <ThumbsDown class="size-3" />
                        Минусы
                    </div>
                    <ul class="space-y-1">
                        <li
                            v-for="con in reviews.cons"
                            :key="con"
                            class="flex items-start gap-2 text-muted-foreground"
                        >
                            <span
                                class="mt-1.5 block size-1 shrink-0 rounded-full bg-red-500 dark:bg-red-400"
                            />
                            {{ con }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

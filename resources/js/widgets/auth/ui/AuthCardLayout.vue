<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@shared/ui/card';
import { AuthHeader } from '@widgets/auth';
import { LandingFooter } from '@widgets/landing';

import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col bg-muted dark:bg-background">
        <AuthHeader />

        <main class="flex flex-1 items-center justify-center px-4 py-12">
            <div class="flex w-full max-w-md flex-col gap-6">
                <Link
                    :href="home()"
                    class="flex items-center gap-2 self-center font-medium"
                >
                    <img
                        src="/images/easylist.svg"
                        alt="Easylist"
                        class="h-12 w-12"
                    />
                </Link>

                <Card class="rounded-xl">
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <CardTitle class="text-xl">{{ title }}</CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </main>

        <LandingFooter :can-register="true" />
    </div>
</template>

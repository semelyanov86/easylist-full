<script setup lang="ts">
import type { JobPublicView } from '@entities/job';
import {
    PublicJobCompanyInfo,
    PublicJobContacts,
    PublicJobDescription,
    PublicJobHero,
} from '@widgets/job-public-view';
import { Head, Link } from '@inertiajs/vue3';

import { home } from '@/routes';

type Props = {
    job: JobPublicView;
};

defineProps<Props>();
</script>

<template>
    <div>
        <Head :title="job.title + ' — ' + job.company_name" />

        <div class="min-h-screen bg-background text-foreground">
            <header
                class="sticky top-0 z-50 border-b border-border bg-background/80 backdrop-blur-lg"
            >
                <div
                    class="mx-auto flex max-w-4xl items-center justify-between px-6 py-4"
                >
                    <Link :href="home.url()" class="flex shrink-0 items-center">
                        <img
                            src="/images/easylist-logo.svg"
                            alt="Easylist"
                            class="h-10"
                        />
                    </Link>
                </div>
            </header>

            <PublicJobHero :job="job" />

            <main class="mx-auto max-w-4xl space-y-8 px-6 py-8">
                <PublicJobDescription
                    v-if="job.description"
                    :description="job.description"
                />

                <PublicJobContacts
                    v-if="job.contacts.length > 0"
                    :contacts="job.contacts"
                />

                <PublicJobCompanyInfo
                    v-if="job.company_info"
                    :company-info="job.company_info"
                />
            </main>

            <footer class="border-t border-border px-6 py-8">
                <div
                    class="mx-auto max-w-4xl text-center text-sm text-muted-foreground"
                >
                    &copy; {{ new Date().getFullYear() }} Easylist. Все права
                    защищены.
                </div>
            </footer>
        </div>
    </div>
</template>

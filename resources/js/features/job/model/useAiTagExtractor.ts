import type { Skill } from '@entities/skill';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

import AiExtractJobTagsController from '@/actions/App/Http/Controllers/AiExtractJobTagsController';

/**
 * Композабл для извлечения тегов навыков через ИИ.
 */
export function useAiTagExtractor() {
    const loading = ref(false);
    const error = ref<string | null>(null);

    async function extractTags(jobId: number): Promise<Skill[] | null> {
        loading.value = true;
        error.value = null;

        try {
            const xsrfToken = document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

            const response = await fetch(
                AiExtractJobTagsController.url(jobId),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-XSRF-TOKEN': xsrfToken
                            ? decodeURIComponent(xsrfToken)
                            : '',
                    },
                    credentials: 'same-origin',
                },
            );

            if (!response.ok) {
                const data = (await response.json()) as {
                    message?: string;
                };
                error.value = data.message ?? 'Ошибка извлечения навыков';
                return null;
            }

            const data = (await response.json()) as {
                skills: Skill[];
            };

            router.reload({ only: ['job'] });

            return data.skills;
        } catch {
            error.value = 'Не удалось подключиться к сервису';
            return null;
        } finally {
            loading.value = false;
        }
    }

    return { loading, error, extractTags };
}

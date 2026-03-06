import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

import AiCoverLetterController from '@/actions/App/Http/Controllers/AiCoverLetterController';

/**
 * Композабл для генерации сопроводительного письма через ИИ.
 */
export function useCoverLetterGenerator() {
    const loading = ref(false);
    const error = ref<string | null>(null);

    function generate(jobId: number): void {
        loading.value = true;
        error.value = null;

        router.post(
            AiCoverLetterController.url(jobId),
            {},
            {
                preserveScroll: true,
                onFinish() {
                    loading.value = false;
                },
                onError(errors: Record<string, string>) {
                    error.value =
                        errors.cover_letter ??
                        'Не удалось сгенерировать сопроводительное письмо';
                },
            },
        );
    }

    return { loading, error, generate };
}

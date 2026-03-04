import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

import AiCompanyAnalyzerController from '@/actions/App/Http/Controllers/AiCompanyAnalyzerController';

/**
 * Композабл для запуска ИИ-анализа компании.
 */
export function useCompanyAnalysis() {
    const loading = ref(false);
    const error = ref<string | null>(null);

    function analyze(jobId: number): void {
        loading.value = true;
        error.value = null;

        router.post(
            AiCompanyAnalyzerController.url(jobId),
            {},
            {
                preserveScroll: true,
                onFinish() {
                    loading.value = false;
                },
                onError() {
                    error.value = 'Не удалось выполнить анализ компании';
                },
            },
        );
    }

    return { loading, error, analyze };
}

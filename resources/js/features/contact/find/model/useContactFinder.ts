import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

import AiContactFinderController from '@/actions/App/Http/Controllers/AiContactFinderController';

/**
 * Композабл для поиска контактов через ИИ.
 */
export function useContactFinder() {
    const loading = ref(false);
    const error = ref<string | null>(null);

    function find(jobId: number): void {
        loading.value = true;
        error.value = null;

        router.post(
            AiContactFinderController.url(jobId),
            {},
            {
                preserveScroll: true,
                onFinish() {
                    loading.value = false;
                },
                onError() {
                    error.value = 'Не удалось найти контакты';
                },
            },
        );
    }

    return { loading, error, find };
}

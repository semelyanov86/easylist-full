import { ref } from 'vue';

import AiFormatController from '@/actions/App/Http/Controllers/AiFormatController';

/**
 * Композабл для форматирования текста через ИИ.
 */
export function useAiFormat() {
    const loading = ref(false);
    const error = ref<string | null>(null);

    async function formatText(text: string): Promise<string | null> {
        if (!text.trim()) {
            return null;
        }

        loading.value = true;
        error.value = null;

        try {
            const xsrfToken = document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

            const response = await fetch(AiFormatController.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': xsrfToken
                        ? decodeURIComponent(xsrfToken)
                        : '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ text }),
            });

            if (!response.ok) {
                const data = (await response.json()) as {
                    message?: string;
                };
                error.value = data.message ?? 'Ошибка форматирования';
                return null;
            }

            const data = (await response.json()) as {
                formatted: string;
            };

            return data.formatted;
        } catch {
            error.value = 'Не удалось подключиться к сервису';
            return null;
        } finally {
            loading.value = false;
        }
    }

    return { loading, error, formatText };
}

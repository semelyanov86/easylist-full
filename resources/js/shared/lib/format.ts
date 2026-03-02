/**
 * Форматирование относительной даты на русском.
 */
export const formatRelativeDate = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMin = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMin < 1) {
        return 'только что';
    }
    if (diffMin < 60) {
        return `${diffMin} мин. назад`;
    }
    if (diffHours < 24) {
        return `${diffHours} ч. назад`;
    }
    if (diffDays < 7) {
        return `${diffDays} дн. назад`;
    }

    return date.toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
    });
};

/**
 * Форматирование размера файла в человекочитаемый вид.
 */
export const formatFileSize = (bytes: number | null): string => {
    if (bytes === null) {
        return '';
    }
    if (bytes < 1024) {
        return `${bytes} Б`;
    }
    if (bytes < 1048576) {
        return `${(bytes / 1024).toFixed(1)} КБ`;
    }

    return `${(bytes / 1048576).toFixed(1)} МБ`;
};

import { File, FileImage, FileText } from 'lucide-vue-next';

export type JobDocument = {
    id: number;
    title: string;
    description: string | null;
    category: string;
    category_label: string;
    file_path: string | null;
    original_filename: string | null;
    mime_type: string | null;
    file_size: number | null;
    external_url: string | null;
    author_name: string;
    created_at: string;
};

export const documentCategories: { value: string; label: string }[] = [
    { value: 'resume', label: 'Резюме' },
    { value: 'portfolio', label: 'Портфолио' },
    { value: 'recommendation', label: 'Рекомендация' },
    { value: 'article', label: 'Статья' },
    { value: 'certificate', label: 'Сертификат' },
    { value: 'cover_letter', label: 'Сопроводительное письмо' },
    { value: 'other', label: 'Прочее' },
];

export type CategoryStyle = {
    bg: string;
    text: string;
    border: string;
};

const categoryStyles: Record<string, CategoryStyle> = {
    resume: {
        bg: 'bg-status-blue/10 dark:bg-status-blue/15',
        text: 'text-status-blue',
        border: 'border-status-blue/20',
    },
    portfolio: {
        bg: 'bg-status-purple/10 dark:bg-status-purple/15',
        text: 'text-status-purple',
        border: 'border-status-purple/20',
    },
    recommendation: {
        bg: 'bg-status-green/10 dark:bg-status-green/15',
        text: 'text-status-green',
        border: 'border-status-green/20',
    },
    article: {
        bg: 'bg-status-amber/10 dark:bg-status-amber/15',
        text: 'text-status-amber',
        border: 'border-status-amber/20',
    },
    certificate: {
        bg: 'bg-status-cyan/10 dark:bg-status-cyan/15',
        text: 'text-status-cyan',
        border: 'border-status-cyan/20',
    },
    cover_letter: {
        bg: 'bg-status-green/10 dark:bg-status-green/15',
        text: 'text-status-green',
        border: 'border-status-green/20',
    },
    other: {
        bg: 'bg-muted',
        text: 'text-muted-foreground',
        border: 'border-border',
    },
};

const defaultStyle: CategoryStyle = {
    bg: 'bg-muted',
    text: 'text-muted-foreground',
    border: 'border-border',
};

export const getCategoryStyle = (category: string): CategoryStyle => {
    return categoryStyles[category] ?? defaultStyle;
};

export const getDocumentFileIcon = (mimeType: string | null) => {
    if (mimeType === null) {
        return File;
    }
    if (mimeType.startsWith('image/')) {
        return FileImage;
    }
    if (mimeType === 'application/pdf') {
        return FileText;
    }

    return File;
};

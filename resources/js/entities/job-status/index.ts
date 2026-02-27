export type JobStatus = {
    id: number;
    title: string;
    description: string | null;
    color: string;
    order_column: number;
};

export type StatusColorOption = {
    value: string;
    label: string;
};

export const STATUS_COLORS: StatusColorOption[] = [
    { value: 'gray', label: 'Серый' },
    { value: 'blue', label: 'Синий' },
    { value: 'green', label: 'Зелёный' },
    { value: 'red', label: 'Красный' },
    { value: 'amber', label: 'Янтарный' },
    { value: 'purple', label: 'Фиолетовый' },
    { value: 'pink', label: 'Розовый' },
    { value: 'cyan', label: 'Бирюзовый' },
    { value: 'indigo', label: 'Индиго' },
    { value: 'teal', label: 'Бирюзово-зелёный' },
    { value: 'orange', label: 'Оранжевый' },
    { value: 'lime', label: 'Лаймовый' },
    { value: 'rose', label: 'Алый' },
    { value: 'sky', label: 'Небесный' },
    { value: 'violet', label: 'Фиалковый' },
];

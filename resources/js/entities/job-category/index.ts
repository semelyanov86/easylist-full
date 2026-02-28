export type JobCategory = {
    id: number;
    title: string;
    description: string | null;
    currency: string;
    currency_symbol: string;
    order_column: number;
};

export const CURRENCIES = [
    { value: 'rub', label: 'Рубль (₽)', symbol: '₽' },
    { value: 'usd', label: 'Доллар ($)', symbol: '$' },
    { value: 'eur', label: 'Евро (€)', symbol: '€' },
] as const;

export function getCurrencySymbol(currency: string): string {
    const found = CURRENCIES.find((c) => c.value === currency);
    return found ? found.symbol : '₽';
}

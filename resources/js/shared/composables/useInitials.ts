export type UseInitialsReturn = {
    getInitials: (fullName?: string) => string;
};

export function getInitials(fullName?: string): string {
    if (!fullName) return '';

    const names = fullName.trim().split(' ');

    if (names.length === 0) return '';
    if (names.length === 1) return (names[0] ?? '').charAt(0).toUpperCase();

    const first = (names[0] ?? '').charAt(0);
    const last = (names[names.length - 1] ?? '').charAt(0);

    return `${first}${last}`.toUpperCase();
}

export function useInitials(): UseInitialsReturn {
    return { getInitials };
}

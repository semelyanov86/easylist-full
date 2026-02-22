import { type InertiaLinkProps, usePage } from '@inertiajs/vue3';
import { toUrl } from '@shared/lib/utils';
import { computed, type ComputedRef, type DeepReadonly, readonly } from 'vue';

export type UseCurrentUrlReturn = {
    currentUrl: DeepReadonly<ComputedRef<string>>;
    isCurrentUrl: (
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        currentUrl?: string,
    ) => boolean;
    whenCurrentUrl: <T, F = null>(
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        ifTrue: T,
        ifFalse?: F,
    ) => T | F;
};

const page = usePage();
const currentUrlReactive = computed(
    () => new URL(page.url, window?.location.origin).pathname,
);

export function useCurrentUrl(): UseCurrentUrlReturn {
    function isCurrentUrl(
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        currentUrl?: string,
    ) {
        const urlToCompare = currentUrl ?? currentUrlReactive.value;
        const urlString = toUrl(urlToCheck);

        if (!urlString.startsWith('http')) {
            return urlString === urlToCompare;
        }

        try {
            const absoluteUrl = new URL(urlString);

            return absoluteUrl.pathname === urlToCompare;
        } catch {
            return false;
        }
    }

    function whenCurrentUrl<T, F = null>(
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        ifTrue: T,
        ifFalse: F = null as unknown as F,
    ): T | F {
        return isCurrentUrl(urlToCheck) ? ifTrue : ifFalse;
    }

    return {
        currentUrl: readonly(currentUrlReactive),
        isCurrentUrl,
        whenCurrentUrl,
    };
}

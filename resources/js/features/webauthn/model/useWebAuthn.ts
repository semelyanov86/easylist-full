import { base64UrlToBuffer, bufferToBase64Url } from '@shared/lib/webauthn';
import { type Ref, ref } from 'vue';

export type UseWebAuthnReturn = {
    loading: Ref<boolean>;
    error: Ref<string | null>;
    register: (alias: string) => Promise<boolean>;
    authenticate: (intendedUrl?: string) => Promise<boolean>;
};

const getXsrfToken = (): string => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    return match?.[1] ? decodeURIComponent(match[1]) : '';
};

const jsonHeaders = (): HeadersInit => ({
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-XSRF-TOKEN': getXsrfToken(),
});

/**
 * Подготавливает PublicKeyCredentialCreationOptions из ответа сервера.
 */
const prepareCreationOptions = (
    options: PublicKeyCredentialCreationOptions,
): PublicKeyCredentialCreationOptions => ({
    ...options,
    challenge: base64UrlToBuffer(options.challenge as unknown as string),
    user: {
        ...options.user,
        id: base64UrlToBuffer(options.user.id as unknown as string),
    },
    excludeCredentials: options.excludeCredentials?.map((cred) => ({
        ...cred,
        id: base64UrlToBuffer(cred.id as unknown as string),
    })),
});

/**
 * Подготавливает PublicKeyCredentialRequestOptions из ответа сервера.
 */
const prepareRequestOptions = (
    options: PublicKeyCredentialRequestOptions,
): PublicKeyCredentialRequestOptions => ({
    ...options,
    challenge: base64UrlToBuffer(options.challenge as unknown as string),
    allowCredentials: options.allowCredentials?.map((cred) => ({
        ...cred,
        id: base64UrlToBuffer(cred.id as unknown as string),
    })),
});

export const useWebAuthn = (): UseWebAuthnReturn => {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    /**
     * Регистрация нового WebAuthn credential (attestation ceremony).
     */
    const register = async (alias: string): Promise<boolean> => {
        loading.value = true;
        error.value = null;

        try {
            // Шаг 1: Получаем challenge от сервера
            const optionsResponse = await fetch(
                '/webauthn/register/challenge',
                {
                    method: 'POST',
                    headers: jsonHeaders(),
                },
            );

            if (!optionsResponse.ok) {
                throw new Error('Не удалось получить параметры регистрации.');
            }

            const serverOptions = await optionsResponse.json();
            const creationOptions = prepareCreationOptions(serverOptions);

            // Шаг 2: Вызываем WebAuthn API браузера
            const credential = (await navigator.credentials.create({
                publicKey: creationOptions,
            })) as PublicKeyCredential | null;

            if (!credential) {
                throw new Error('Регистрация ключа отменена.');
            }

            const attestationResponse =
                credential.response as AuthenticatorAttestationResponse;

            // Шаг 3: Отправляем результат на сервер
            const storeResponse = await fetch('/webauthn/register', {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({
                    id: credential.id,
                    rawId: bufferToBase64Url(credential.rawId),
                    type: credential.type,
                    response: {
                        clientDataJSON: bufferToBase64Url(
                            attestationResponse.clientDataJSON,
                        ),
                        attestationObject: bufferToBase64Url(
                            attestationResponse.attestationObject,
                        ),
                    },
                    alias,
                }),
            });

            if (!storeResponse.ok) {
                const data = await storeResponse.json().catch(() => null);
                throw new Error(
                    data?.message ?? 'Не удалось сохранить ключ безопасности.',
                );
            }

            return true;
        } catch (e) {
            if (e instanceof DOMException && e.name === 'NotAllowedError') {
                error.value = 'Операция отменена пользователем.';
            } else {
                error.value =
                    e instanceof Error
                        ? e.message
                        : 'Произошла неизвестная ошибка.';
            }

            return false;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Аутентификация через WebAuthn (assertion ceremony).
     */
    const authenticate = async (intendedUrl?: string): Promise<boolean> => {
        loading.value = true;
        error.value = null;

        try {
            // Шаг 1: Получаем challenge от сервера
            const optionsResponse = await fetch('/webauthn/auth/challenge', {
                method: 'POST',
                headers: jsonHeaders(),
            });

            if (!optionsResponse.ok) {
                throw new Error(
                    'Не удалось получить параметры аутентификации.',
                );
            }

            const serverOptions = await optionsResponse.json();
            const requestOptions = prepareRequestOptions(serverOptions);

            // Шаг 2: Вызываем WebAuthn API браузера
            const assertion = (await navigator.credentials.get({
                publicKey: requestOptions,
            })) as PublicKeyCredential | null;

            if (!assertion) {
                throw new Error('Аутентификация отменена.');
            }

            const assertionResponse =
                assertion.response as AuthenticatorAssertionResponse;

            // Шаг 3: Отправляем результат на сервер
            const verifyResponse = await fetch('/webauthn/auth/verify', {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({
                    id: assertion.id,
                    rawId: bufferToBase64Url(assertion.rawId),
                    type: assertion.type,
                    response: {
                        clientDataJSON: bufferToBase64Url(
                            assertionResponse.clientDataJSON,
                        ),
                        authenticatorData: bufferToBase64Url(
                            assertionResponse.authenticatorData,
                        ),
                        signature: bufferToBase64Url(
                            assertionResponse.signature,
                        ),
                        userHandle: assertionResponse.userHandle
                            ? bufferToBase64Url(assertionResponse.userHandle)
                            : null,
                    },
                }),
            });

            if (!verifyResponse.ok) {
                const data = await verifyResponse.json().catch(() => null);
                throw new Error(
                    data?.message ?? 'Не удалось выполнить аутентификацию.',
                );
            }

            const result = await verifyResponse.json();

            // Перенаправляем на intended URL
            window.location.href =
                intendedUrl ?? result.redirect ?? '/dashboard';

            return true;
        } catch (e) {
            if (e instanceof DOMException && e.name === 'NotAllowedError') {
                error.value = 'Операция отменена пользователем.';
            } else {
                error.value =
                    e instanceof Error
                        ? e.message
                        : 'Произошла неизвестная ошибка.';
            }

            return false;
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        error,
        register,
        authenticate,
    };
};

<script setup lang="ts">
import Heading from '@shared/components/Heading.vue';
import type { ApiToken } from '@shared/types';
import { ref, watch } from 'vue';

import ApiTokenList from './ApiTokenList.vue';
import CreateApiTokenForm from './CreateApiTokenForm.vue';
import DeleteTokenDialog from './DeleteTokenDialog.vue';
import NewTokenDialog from './NewTokenDialog.vue';

type Props = {
    tokens: ApiToken[];
    newToken: string | null;
};

const props = defineProps<Props>();

const showNewTokenDialog = ref(false);
const tokenToDelete = ref<ApiToken | null>(null);

watch(
    () => props.newToken,
    (value) => {
        if (value) {
            showNewTokenDialog.value = true;
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="API-токены"
            description="Создание и управление персональными API-токенами для доступа к API"
        />

        <CreateApiTokenForm />

        <ApiTokenList :tokens="tokens" @delete="tokenToDelete = $event" />

        <NewTokenDialog
            v-model:is-open="showNewTokenDialog"
            :token="newToken"
        />

        <DeleteTokenDialog
            :token="tokenToDelete"
            @close="tokenToDelete = null"
        />
    </div>
</template>

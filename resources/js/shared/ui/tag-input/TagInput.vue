<script setup lang="ts">
import type { Skill } from '@entities/skill';
import { Badge } from '@shared/ui/badge';
import { X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type Props = {
    options: Skill[];
    defaultValue?: Skill[];
    name?: string;
    placeholder?: string;
    searchUrl?: string;
    createUrl?: string;
};

const props = withDefaults(defineProps<Props>(), {
    defaultValue: () => [],
    name: 'skill_ids[]',
    placeholder: 'Поиск навыков...',
    searchUrl: undefined,
    createUrl: undefined,
});

const selected = ref<Skill[]>([...props.defaultValue]);
const query = ref('');
const isOpen = ref(false);
const searchResults = ref<Skill[]>([]);
const isSearching = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const selectedIds = computed(() => new Set(selected.value.map((s) => s.id)));

const filteredOptions = computed<Skill[]>(() => {
    const source =
        query.value && searchResults.value.length > 0
            ? searchResults.value
            : props.options;

    return source.filter(
        (skill) =>
            !selectedIds.value.has(skill.id) &&
            skill.title.toLowerCase().includes(query.value.toLowerCase()),
    );
});

const hasExactMatch = computed(() => {
    const q = query.value.trim().toLowerCase();

    if (!q) {
        return true;
    }

    const allSkills = [
        ...props.options,
        ...searchResults.value,
        ...selected.value,
    ];

    return allSkills.some((s) => s.title.toLowerCase() === q);
});

const addSkill = (skill: Skill): void => {
    if (!selectedIds.value.has(skill.id)) {
        selected.value.push(skill);
    }

    query.value = '';
    isOpen.value = false;
};

const removeSkill = (skillId: number): void => {
    selected.value = selected.value.filter((s) => s.id !== skillId);
};

const createSkill = async (): Promise<void> => {
    if (!props.createUrl || !query.value.trim()) {
        return;
    }

    try {
        const response = await fetch(props.createUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie
                        .split('; ')
                        .find((row) => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1] ?? '',
                ),
            },
            body: JSON.stringify({ title: query.value.trim() }),
        });

        if (response.ok) {
            const skill = (await response.json()) as Skill;
            addSkill(skill);
        }
    } catch {
        // Игнорируем ошибки создания
    }
};

const searchSkills = async (q: string): Promise<void> => {
    if (!props.searchUrl || !q.trim()) {
        searchResults.value = [];

        return;
    }

    isSearching.value = true;

    try {
        const url = `${props.searchUrl}?q=${encodeURIComponent(q.trim())}`;
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
        });

        if (response.ok) {
            searchResults.value = (await response.json()) as Skill[];
        }
    } catch {
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

watch(query, (newQuery) => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    if (newQuery.trim()) {
        isOpen.value = true;
        debounceTimer = setTimeout(() => {
            searchSkills(newQuery);
        }, 300);
    } else {
        searchResults.value = [];
    }
});

const handleFocus = (): void => {
    isOpen.value = true;
};

const handleBlur = (): void => {
    setTimeout(() => {
        isOpen.value = false;
    }, 200);
};
</script>

<template>
    <div class="relative">
        <!-- Скрытые поля для Inertia Form -->
        <input
            v-for="skill in selected"
            :key="skill.id"
            type="hidden"
            :name="name"
            :value="skill.id"
        />

        <!-- Выбранные теги и поле ввода -->
        <div
            class="flex min-h-9 flex-wrap items-center gap-1.5 rounded-md border border-input bg-transparent px-3 py-1.5 shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50 dark:bg-input/30"
        >
            <Badge
                v-for="skill in selected"
                :key="skill.id"
                variant="secondary"
                class="gap-1 py-0.5 pr-1 text-xs"
            >
                {{ skill.title }}
                <button
                    type="button"
                    class="rounded-full p-0.5 transition-colors hover:bg-muted-foreground/20"
                    @click="removeSkill(skill.id)"
                >
                    <X class="size-3" />
                </button>
            </Badge>

            <input
                v-model="query"
                type="text"
                class="min-w-20 flex-1 border-0 bg-transparent p-0 text-sm outline-none placeholder:text-muted-foreground"
                :placeholder="selected.length === 0 ? placeholder : ''"
                @focus="handleFocus"
                @blur="handleBlur"
            />
        </div>

        <!-- Выпадающий список -->
        <div
            v-if="
                isOpen &&
                (filteredOptions.length > 0 ||
                    (query.trim() && !hasExactMatch && createUrl))
            "
            class="absolute z-50 mt-1 max-h-48 w-full overflow-auto rounded-md border border-border bg-popover p-1 shadow-md"
        >
            <button
                v-for="skill in filteredOptions"
                :key="skill.id"
                type="button"
                class="flex w-full items-center rounded-sm px-2 py-1.5 text-left text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                @mousedown.prevent="addSkill(skill)"
            >
                {{ skill.title }}
            </button>

            <button
                v-if="query.trim() && !hasExactMatch && createUrl"
                type="button"
                class="flex w-full items-center gap-1.5 rounded-sm px-2 py-1.5 text-left text-sm text-primary transition-colors hover:bg-accent"
                @mousedown.prevent="createSkill"
            >
                Создать «{{ query.trim() }}»
            </button>
        </div>
    </div>
</template>

import type { Folder } from '@entities/folder';
import type { ShoppingItem } from '@entities/shopping-item';

export type ShoppingList = {
    id: number;
    folder_id: number | null;
    name: string;
    icon: string | null;
    link: string | null;
    is_public: boolean;
    order_column: number;
    created_at: string | null;
    updated_at: string | null;
    folder?: Folder | null;
    items?: ShoppingItem[] | null;
};

export type ShoppingListWithItems = ShoppingList & {
    items: ShoppingItem[];
};

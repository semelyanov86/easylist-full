export type ShoppingItem = {
    id: number;
    shopping_list_id: number;
    name: string;
    description: string | null;
    quantity: number;
    quantity_type: string | null;
    price: number | null;
    is_starred: boolean;
    is_done: boolean;
    file: string | null;
    order_column: number;
    created_at: string | null;
    updated_at: string | null;
};

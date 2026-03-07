export type Folder = {
    id: number;
    name: string;
    icon: string | null;
    order_column: number;
    created_at: string | null;
    updated_at: string | null;
    lists_count?: number;
};

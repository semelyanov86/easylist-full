export type JobTask = {
    id: number;
    user_id: number;
    title: string;
    external_id: string | null;
    deadline: string | null;
    completed_at: string | null;
    created_at: string;
};

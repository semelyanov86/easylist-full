export type JobTask = {
    id: number;
    user_id: number;
    title: string;
    external_id: string | null;
    deadline: string | null;
    completed_at: string | null;
    created_at: string;
};

export type DashboardPendingTask = {
    id: number;
    title: string;
    deadline: string | null;
    job_id: number;
    job_title: string;
    job_company_name: string;
};

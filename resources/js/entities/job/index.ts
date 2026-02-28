import type { JobCategory } from '@entities/job-category';
import type { JobStatus } from '@entities/job-status';

export type Job = {
    id: number;
    title: string;
    company_name: string;
    location_city: string | null;
    salary: number | null;
    is_favorite: boolean;
    created_at: string;
    status: JobStatus;
    category: JobCategory;
};

export type JobFilters = {
    search: string | null;
    status_id: number | null;
    date_from: string | null;
    date_to: string | null;
    job_category_id: number | null;
    sort: string | null;
};

export type StatusTab = {
    id: number;
    title: string;
    color: string;
    count: number;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginatedJobs = {
    data: Job[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type JobsViewMode = 'table' | 'kanban';

export type KanbanColumn = {
    statusId: number;
    title: string;
    color: string;
    jobs: Job[];
};

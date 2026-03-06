import type { ActivityTimelineItem } from '@entities/activity';
import type { CompanyInfo, CompanyInfoDetails } from '@entities/company-info';
import type { Contact } from '@entities/contact';
import type { JobCategory } from '@entities/job-category';
import type { JobComment } from '@entities/job-comment';
import type { JobDocument } from '@entities/job-document';
import type { JobStatus } from '@entities/job-status';
import type { JobTask } from '@entities/job-task';
import type { Skill } from '@entities/skill';

export type Job = {
    id: number;
    uuid: string | null;
    title: string;
    company_name: string;
    description: string | null;
    job_url: string | null;
    location_city: string | null;
    salary: number | null;
    is_favorite: boolean;
    job_status_id: number;
    job_category_id: number;
    created_at: string;
    status: JobStatus;
    category: JobCategory;
    skills: Skill[];
};

export type JobFilters = {
    search: string | null;
    status_id: number | null;
    date_from: string | null;
    date_to: string | null;
    job_category_id: number | null;
    is_favorite: boolean | null;
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

export type JobShowTabId =
    | 'overview'
    | 'comments'
    | 'documents'
    | 'company'
    | 'contacts'
    | 'tasks';

export type JobShowTab = {
    id: JobShowTabId;
    title: string;
    enabled: boolean;
};

export type JobDetail = Job & {
    comments: JobComment[];
    documents: JobDocument[];
    activities: ActivityTimelineItem[];
    contacts: Contact[];
    tasks: JobTask[];
    company_info: CompanyInfo | null;
};

export type JobsViewMode = 'table' | 'kanban';

export type KanbanColumn = {
    statusId: number;
    title: string;
    color: string;
    jobs: Job[];
};

export type PublicContact = {
    first_name: string;
    last_name: string;
    position: string | null;
    city: string | null;
    email: string | null;
    phone: string | null;
    linkedin_url: string | null;
};

export type JobPublicView = {
    title: string;
    company_name: string;
    description: string | null;
    job_url: string | null;
    location_city: string | null;
    salary: number | null;
    currency_symbol: string | null;
    created_at: string;
    skills: Skill[];
    contacts: PublicContact[];
    company_info: CompanyInfoDetails | null;
};

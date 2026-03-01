export type ActivityTimelineItem = {
    id: number;
    description: string;
    event: string | null;
    causer_name: string | null;
    properties: Record<string, unknown>;
    changes: Record<string, unknown>;
    created_at: string;
};

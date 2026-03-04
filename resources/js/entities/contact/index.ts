export type Contact = {
    id: number;
    user_id: number;
    first_name: string;
    last_name: string;
    position: string | null;
    city: string | null;
    email: string | null;
    phone: string | null;
    description: string | null;
    linkedin_url: string | null;
    facebook_url: string | null;
    whatsapp_url: string | null;
    created_at: string;
};

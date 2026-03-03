export type CompanyReviews = {
    source: string | null;
    rating: number | null;
    total_reviews: number | null;
    pros: string[] | null;
    cons: string[] | null;
};

export type CompanyNewsItem = {
    title: string | null;
    date: string | null;
    url: string | null;
};

export type CompanyLinks = {
    website: string | null;
    glassdoor: string | null;
    kununu: string | null;
    linkedin: string | null;
};

export type CompanyInfoDetails = {
    overview: string | null;
    industry: string | null;
    founded: string | null;
    employees: string | null;
    revenue: string | null;
    funding: string | null;
    hq: string | null;
    local_office: string | null;
    tech_stack: string[] | null;
    reviews: CompanyReviews | null;
    recent_news: CompanyNewsItem[] | null;
    links: CompanyLinks | null;
};

export type CompanyInfo = {
    id: number;
    name: string;
    city: string | null;
    info: CompanyInfoDetails | null;
};

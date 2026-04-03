const BASE_PATH = "/api/v1";

export class ApiClient {
  private baseUrl: string;
  private token: string;
  private timeout: number;

  constructor(baseUrl: string, token: string, timeout = 30000) {
    this.baseUrl = baseUrl.replace(/\/+$/, "");
    this.token = token;
    this.timeout = timeout;
  }

  async get(path: string, query?: Record<string, string | number | boolean | undefined>): Promise<unknown> {
    const url = this.buildUrl(path, query);
    return this.request(url, { method: "GET" });
  }

  async post(path: string, data?: unknown): Promise<unknown> {
    return this.request(this.buildUrl(path), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: data !== undefined ? JSON.stringify(data) : undefined,
    });
  }

  async put(path: string, data?: unknown): Promise<unknown> {
    return this.request(this.buildUrl(path), {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: data !== undefined ? JSON.stringify(data) : undefined,
    });
  }

  async patch(path: string, data?: unknown): Promise<unknown> {
    return this.request(this.buildUrl(path), {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: data !== undefined ? JSON.stringify(data) : undefined,
    });
  }

  async delete(path: string): Promise<unknown> {
    return this.request(this.buildUrl(path), { method: "DELETE" });
  }

  private buildUrl(path: string, query?: Record<string, string | number | boolean | undefined>): string {
    const url = new URL(`${BASE_PATH}/${path.replace(/^\/+/, "")}`, this.baseUrl);
    if (query) {
      for (const [key, value] of Object.entries(query)) {
        if (value !== undefined && value !== null) {
          url.searchParams.set(key, String(value));
        }
      }
    }
    return url.toString();
  }

  private async request(url: string, init: RequestInit): Promise<unknown> {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), this.timeout);

    try {
      const response = await fetch(url, {
        ...init,
        signal: controller.signal,
        headers: {
          Accept: "application/vnd.api+json",
          Authorization: `Bearer ${this.token}`,
          ...init.headers,
        },
      });

      const text = await response.text();

      if (!response.ok) {
        return { error: true, status: response.status, body: text };
      }

      if (!text) {
        return { success: true, status: response.status };
      }

      return JSON.parse(text);
    } finally {
      clearTimeout(timer);
    }
  }
}

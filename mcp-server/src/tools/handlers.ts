import type { ApiClient } from "../api-client.js";

type Args = Record<string, unknown>;

function pick(args: Args, keys: string[]): Record<string, unknown> {
  const result: Record<string, unknown> = {};
  for (const key of keys) {
    if (args[key] !== undefined && args[key] !== null) {
      result[key] = args[key];
    }
  }
  return result;
}

function jobQuery(args: Args): Record<string, string | number | boolean | undefined> {
  return {
    include: args.include as string | undefined,
    "page[number]": args.page_number as number | undefined,
    "page[size]": args.page_size as number | undefined,
    "filter[search]": args.filter_search as string | undefined,
    "filter[status_id]": args.filter_status_id as number | undefined,
    "filter[job_category_id]": args.filter_job_category_id as number | undefined,
    "filter[is_favorite]": args.filter_is_favorite as boolean | undefined,
    "filter[date_from]": args.filter_date_from as string | undefined,
    "filter[date_to]": args.filter_date_to as string | undefined,
  };
}

export async function handleTool(api: ApiClient, name: string, args: Args): Promise<unknown> {
  switch (name) {
    // Auth
    case "get-me":
      return api.get("me");

    // Jobs
    case "list-jobs":
      return api.get("jobs", jobQuery(args));

    case "create-job":
      return api.post("jobs", pick(args, [
        "title", "company_name", "job_status_id", "job_category_id",
        "description", "job_url", "salary", "location_city", "skill_ids",
      ]));

    case "show-job":
      return api.get(`jobs/${args.job_id}`, { include: args.include as string | undefined });

    case "update-job":
      return api.put(`jobs/${args.job_id}`, pick(args, [
        "title", "company_name", "job_status_id", "job_category_id",
        "description", "job_url", "salary", "location_city", "skill_ids",
      ]));

    case "delete-job":
      return api.delete(`jobs/${args.job_id}`);

    case "move-job-status":
      return api.patch(`jobs/${args.job_id}/status`, { status_id: args.status_id });

    case "toggle-job-favorite":
      return api.patch(`jobs/${args.job_id}/favorite`);

    case "share-job":
      return api.post(`jobs/${args.job_id}/share`);

    // Job Categories
    case "list-job-categories":
      return api.get("job-categories");

    case "show-job-category":
      return api.get(`job-categories/${args.job_category_id}`);

    case "list-category-jobs":
      return api.get(`job-categories/${args.job_category_id}/jobs`, jobQuery(args));

    // Comments
    case "list-job-comments":
      return api.get(`jobs/${args.job_id}/comments`);

    case "create-job-comment":
      return api.post(`jobs/${args.job_id}/comments`, { body: args.body });

    // Documents
    case "list-job-documents":
      return api.get(`jobs/${args.job_id}/documents`);

    // Contacts
    case "list-job-contacts":
      return api.get(`jobs/${args.job_id}/contacts`);

    case "create-job-contact":
      return api.post(`jobs/${args.job_id}/contacts`, pick(args, [
        "first_name", "last_name", "position", "city", "email",
        "phone", "description", "linkedin_url", "facebook_url", "whatsapp_url",
      ]));

    case "delete-job-contact":
      return api.delete(`jobs/${args.job_id}/contacts/${args.contact_id}`);

    // Tasks
    case "list-pending-tasks":
      return api.get("tasks/pending");

    // Statistics
    case "get-statistics":
      return api.get("statistics", { funnel_category_id: args.funnel_category_id as number | undefined });

    // AI
    case "analyze-company":
      return api.post(`jobs/${args.job_id}/analyze-company`);

    case "find-contacts":
      return api.post(`jobs/${args.job_id}/find-contacts`);

    // Folders
    case "list-folders":
      return api.get("folders", { include: args.include as string | undefined });

    case "create-folder":
      return api.post("folders", {
        data: { type: "folders", attributes: pick(args, ["name", "icon"]) },
      });

    case "show-folder":
      return api.get(`folders/${args.folder_id}`, { include: args.include as string | undefined });

    case "update-folder":
      return api.patch(`folders/${args.folder_id}`, {
        data: {
          id: String(args.folder_id),
          type: "folders",
          attributes: pick(args, ["name", "icon", "order_column"]),
        },
      });

    case "delete-folder":
      return api.delete(`folders/${args.folder_id}`);

    // Shopping Lists
    case "list-shopping-lists":
      return api.get("lists", { include: args.include as string | undefined });

    case "create-shopping-list":
      return api.post("lists", {
        data: { type: "lists", attributes: pick(args, ["folder_id", "name", "icon", "is_public"]) },
      });

    case "show-shopping-list":
      return api.get(`lists/${args.list_id}`, { include: args.include as string | undefined });

    case "update-shopping-list":
      return api.patch(`lists/${args.list_id}`, {
        data: {
          id: String(args.list_id),
          type: "lists",
          attributes: pick(args, ["name", "icon", "folder_id", "is_public", "order_column"]),
        },
      });

    case "delete-shopping-list":
      return api.delete(`lists/${args.list_id}`);

    case "list-folder-shopping-lists":
      return api.get(`folders/${args.folder_id}/lists`);

    case "send-shopping-list-email":
      return api.post(`lists/${args.list_id}/email`, {
        data: { type: "emails", attributes: { email: args.email } },
      });

    // Shopping Items
    case "list-shopping-items":
      return api.get("items");

    case "create-shopping-item":
      return api.post("items", {
        data: {
          type: "items",
          attributes: pick(args, [
            "shopping_list_id", "name", "description", "quantity",
            "quantity_type", "price", "is_starred", "is_done",
          ]),
        },
      });

    case "show-shopping-item":
      return api.get(`items/${args.item_id}`, { include: args.include as string | undefined });

    case "update-shopping-item":
      return api.patch(`items/${args.item_id}`, {
        data: {
          id: String(args.item_id),
          type: "items",
          attributes: pick(args, [
            "name", "description", "quantity", "quantity_type",
            "price", "is_starred", "is_done", "order_column",
          ]),
        },
      });

    case "delete-shopping-item":
      return api.delete(`items/${args.item_id}`);

    case "list-shopping-list-items":
      return api.get(`lists/${args.list_id}/items`);

    case "delete-all-items":
      return api.delete(`lists/${args.list_id}/items`);

    case "uncross-all-items":
      return api.patch(`lists/${args.list_id}/items/undone`);

    default:
      throw new Error(`Unknown tool: ${name}`);
  }
}

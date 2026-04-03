import type { Tool } from "@modelcontextprotocol/sdk/types.js";

// Helper to build tool definition
function tool(name: string, description: string, properties: Record<string, object> = {}, required: string[] = []): Tool {
  return {
    name,
    description,
    inputSchema: {
      type: "object" as const,
      properties,
      ...(required.length > 0 ? { required } : {}),
    },
  };
}

const str = (desc: string) => ({ type: "string", description: desc });
const int = (desc: string) => ({ type: "integer", description: desc });
const bool = (desc: string) => ({ type: "boolean", description: desc });
const arr = (desc: string) => ({ type: "array", description: desc, items: { type: "integer" } });

export const tools: Tool[] = [
  // Auth
  tool("get-me", "Получить текущего авторизованного пользователя"),

  // Jobs
  tool("list-jobs", "Список вакансий с фильтрацией и пагинацией", {
    include: str("Связи через запятую: contacts, comments, documents, tasks, company-info"),
    page_number: int("Номер страницы (по умолчанию 1)"),
    page_size: int("Размер страницы (1-100, по умолчанию 15)"),
    filter_search: str("Поиск по title, company_name, location_city"),
    filter_status_id: int("Фильтр по статусу"),
    filter_job_category_id: int("Фильтр по категории"),
    filter_is_favorite: bool("Только избранные"),
    filter_date_from: str("Дата создания от (YYYY-MM-DD)"),
    filter_date_to: str("Дата создания до (YYYY-MM-DD)"),
  }),

  tool("create-job", "Создать новую вакансию", {
    title: str("Название вакансии"),
    company_name: str("Название компании"),
    job_status_id: int("ID статуса"),
    job_category_id: int("ID категории"),
    description: str("Описание вакансии"),
    job_url: str("URL вакансии"),
    salary: int("Зарплата"),
    location_city: str("Город"),
    skill_ids: arr("Массив ID навыков"),
  }, ["title", "company_name", "job_status_id", "job_category_id"]),

  tool("show-job", "Детальный просмотр вакансии по ID", {
    job_id: int("ID вакансии"),
    include: str("Связи через запятую: contacts, comments, documents, tasks, company-info"),
  }, ["job_id"]),

  tool("update-job", "Обновить вакансию", {
    job_id: int("ID вакансии"),
    title: str("Название вакансии"),
    company_name: str("Название компании"),
    job_status_id: int("ID статуса"),
    job_category_id: int("ID категории"),
    description: str("Описание вакансии"),
    job_url: str("URL вакансии"),
    salary: int("Зарплата"),
    location_city: str("Город"),
    skill_ids: arr("Массив ID навыков"),
  }, ["job_id", "title", "company_name", "job_status_id", "job_category_id"]),

  tool("delete-job", "Удалить вакансию (soft delete)", {
    job_id: int("ID вакансии"),
  }, ["job_id"]),

  tool("move-job-status", "Переместить вакансию в другой статус", {
    job_id: int("ID вакансии"),
    status_id: int("ID нового статуса"),
  }, ["job_id", "status_id"]),

  tool("toggle-job-favorite", "Переключить статус избранного у вакансии", {
    job_id: int("ID вакансии"),
  }, ["job_id"]),

  tool("share-job", "Сгенерировать публичную ссылку на вакансию", {
    job_id: int("ID вакансии"),
  }, ["job_id"]),

  // Job Categories
  tool("list-job-categories", "Получить все категории вакансий пользователя"),

  tool("show-job-category", "Получить категорию вакансий по ID", {
    job_category_id: int("ID категории"),
  }, ["job_category_id"]),

  tool("list-category-jobs", "Получить вакансии из определённой категории", {
    job_category_id: int("ID категории"),
    include: str("Связи через запятую: contacts, comments, documents, tasks, company-info"),
    page_number: int("Номер страницы"),
    page_size: int("Размер страницы (1-100)"),
    filter_search: str("Поиск по title, company_name, location_city"),
    filter_status_id: int("Фильтр по статусу"),
    filter_is_favorite: bool("Только избранные"),
    filter_date_from: str("Дата создания от (YYYY-MM-DD)"),
    filter_date_to: str("Дата создания до (YYYY-MM-DD)"),
  }, ["job_category_id"]),

  // Comments
  tool("list-job-comments", "Получить комментарии вакансии", {
    job_id: int("ID вакансии"),
  }, ["job_id"]),

  tool("create-job-comment", "Добавить комментарий к вакансии", {
    job_id: int("ID вакансии"),
    body: str("Текст комментария (макс. 5000 символов)"),
  }, ["job_id", "body"]),

  // Documents
  tool("list-job-documents", "Получить документы вакансии", {
    job_id: int("ID вакансии"),
  }, ["job_id"]),

  // Contacts
  tool("list-job-contacts", "Получить контакты вакансии", {
    job_id: int("ID вакансии"),
  }, ["job_id"]),

  tool("create-job-contact", "Добавить контакт к вакансии", {
    job_id: int("ID вакансии"),
    first_name: str("Имя"),
    last_name: str("Фамилия"),
    position: str("Должность"),
    city: str("Город"),
    email: str("Email"),
    phone: str("Телефон"),
    description: str("Описание"),
    linkedin_url: str("LinkedIn URL"),
    facebook_url: str("Facebook URL"),
    whatsapp_url: str("WhatsApp URL"),
  }, ["job_id", "first_name", "last_name"]),

  tool("delete-job-contact", "Удалить контакт вакансии", {
    job_id: int("ID вакансии"),
    contact_id: int("ID контакта"),
  }, ["job_id", "contact_id"]),

  // Tasks
  tool("list-pending-tasks", "Получить незавершённые задачи текущего пользователя"),

  // Statistics
  tool("get-statistics", "Получить статистику дашборда: активности, задачи, избранные вакансии, воронка, навыки", {
    funnel_category_id: int("Фильтр воронки по категории вакансий"),
  }),

  // AI
  tool("analyze-company", "Запустить ИИ-анализ компании (фоновый процесс, требует Premium)", {
    job_id: int("ID вакансии"),
  }, ["job_id"]),

  tool("find-contacts", "Запустить ИИ-поиск контактов для вакансии (фоновый процесс, требует Premium)", {
    job_id: int("ID вакансии"),
  }, ["job_id"]),

  // Folders
  tool("list-folders", "Получить все папки пользователя (для списков покупок)", {
    include: str("Допустимые значения: lists"),
  }),

  tool("create-folder", "Создать папку для списков покупок", {
    name: str("Название папки"),
    icon: str("Иконка папки"),
  }, ["name"]),

  tool("show-folder", "Показать папку по ID", {
    folder_id: int("ID папки"),
    include: str("Допустимые значения: lists"),
  }, ["folder_id"]),

  tool("update-folder", "Обновить папку", {
    folder_id: int("ID папки"),
    name: str("Название папки"),
    icon: str("Иконка папки"),
    order_column: int("Порядок сортировки"),
  }, ["folder_id"]),

  tool("delete-folder", "Удалить папку (каскадно удаляет списки и позиции)", {
    folder_id: int("ID папки"),
  }, ["folder_id"]),

  // Shopping Lists
  tool("list-shopping-lists", "Получить все списки покупок пользователя", {
    include: str("Допустимые значения: folder, items"),
  }),

  tool("create-shopping-list", "Создать список покупок", {
    folder_id: int("ID папки"),
    name: str("Название списка"),
    icon: str("Иконка списка"),
    is_public: bool("Публичный доступ"),
  }, ["folder_id", "name"]),

  tool("show-shopping-list", "Показать список покупок по ID", {
    list_id: int("ID списка"),
    include: str("Допустимые значения: folder, items"),
  }, ["list_id"]),

  tool("update-shopping-list", "Обновить список покупок", {
    list_id: int("ID списка"),
    name: str("Название списка"),
    icon: str("Иконка"),
    folder_id: int("ID папки"),
    is_public: bool("Публичный доступ"),
    order_column: int("Порядок сортировки"),
  }, ["list_id"]),

  tool("delete-shopping-list", "Удалить список покупок (каскадно удаляет позиции)", {
    list_id: int("ID списка"),
  }, ["list_id"]),

  tool("list-folder-shopping-lists", "Получить списки покупок из определённой папки", {
    folder_id: int("ID папки"),
  }, ["folder_id"]),

  tool("send-shopping-list-email", "Отправить список покупок по email", {
    list_id: int("ID списка"),
    email: str("Email получателя"),
  }, ["list_id", "email"]),

  // Shopping Items
  tool("list-shopping-items", "Получить все позиции покупок пользователя"),

  tool("create-shopping-item", "Создать позицию в списке покупок", {
    shopping_list_id: int("ID списка покупок"),
    name: str("Название позиции"),
    description: str("Описание"),
    quantity: int("Количество (мин. 1)"),
    quantity_type: str("Единица измерения (шт, кг, л и т.п.)"),
    price: int("Цена"),
    is_starred: bool("Помеченная позиция"),
    is_done: bool("Куплено"),
  }, ["shopping_list_id", "name"]),

  tool("show-shopping-item", "Показать позицию покупки по ID", {
    item_id: int("ID позиции"),
    include: str("Допустимые значения: list"),
  }, ["item_id"]),

  tool("update-shopping-item", "Обновить позицию покупки", {
    item_id: int("ID позиции"),
    name: str("Название"),
    description: str("Описание"),
    quantity: int("Количество"),
    quantity_type: str("Единица измерения"),
    price: int("Цена"),
    is_starred: bool("Помеченная"),
    is_done: bool("Куплено"),
    order_column: int("Порядок сортировки"),
  }, ["item_id"]),

  tool("delete-shopping-item", "Удалить позицию покупки", {
    item_id: int("ID позиции"),
  }, ["item_id"]),

  tool("list-shopping-list-items", "Получить позиции из определённого списка покупок", {
    list_id: int("ID списка покупок"),
  }, ["list_id"]),

  tool("delete-all-items", "Удалить все позиции из списка покупок", {
    list_id: int("ID списка покупок"),
  }, ["list_id"]),

  tool("uncross-all-items", "Снять все отметки (uncross) у позиций в списке покупок", {
    list_id: int("ID списка покупок"),
  }, ["list_id"]),
];

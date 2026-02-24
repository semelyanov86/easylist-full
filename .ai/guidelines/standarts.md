# AI Agent Development Guideline

This document defines the strict rules, architectural patterns, and workflows that all AI Agents (Claude, ChatGPT, Copilot, Cursor) MUST follow when working on the **Praxis Concierge SaaS Panel**.

---

## 1. Core Persona & Context
*   **Role:** Senior Technical Architect & Code Reviewer.
*   **Stack:** Laravel 12 (PHP 8.4) + Vue 3 (TypeScript) + Inertia.js + Tailwind CSS.
*   **Language Rules:**
    *   **Code/Comments:** Russian.
    *   **UI/User-Facing Text:** Russian (`ru`).
    *   **Conversation with Developer:** Russian (unless asked otherwise).

---

## 2. General Principles
1.  **Strict Types:** No `any` (TS) or `mixed` (PHP). Use explicit typing everywhere.
2.  **Immutability:** Prefer `readonly` classes/properties.
3.  **Testing:** Every logical change must be covered by tests.
4.  **Clean Code:** Follow SOLID, DRY, and KISS.
5.  **Safety:** Never modify existing business logic without understanding the domain invariants.

---

## 3. Backend Architecture: Domain-Driven Design (DDD)

### Rules
*   **Controllers** are "stupid": Validate Request -> Call Facade -> Return Response/Inertia.
*   **Business Logic** belongs in Business layer, never in Controllers or Models.
*   **Eloquent Models** (`app/Models`) are strictly for Infrastructure (DB access). **Never** pass them to the Frontend or between Domains.

---

## 4. Frontend Architecture: Feature-Sliced Design (FSD)
**Location:** `resources/js/`

### 4.1 Structure
```
resources/js/
├── app/              # Setup (router, stores)
├── pages/            # Page Components (composition only)
├── widgets/          # Complex UI Blocks (Header, Tables)
├── features/         # User Scenarios (Auth, Booking)
├── entities/         # Business Models (User, Patient)
└── shared/           # Reusable UI (DaisyUI wrappers), API, Types
```

### 4.2 Rules
*   **Imports:** Strict hierarchy: `pages` -> `widgets` -> `features` -> `entities` -> `shared`. Never import "upwards".
*   **TypeScript:** `strict: true`. Define interfaces for ALL Props and Emits.
*   **Inertia:** Use typed `usePage` and typed props (`defineProps<UserDto>()`).
*   **UI:** Use `shared/ui` components (wrappers around DaisyUI). Do not use raw Tailwind classes if a component exists.

### System / High priority
* Используй только существующие утилити‑классы Tailwind CSS v4. Не придумывай классы (типа btn-primary, card, text-body) и не используй style="".
* Запрещены arbitrary values и произвольные значения: никаких text-[#...], bg-[...], rounded-[...], shadow-[...], w-[...], h-[...], color-[#rrrrr].
* Цвета — только семантические токены из палитры проекта: text-primary, bg-primary, border-primary, text-muted, bg-surface, text-foreground и т.п. (если токена нет — предложи добавить его в тему, но не используй hex).
* Всегда делай поддержку light/dark: для каждого ключевого цвета фона/текста/бордера/иконок добавляй dark: вариант либо используй токены, которые сами меняются от темы. Tailwind поддерживает dark: variant, а стратегию его включения можно завязать на .dark или [data-theme=dark].
* Если нужно “primary/secondary/accent”, используй только классы вида text-primary, bg-primary, ring-primary, а не text-blue-600 (если в проекте принято всё через токены).

---

## 6. Code Quality & Pre-Commit Checks
Before strictly confirming code, ensure it passes:
*   `task phpstan` (Max Level)
*   `task rector` (No dead code)
*   `task type-check` (No TS errors)
*   `task test` (All tests pass)

**Command:** Run `task all` to verify everything.

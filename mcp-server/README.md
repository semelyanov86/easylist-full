# EasyList MCP Server

MCP server for [EasyList](https://easylist.sergeyem.ru) — manage jobs, shopping lists, contacts, and more through Claude.

## Quick Start

1. Download `easylist-mcp.cjs` from the [latest release](https://github.com/TODO/releases)
2. Add to your Claude config:

### Claude Desktop

Edit `~/Library/Application Support/Claude/claude_desktop_config.json` (macOS) or `%APPDATA%\Claude\claude_desktop_config.json` (Windows):

```json
{
  "mcpServers": {
    "easylist": {
      "command": "node",
      "args": ["/path/to/easylist-mcp.cjs"],
      "env": {
        "EASYLIST_API_TOKEN": "your-bearer-token-here"
      }
    }
  }
}
```

### Claude Code

Add to `.mcp.json` in your project or `~/.claude.json`:

```json
{
  "mcpServers": {
    "easylist": {
      "command": "node",
      "args": ["/path/to/easylist-mcp.cjs"],
      "env": {
        "EASYLIST_API_TOKEN": "your-bearer-token-here"
      }
    }
  }
}
```

## Environment Variables

| Variable | Required | Default | Description |
|---|---|---|---|
| `EASYLIST_API_TOKEN` | Yes | — | Your EasyList API bearer token |
| `EASYLIST_API_URL` | No | `https://easylist.sergeyem.ru` | API base URL |

## Available Tools (42)

### Auth
- `get-me` — Get current user

### Jobs
- `list-jobs` — List jobs with filtering and pagination
- `create-job` — Create a job
- `show-job` — Show job details
- `update-job` — Update a job
- `delete-job` — Delete a job (soft delete)
- `move-job-status` — Change job status
- `toggle-job-favorite` — Toggle favorite
- `share-job` — Generate public link

### Job Categories
- `list-job-categories` — List all categories
- `show-job-category` — Show category
- `list-category-jobs` — List jobs in category

### Comments & Documents
- `list-job-comments` / `create-job-comment`
- `list-job-documents`

### Contacts
- `list-job-contacts` / `create-job-contact` / `delete-job-contact`

### Tasks & Statistics
- `list-pending-tasks` — Pending tasks
- `get-statistics` — Dashboard statistics

### AI (Premium)
- `analyze-company` — AI company analysis
- `find-contacts` — AI contact search

### Folders
- `list-folders` / `create-folder` / `show-folder` / `update-folder` / `delete-folder`

### Shopping Lists
- `list-shopping-lists` / `create-shopping-list` / `show-shopping-list` / `update-shopping-list` / `delete-shopping-list`
- `list-folder-shopping-lists` / `send-shopping-list-email`

### Shopping Items
- `list-shopping-items` / `create-shopping-item` / `show-shopping-item` / `update-shopping-item` / `delete-shopping-item`
- `list-shopping-list-items` / `delete-all-items` / `uncross-all-items`

## Requirements

- Node.js >= 18

## Development

```bash
cd mcp-server
npm install
npm run build    # TypeScript → dist/
npm run bundle   # Single file → bundle/easylist-mcp.cjs
```

## License

MIT

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import { ApiClient } from "./api-client.js";
import { tools } from "./tools/definitions.js";
import { handleTool } from "./tools/handlers.js";

const EASYLIST_API_URL = process.env.EASYLIST_API_URL || "https://easylist.sergeyem.ru";
const EASYLIST_API_TOKEN = process.env.EASYLIST_API_TOKEN || "";

if (!EASYLIST_API_TOKEN) {
  console.error("Error: EASYLIST_API_TOKEN environment variable is required.");
  console.error("Set it in your MCP server configuration:");
  console.error('  "env": { "EASYLIST_API_TOKEN": "your-bearer-token" }');
  process.exit(1);
}

const api = new ApiClient(EASYLIST_API_URL, EASYLIST_API_TOKEN);

const server = new Server(
  { name: "easylist", version: "1.0.0" },
  { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => ({
  tools,
}));

server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;
  try {
    const result = await handleTool(api, name, args ?? {});
    return {
      content: [{ type: "text" as const, text: JSON.stringify(result, null, 2) }],
    };
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return {
      content: [{ type: "text" as const, text: `Error: ${message}` }],
      isError: true,
    };
  }
});

async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
}

main().catch((error) => {
  console.error("Fatal error:", error);
  process.exit(1);
});

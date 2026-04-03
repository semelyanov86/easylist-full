import { createServer } from "node:http";

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StreamableHTTPServerTransport } from "@modelcontextprotocol/sdk/server/streamableHttp.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import { ApiClient } from "./api-client.js";
import { tools } from "./tools/definitions.js";
import { handleTool } from "./tools/handlers.js";

const EASYLIST_API_URL = process.env.EASYLIST_API_URL || "https://easylist.sergeyem.ru";
const EASYLIST_API_TOKEN = process.env.EASYLIST_API_TOKEN || "";
const MCP_SECRET = process.env.MCP_SECRET || "";
const PORT = parseInt(process.env.MCP_PORT || "3100", 10);

if (!EASYLIST_API_TOKEN) {
  console.error("Error: EASYLIST_API_TOKEN environment variable is required.");
  process.exit(1);
}

const api = new ApiClient(EASYLIST_API_URL, EASYLIST_API_TOKEN);

function createMcpServer(): Server {
  const server = new Server(
    { name: "easylist", version: "1.0.0" },
    { capabilities: { tools: {} } }
  );

  server.setRequestHandler(ListToolsRequestSchema, async () => ({ tools }));

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

  return server;
}

const httpServer = createServer(async (req, res) => {
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, DELETE, OPTIONS");
  res.setHeader("Access-Control-Allow-Headers", "Content-Type, Accept, Mcp-Session-Id");
  res.setHeader("Access-Control-Expose-Headers", "Mcp-Session-Id");

  if (req.method === "OPTIONS") {
    res.writeHead(204);
    res.end();
    return;
  }

  const url = req.url ?? "/";

  // Проверка секретного ключа: запрос должен содержать Bearer token или секретный path
  if (MCP_SECRET) {
    const authHeader = req.headers["authorization"] ?? "";
    const hasValidBearer = authHeader === `Bearer ${MCP_SECRET}`;
    const hasValidPath = url.startsWith(`/${MCP_SECRET}`);

    if (!hasValidBearer && !hasValidPath) {
      res.writeHead(403, { "Content-Type": "application/json" });
      res.end(JSON.stringify({ error: "Forbidden" }));
      return;
    }

    // Стрипаем секрет из path если он был в URL
    if (hasValidPath) {
      req.url = url.slice(`/${MCP_SECRET}`.length) || "/";
    }
  }

  if ((req.url ?? "/") === "/health") {
    res.writeHead(200, { "Content-Type": "application/json" });
    res.end(JSON.stringify({ status: "ok" }));
    return;
  }

  const resolvedUrl = req.url ?? "/";
  if (resolvedUrl !== "/mcp" && resolvedUrl !== "/") {
    res.writeHead(404);
    res.end("Not found");
    return;
  }

  // Stateless: каждый запрос — новый transport + server
  const transport = new StreamableHTTPServerTransport({
    sessionIdGenerator: undefined,
  });

  const server = createMcpServer();
  await server.connect(transport);
  await transport.handleRequest(req, res);
  await server.close();
});

httpServer.listen(PORT, () => {
  console.log(`EasyList MCP server running on http://0.0.0.0:${PORT}/mcp`);
});

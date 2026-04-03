import { build } from "esbuild";
import { writeFileSync, readFileSync } from "fs";

await build({
  entryPoints: ["src/index.ts"],
  bundle: true,
  platform: "node",
  target: "node18",
  format: "cjs",
  outfile: "bundle/easylist-mcp.cjs",
  minify: true,
});

const content = readFileSync("bundle/easylist-mcp.cjs", "utf-8");
writeFileSync("bundle/easylist-mcp.cjs", `#!/usr/bin/env node\n${content}`);
console.log("Bundled → bundle/easylist-mcp.cjs");

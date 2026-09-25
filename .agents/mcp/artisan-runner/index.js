// .agents/mcp/artisan-runner/index.js
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { CallToolRequestSchema, ListToolsRequestSchema } from "@modelcontextprotocol/sdk/types.js";
import { spawn } from "child_process";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const defaultProjectRoot = path.resolve(__dirname, "../../..");

const server = new Server(
    { name: "laravel-artisan-runner", version: "1.0.0" },
    { capabilities: { tools: {} } }
);

function getProjectRoot() {
    return fs.existsSync(path.resolve(process.cwd(), "artisan"))
        ? process.cwd()
        : defaultProjectRoot;
}

function runArtisan(commandArgs, projectRoot) {
    return new Promise((resolve) => {
        const artisanPath = path.resolve(projectRoot, "artisan");
        if (!fs.existsSync(artisanPath)) {
            resolve({
                success: false,
                output: `Erro: arquivo artisan não encontrado em ${artisanPath}`
            });
            return;
        }

        const args = [artisanPath, ...commandArgs];
        const child = spawn("php", args, {
            cwd: projectRoot,
            env: { ...process.env, COLUMNS: "120" },
            shell: process.platform === "win32"
        });

        let stdout = "";
        let stderr = "";

        child.stdout.on("data", (data) => {
            stdout += data.toString();
        });

        child.stderr.on("data", (data) => {
            stderr += data.toString();
        });

        child.on("close", (code) => {
            const fullOutput = (stdout + (stderr ? `\n--- STDERR ---\n${stderr}` : "")).trim();
            resolve({
                success: code === 0,
                code,
                output: fullOutput || (code === 0 ? "Comando executado com sucesso (sem saída)." : `Falha na execução (código ${code}).`)
            });
        });

        child.on("error", (err) => {
            resolve({
                success: false,
                output: `Erro ao iniciar processo PHP: ${err.message}`
            });
        });
    });
}

server.setRequestHandler(ListToolsRequestSchema, async () => {
    return {
        tools: [
            {
                name: "artisan_run",
                description: "Executa comandos do Laravel Artisan na raiz do projeto (ex: 'test', 'esus:inspect-schema', 'migrate:status', 'route:list', etc.).",
                inputSchema: {
                    type: "object",
                    properties: {
                        command: {
                            type: "string",
                            description: "Nome do comando Artisan (ex: 'migrate:status', 'route:list', 'cache:clear', 'test')"
                        },
                        parameters: {
                            type: "array",
                            items: { type: "string" },
                            description: "Parâmetros ou flags adicionais opcionais (ex: ['--filter=C4IndicatorTest'])"
                        }
                    },
                    required: ["command"]
                }
            },
            {
                name: "artisan_test",
                description: "Executa a suíte de testes do Laravel (php artisan test), com suporte a filtro de testes.",
                inputSchema: {
                    type: "object",
                    properties: {
                        filter: {
                            type: "string",
                            description: "Nome do teste ou classe de teste para filtrar (ex: 'C4IndicatorTest')"
                        }
                    }
                }
            },
            {
                name: "artisan_inspect_schema",
                description: "Executa a inspeção do schema e-SUS interna da aplicação (php artisan esus:inspect-schema).",
                inputSchema: {
                    type: "object",
                    properties: {}
                }
            }
        ]
    };
});

server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const { name, arguments: args = {} } = request.params;
    const projectRoot = getProjectRoot();

    if (name === "artisan_run") {
        const cmd = args.command.trim();
        const extra = Array.isArray(args.parameters) ? args.parameters : [];
        const result = await runArtisan([cmd, ...extra], projectRoot);
        return {
            content: [{ type: "text", text: result.output }],
            isError: !result.success
        };
    }

    if (name === "artisan_test") {
        const testArgs = ["test"];
        if (args.filter) {
            testArgs.push(`--filter=${args.filter}`);
        }
        const result = await runArtisan(testArgs, projectRoot);
        return {
            content: [{ type: "text", text: result.output }],
            isError: !result.success
        };
    }

    if (name === "artisan_inspect_schema") {
        const result = await runArtisan(["esus:inspect-schema"], projectRoot);
        return {
            content: [{ type: "text", text: result.output }],
            isError: !result.success
        };
    }

    throw new Error(`Ferramenta desconhecida: ${name}`);
});

const transport = new StdioServerTransport();
await server.connect(transport);

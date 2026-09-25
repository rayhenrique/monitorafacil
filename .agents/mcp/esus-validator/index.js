// .agents/mcp/esus-validator/index.js
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { CallToolRequestSchema, ListToolsRequestSchema } from "@modelcontextprotocol/sdk/types.js";
import fs from "fs";
import path from "path";

const server = new Server(
    { name: "esus-validator", version: "1.0.0" },
    { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => {
    return {
        tools: [
            {
                name: "inspect_csv_headers_and_sample",
                description: "Lê o cabeçalho e as primeiras linhas de relatórios CSV do SIAPS ou exportações do e-SUS",
                inputSchema: {
                    type: "object",
                    properties: {
                        filepath: { type: "string", description: "Caminho relativo do CSV a partir da raiz do projeto" },
                        limit: { type: "number", default: 5 }
                    },
                    required: ["filepath"]
                }
            },
            {
                name: "validate_esus_xml_structure",
                description: "Inspeciona nós principais de arquivos XML e-SUS APS para checagem de conformidade de tags",
                inputSchema: {
                    type: "object",
                    properties: {
                        filepath: { type: "string", description: "Caminho relativo do arquivo XML" }
                    },
                    required: ["filepath"]
                }
            }
        ]
    };
});

server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const { name, arguments: args } = request.params;
    const projectRoot = process.cwd();

    if (name === "inspect_csv_headers_and_sample") {
        const fullPath = path.resolve(projectRoot, args.filepath);
        if (!fs.existsSync(fullPath)) {
            throw new Error(`Arquivo não encontrado: ${args.filepath}`);
        }
        const lines = fs.readFileSync(fullPath, "utf-8").split("\n").filter(l => l.trim() !== "");
        const header = lines[0];
        const samples = lines.slice(1, (args.limit || 5) + 1);
        return {
            content: [{ type: "text", text: JSON.stringify({ header, samples }, null, 2) }]
        };
    }

    if (name === "validate_esus_xml_structure") {
        const fullPath = path.resolve(projectRoot, args.filepath);
        if (!fs.existsSync(fullPath)) {
            throw new Error(`Arquivo não encontrado: ${args.filepath}`);
        }
        const content = fs.readFileSync(fullPath, "utf-8").slice(0, 1500); // Primeiros 1.5kb para preview da estrutura
        return {
            content: [{ type: "text", text: `Estrutura inicial do arquivo:\n${content}` }]
        };
    }

    throw new Error(`Ferramenta desconhecida: ${name}`);
});

const transport = new StdioServerTransport();
await server.connect(transport);
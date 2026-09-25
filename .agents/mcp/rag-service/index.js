// .agents/mcp/rag-service/index.js
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { CallToolRequestSchema, ListToolsRequestSchema } from "@modelcontextprotocol/sdk/types.js";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { searchApsRules, searchDbSchema } from "../../rag/vector_engine.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const defaultProjectRoot = path.resolve(__dirname, "../../..");

function getProjectRoot() {
    return fs.existsSync(path.resolve(process.cwd(), "artisan"))
        ? process.cwd()
        : defaultProjectRoot;
}

let cachedKnowledgeStore = null;

function loadKnowledgeStore() {
    if (cachedKnowledgeStore) {
        return cachedKnowledgeStore;
    }
    const root = getProjectRoot();
    const storePath = path.resolve(root, ".agents/rag/storage/knowledge_store.json");

    if (!fs.existsSync(storePath)) {
        throw new Error(
            `A base de conhecimento RAG não foi encontrada em: ${storePath}. Execute 'node .agents/rag/ingest.js' primeiro.`
        );
    }

    const rawData = fs.readFileSync(storePath, "utf-8");
    cachedKnowledgeStore = JSON.parse(rawData);
    return cachedKnowledgeStore;
}

const server = new Server(
    { name: "rag-service", version: "1.0.0" },
    { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => {
    return {
        tools: [
            {
                name: "search_aps_rules",
                description:
                    "Procura trechos normativos exatos da APS (Notas Metodológicas C1 a C7, NT 08/2026 CVAT e NT 30/2025 Vínculo) com filtragem estrita por indicador (C1, C2, C3, C4, C5, C6, C7, CVAT ou GERAL) e tipo de conteúdo (criterio_inclusao, criterio_exclusao, boas_praticas, codigos_cid_ciap, profissionais_validos, periodo_avaliacao, metodologia_calculo) para evitar contaminação entre regras.",
                inputSchema: {
                    type: "object",
                    properties: {
                        query: {
                            type: "string",
                            description: "Pergunta ou termos de busca normativa (ex: 'códigos CIAP CID diabetes ativo', 'doses vacinas 2 anos', 'DUM DPP semanas gestação')"
                        },
                        indicador: {
                            type: "string",
                            description: "Indicador específico para filtrar estritamente e evitar contaminação: 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'CVAT' ou 'GERAL'",
                            enum: ["C1", "C2", "C3", "C4", "C5", "C6", "C7", "CVAT", "GERAL"]
                        },
                        tipo_conteudo: {
                            type: "string",
                            description: "Tipo de conteúdo normativo desejado",
                            enum: [
                                "criterio_inclusao",
                                "criterio_exclusao",
                                "boas_praticas",
                                "codigos_cid_ciap",
                                "profissionais_validos",
                                "periodo_avaliacao",
                                "metodologia_calculo"
                            ]
                        },
                        limit: {
                            type: "number",
                            description: "Número máximo de trechos retornados (default: 5)",
                            default: 5
                        }
                    },
                    required: ["query"]
                }
            },
            {
                name: "search_db_schema",
                description:
                    "Procura definições de tabelas, campos, colunas, tipos, índices e relacionamentos internos do Monitora Fácil no DATABASE-SCHEMA.md e nas migrations de database/migrations/.",
                inputSchema: {
                    type: "object",
                    properties: {
                        query: {
                            type: "string",
                            description: "Termos da tabela ou modelagem (ex: 'c4_nominal_diabetics', 'cvat_team_evaluations', 'tabela de coorte C3', 'consolidation_teams')"
                        },
                        indicador: {
                            type: "string",
                            description: "Opcional: filtrar por indicador associado à tabela ('C1'..'C7', 'CVAT', 'GERAL')",
                            enum: ["C1", "C2", "C3", "C4", "C5", "C6", "C7", "CVAT", "GERAL"]
                        },
                        limit: {
                            type: "number",
                            description: "Número máximo de trechos retornados (default: 5)",
                            default: 5
                        }
                    },
                    required: ["query"]
                }
            }
        ]
    };
});

server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const { name, arguments: args } = request.params;
    const store = loadKnowledgeStore();

    if (name === "search_aps_rules") {
        const query = args.query;
        const indicador = args.indicador || null;
        const tipo_conteudo = args.tipo_conteudo || null;
        const limit = args.limit || 5;

        const results = searchApsRules(query, store, {
            indicador,
            tipo_conteudo,
            limit
        });

        if (results.length === 0) {
            return {
                content: [
                    {
                        type: "text",
                        text: `Nenhum trecho normativo encontrado para a busca: "${query}"${indicador ? ` no indicador ${indicador}` : ""}.`
                    }
                ]
            };
        }

        const formatted = results.map((r, i) => {
            const pageStr = r.pagina ? ` (Página ${r.pagina})` : "";
            return `### [Resultado ${i + 1}] ${r.secao}\n- **Indicador:** ${r.indicador} | **Tema:** ${r.tema} | **Tipo:** ${r.tipo_conteudo}\n- **Fonte:** ${r.fonte}${pageStr}\n- **Relevância Híbrida:** ${(r.score * 100).toFixed(1)}% (Vetorial: ${(r.vector_score * 100).toFixed(1)}% / Léxica: ${(r.lexical_score * 100).toFixed(1)}%)\n\n> ${r.trecho.replace(/\n/g, "\n> ")}\n`;
        }).join("\n---\n\n");

        return {
            content: [{ type: "text", text: formatted }]
        };
    }

    if (name === "search_db_schema") {
        const query = args.query;
        const indicador = args.indicador || null;
        const limit = args.limit || 5;

        const results = searchDbSchema(query, store, {
            indicador,
            limit
        });

        if (results.length === 0) {
            return {
                content: [
                    {
                        type: "text",
                        text: `Nenhuma definição de esquema ou tabela encontrada para: "${query}".`
                    }
                ]
            };
        }

        const formatted = results.map((r, i) => {
            return `### [Esquema ${i + 1}] ${r.secao}\n- **Indicador / Módulo:** ${r.indicador}\n- **Fonte:** ${r.fonte}\n- **Relevância Híbrida:** ${(r.score * 100).toFixed(1)}%\n\n\`\`\`sql\n${r.trecho}\n\`\`\`\n`;
        }).join("\n---\n\n");

        return {
            content: [{ type: "text", text: formatted }]
        };
    }

    throw new Error(`Ferramenta desconhecida: ${name}`);
});

const transport = new StdioServerTransport();
await server.connect(transport);

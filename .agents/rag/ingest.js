// .agents/rag/ingest.js
import fs from "fs";
import path from "path";
import { execSync } from "child_process";
import { fileURLToPath } from "url";
import { generateEmbedding, buildBm25Index } from "./vector_engine.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, "../..");
const storageDir = path.resolve(__dirname, "storage");

if (!fs.existsSync(storageDir)) {
    fs.mkdirSync(storageDir, { recursive: true });
}

console.log("🚀 Iniciando ingestão do RAG local para o Monitora Fácil...");
console.log(`📁 Raiz do projeto: ${projectRoot}`);

// 1. Extração de PDFs via script Python
console.log("\n📄 [1/4] Extraindo texto estruturado dos PDFs em importacao/referencia/...");
let pdfData = [];
try {
    const pythonScript = path.resolve(__dirname, "extract_pdfs.py");
    const output = execSync(`python "${pythonScript}"`, {
        cwd: projectRoot,
        encoding: "utf-8",
        maxBuffer: 50 * 1024 * 1024
    });
    pdfData = JSON.parse(output);
    console.log(`✅ ${pdfData.length} arquivos PDF extraídos com sucesso.`);
} catch (error) {
    console.error("❌ Erro ao extrair PDFs via Python:", error.message);
    process.exit(1);
}

// Mapeamento de metadados por PDF
function getPdfMetadata(filename) {
    const fn = filename.toLowerCase();
    if (fn.includes("c1")) {
        return { indicador: "C1", tema: "acesso", defaultTitulo: "Nota Metodológica C1 - Mais Acesso" };
    }
    if (fn.includes("c2")) {
        return { indicador: "C2", tema: "cuidado_infantil", defaultTitulo: "Nota Metodológica C2 - Desenvolvimento Infantil" };
    }
    if (fn.includes("c3")) {
        return { indicador: "C3", tema: "gestacao_puerperio", defaultTitulo: "Nota Metodológica C3 - Gestação e Puerpério" };
    }
    if (fn.includes("c4")) {
        return { indicador: "C4", tema: "diabetes", defaultTitulo: "Nota Metodológica C4 - Pessoa com Diabetes" };
    }
    if (fn.includes("c5")) {
        return { indicador: "C5", tema: "hipertensao", defaultTitulo: "Nota Metodológica C5 - Pessoa com Hipertensão" };
    }
    if (fn.includes("c6")) {
        return { indicador: "C6", tema: "idoso", defaultTitulo: "Nota Metodológica C6 - Pessoa Idosa" };
    }
    if (fn.includes("c7")) {
        return { indicador: "C7", tema: "citopatologico_cancer", defaultTitulo: "Nota Metodológica C7 - Prevenção de Câncer" };
    }
    if (fn.includes("cvat") || fn.includes("nt_06")) {
        return { indicador: "CVAT", tema: "avaliacao_territorial_qualidade", defaultTitulo: "Nota Técnica 8/2026 - Avaliação Quadrimestral e CVAT" };
    }
    if (fn.includes("nota-tecnica-no-30-2025")) {
        return { indicador: "CVAT", tema: "vinculo_acompanhamento_territorial", defaultTitulo: "Nota Técnica 30/2025 - Vínculo e Acompanhamento Territorial" };
    }
    return { indicador: "GERAL", tema: "aps_normativa", defaultTitulo: filename };
}

// Detecta o tipo_conteudo com base no cabeçalho e termos do trecho
function detectContentType(header, text) {
    const combined = `${header} ${text}`.toLowerCase();
    
    if (combined.includes("critério de inclusão") || combined.includes("criterio de inclusao") ||
        combined.includes("são consideradas no denominador") || combined.includes("população-alvo") ||
        combined.includes("denominador:") || combined.includes("definição de")) {
        return "criterio_inclusao";
    }
    if (combined.includes("critério de exclusão") || combined.includes("criterio de exclusao") ||
        combined.includes("não serão contabilizadas") || combined.includes("excluídos") ||
        combined.includes("condições resolvidas") || combined.includes("óbito") ||
        combined.includes("interrupção do acompanhamento")) {
        return "criterio_exclusao";
    }
    if (combined.includes("quadro 01") || combined.includes("quadro 1") ||
        combined.includes("boas práticas") || combined.includes("boas praticas") ||
        combined.includes("prática a") || combined.includes("pratica a") ||
        combined.includes("pontuação") || combined.includes("peso atribuído") ||
        combined.includes("intervalo mínimo") || combined.includes("intervalo minimo")) {
        return "boas_praticas";
    }
    if (combined.includes("quadro 02") || combined.includes("quadro 2") ||
        combined.includes("ciap") || combined.includes("cid") || combined.includes("sigtap") ||
        combined.includes("código") || combined.includes("imunobiológico") || combined.includes("vacina") ||
        combined.includes("procedimento")) {
        return "codigos_cid_ciap";
    }
    if (combined.includes("cbo") || combined.includes("profissional") ||
        combined.includes("médico") || combined.includes("enfermeiro") ||
        combined.includes("cirurgião-dentista") || combined.includes("agente comunitário")) {
        return "profissionais_validos";
    }
    if (combined.includes("período de monitoramento") || combined.includes("período de avaliação") ||
        combined.includes("quadrimestre") || combined.includes("competência") ||
        combined.includes("janela") || combined.includes("semanas máximas") || combined.includes("42 dias")) {
        return "periodo_avaliacao";
    }
    if (combined.includes("fórmula") || combined.includes("cálculo") ||
        combined.includes("numerador:") || combined.includes("nota final") ||
        combined.includes("faixas de classificação") || combined.includes("incentivo")) {
        return "metodologia_calculo";
    }
    return "metodologia_calculo";
}

const chunks = [];
let chunkIdCounter = 1;

// Segmentador semântico de PDFs (por seções e parágrafos com target 500-800 tokens / 1500-2500 chars)
for (const file of pdfData) {
    const meta = getPdfMetadata(file.filename);
    
    for (const page of file.pages) {
        const text = page.text.trim();
        if (!text || text.length < 50) continue;

        // Quebra o texto por parágrafos/seções
        const rawBlocks = text.split(/\n(?=[0-9]+\.[0-9]*\s+[A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ]|Quadro\s+[0-9]+|Figura\s+[0-9]+)/i);

        let currentSectionTitle = meta.defaultTitulo;
        let currentChunkText = "";

        for (const block of rawBlocks) {
            const cleanBlock = block.replace(/\s+/g, " ").trim();
            if (!cleanBlock) continue;

            const headerMatch = cleanBlock.match(/^([0-9]+\.[0-9]*\s+[A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ][^\.\n]{3,60}|Quadro\s+[0-9]+[^\.\n]{0,60})/i);
            if (headerMatch) {
                currentSectionTitle = headerMatch[1].trim();
            }

            if ((currentChunkText.length + cleanBlock.length) > 2200 && currentChunkText.length > 800) {
                chunks.push({
                    id: `chunk_pdf_${chunkIdCounter++}`,
                    indicador: meta.indicador,
                    tema: meta.tema,
                    tipo_conteudo: detectContentType(currentSectionTitle, currentChunkText),
                    fonte: file.filename,
                    secao: currentSectionTitle,
                    pagina: page.page_number,
                    content: currentChunkText.trim(),
                    tokens_approx: Math.round(currentChunkText.length / 4)
                });
                currentChunkText = cleanBlock;
            } else {
                currentChunkText += (currentChunkText ? "\n\n" : "") + cleanBlock;
            }
        }

        if (currentChunkText.length >= 100) {
            chunks.push({
                id: `chunk_pdf_${chunkIdCounter++}`,
                indicador: meta.indicador,
                tema: meta.tema,
                tipo_conteudo: detectContentType(currentSectionTitle, currentChunkText),
                fonte: file.filename,
                secao: currentSectionTitle,
                pagina: page.page_number,
                content: currentChunkText.trim(),
                tokens_approx: Math.round(currentChunkText.length / 4)
            });
        }
    }
}
console.log(`✅ ${chunks.length} chunks semânticos gerados a partir dos documentos normativos.`);

// 2. Ingestão do DATABASE-SCHEMA.md
console.log("\n📐 [2/4] Indexando DATABASE-SCHEMA.md...");
const schemaPath = path.resolve(projectRoot, "DATABASE-SCHEMA.md");
if (fs.existsSync(schemaPath)) {
    const schemaContent = fs.readFileSync(schemaPath, "utf-8");
    // Quebra por seções de nível 2, 3 e 4 (##, ### e ####)
    const schemaSections = schemaContent.split(/\n(?=###?#?\s+)/);

    for (const sec of schemaSections) {
        const lines = sec.trim().split("\n");
        const header = lines[0].replace(/^#+\s*/, "").trim();
        const body = lines.slice(1).join("\n").trim();
        if (!body || body.length < 50) continue;

        // Seção 2.2.3 contém detalhamento por indicador (C2 a C7). Quebra por cada indicador individualmente!
        if (header.includes("2.2.3") || header.includes("Coortes e Listas Nominais")) {
            const subIndBlocks = body.split(/\n(?=-\s+\*\*C[2-7]\s+-)/);
            for (const indBlock of subIndBlocks) {
                const bTrim = indBlock.trim();
                if (!bTrim) continue;
                const matchInd = bTrim.match(/\*\*C([2-7])\s+-/);
                const subInd = matchInd ? `C${matchInd[1]}` : "GERAL";
                chunks.push({
                    id: `chunk_schema_${chunkIdCounter++}`,
                    indicador: subInd,
                    tema: "schema_db",
                    tipo_conteudo: "schema_db",
                    fonte: "DATABASE-SCHEMA.md",
                    secao: `Esquema de Banco: Coorte e Lista Nominal ${subInd}`,
                    pagina: null,
                    content: bTrim,
                    tokens_approx: Math.round(bTrim.length / 4)
                });
            }
            continue;
        }

        // Mapeamento de indicador geral
        let secIndicador = "GERAL";
        const hLow = header.toLowerCase();
        const bLow = body.toLowerCase();
        if (hLow.includes("cvat") || bLow.includes("cvat_team_evaluations") || hLow.includes("2.2.4")) secIndicador = "CVAT";
        else if (hLow.includes("c1")) secIndicador = "C1";
        else if (hLow.includes("c2")) secIndicador = "C2";
        else if (hLow.includes("c3")) secIndicador = "C3";
        else if (hLow.includes("c4")) secIndicador = "C4";
        else if (hLow.includes("c5")) secIndicador = "C5";
        else if (hLow.includes("c6")) secIndicador = "C6";
        else if (hLow.includes("c7")) secIndicador = "C7";

        chunks.push({
            id: `chunk_schema_${chunkIdCounter++}`,
            indicador: secIndicador,
            tema: "schema_db",
            tipo_conteudo: "schema_db",
            fonte: "DATABASE-SCHEMA.md",
            secao: header,
            pagina: null,
            content: body,
            tokens_approx: Math.round(body.length / 4)
        });
    }
    console.log(`✅ DATABASE-SCHEMA.md indexado com sucesso.`);
}

// 3. Ingestão de Migrations críticas
console.log("\n🗄️ [3/4] Indexando migrations do banco de dados em database/migrations/...");
const migrationsDir = path.resolve(projectRoot, "database/migrations");
if (fs.existsSync(migrationsDir)) {
    const files = fs.readdirSync(migrationsDir).filter(f => f.endsWith(".php"));
    for (const mf of files) {
        const fullMigPath = path.join(migrationsDir, mf);
        const code = fs.readFileSync(fullMigPath, "utf-8");

        const tableMatches = [...code.matchAll(/Schema::(create|table)\(\s*['"]([^'"]+)['"]/g)];
        if (tableMatches.length === 0) continue;

        const tableNames = tableMatches.map(m => m[2]).join(", ");
        
        let migIndicador = "GERAL";
        const fn = mf.toLowerCase();
        if (fn.includes("c2_")) migIndicador = "C2";
        else if (fn.includes("c3_")) migIndicador = "C3";
        else if (fn.includes("c4_")) migIndicador = "C4";
        else if (fn.includes("c5_")) migIndicador = "C5";
        else if (fn.includes("c6_")) migIndicador = "C6";
        else if (fn.includes("c7_")) migIndicador = "C7";
        else if (fn.includes("cvat")) migIndicador = "CVAT";

        chunks.push({
            id: `chunk_mig_${chunkIdCounter++}`,
            indicador: migIndicador,
            tema: "schema_db",
            tipo_conteudo: "schema_db",
            fonte: mf,
            secao: `Migration: ${tableNames}`,
            pagina: null,
            content: `Arquivo de migração: ${mf}\nTabelas afetadas: ${tableNames}\n\nCódigo fonte da migração:\n${code}`,
            tokens_approx: Math.round(code.length / 4)
        });
    }
    console.log(`✅ Migrations indexadas com sucesso.`);
}

// 4. Geração de Embeddings e Construção do Índice BM25
console.log(`\n🧠 [4/4] Gerando dense embeddings (384d) e índice BM25 para ${chunks.length} chunks...`);
const embeddings = [];
for (let i = 0; i < chunks.length; i++) {
    const chunk = chunks[i];
    const fullText = `${chunk.secao} ${chunk.content} Indicador: ${chunk.indicador} Tema: ${chunk.tema} Conteúdo: ${chunk.tipo_conteudo}`;
    embeddings.push(generateEmbedding(fullText, 384));
    if ((i + 1) % 50 === 0 || i === chunks.length - 1) {
        process.stdout.write(`\rProcessados ${i + 1}/${chunks.length} embeddings...`);
    }
}
console.log("\n✅ Embeddings gerados.");

console.log("📊 Indexando termos para BM25...");
const bm25Index = buildBm25Index(chunks);
console.log(`✅ Índice BM25 construído com ${Object.keys(bm25Index.idf).length} termos únicos.`);

// Estatísticas por indicador
const stats = {};
for (const c of chunks) {
    stats[c.indicador] = (stats[c.indicador] || 0) + 1;
}

const knowledgeStore = {
    metadata: {
        total_chunks: chunks.length,
        indicators_breakdown: stats,
        generated_at: new Date().toISOString(),
        version: "1.0.0"
    },
    chunks,
    embeddings,
    bm25: bm25Index
};

const storePath = path.join(storageDir, "knowledge_store.json");
fs.writeFileSync(storePath, JSON.stringify(knowledgeStore, null, 2), "utf-8");
console.log(`💾 Base de conhecimento RAG salva com sucesso em:\n   ${storePath}`);
console.log("\n📈 Resumo da Ingestão por Indicador:");
for (const [ind, count] of Object.entries(stats)) {
    console.log(`   - ${ind.padEnd(8)}: ${count} chunks`);
}
console.log(`\n✨ Ingestão concluída com sucesso!`);

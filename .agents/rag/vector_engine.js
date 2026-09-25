// .agents/rag/vector_engine.js
/**
 * Motor vetorial e léxico híbrido de alta performance para RAG local.
 * Combina Dense Embeddings (384 dimensões via hashing ponderado de subwords/n-grams)
 * com BM25 (Best Matching 25) para máxima precisão em regras normativas e códigos clínicos.
 */

// Função hash estável de 32-bit (MurmurHash3)
function murmur3(key, seed = 0x9747b28c) {
    let remainder = key.length & 3;
    let bytes = key.length - remainder;
    let h1 = seed;
    let c1 = 0xcc9e2d51;
    let c2 = 0x1b873593;
    let i = 0;

    while (i < bytes) {
        let k1 =
            (key.charCodeAt(i) & 0xff) |
            ((key.charCodeAt(++i) & 0xff) << 8) |
            ((key.charCodeAt(++i) & 0xff) << 16) |
            ((key.charCodeAt(++i) & 0xff) << 24);
        ++i;

        k1 = Math.imul(k1, c1);
        k1 = (k1 << 15) | (k1 >>> 17);
        k1 = Math.imul(k1, c2);

        h1 ^= k1;
        h1 = (h1 << 13) | (h1 >>> 19);
        h1 = Math.imul(h1, 5) + 0xe6546b64;
    }

    let k1 = 0;
    switch (remainder) {
        case 3:
            k1 ^= (key.charCodeAt(i + 2) & 0xff) << 16;
        case 2:
            k1 ^= (key.charCodeAt(i + 1) & 0xff) << 8;
        case 1:
            k1 ^= key.charCodeAt(i) & 0xff;
            k1 = Math.imul(k1, c1);
            k1 = (k1 << 15) | (k1 >>> 17);
            k1 = Math.imul(k1, c2);
            h1 ^= k1;
    }

    h1 ^= key.length;
    h1 ^= h1 >>> 16;
    h1 = Math.imul(h1, 0x85ebca6b);
    h1 ^= h1 >>> 13;
    h1 = Math.imul(h1, 0xc2b2ae35);
    h1 ^= h1 >>> 16;

    return h1 >>> 0;
}

// Normalizador de texto para busca em português e termos técnicos
export function normalizeText(text) {
    if (!text) return "";
    return text
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "") // remove acentos
        .toLowerCase()
        .replace(/[^\w\s\-\.]/g, " ") // preserva hífens e pontos em códigos (ex: E11.9, CIAP-2, c4_nominal_diabetics)
        .replace(/\s+/g, " ")
        .trim();
}

// Tokenizador semântico preservando códigos e termos médicos/APS
export function tokenize(text) {
    const normalized = normalizeText(text);
    if (!normalized) return [];
    
    const rawTokens = normalized.split(/[\s_]+/).filter(t => t.length > 1);
    const tokens = [];

    for (let i = 0; i < rawTokens.length; i++) {
        const token = rawTokens[i];
        tokens.push(token);

        // Bi-gramas para termos compostos (ex: "busca ativa", "hemoglobina glicada", "plano terapeutico")
        if (i < rawTokens.length - 1) {
            tokens.push(`${token}_${rawTokens[i + 1]}`);
        }
    }
    return tokens;
}

// Gera vetor denso de 384 dimensões usando Feature Hashing e n-grams de subwords
export function generateEmbedding(text, dimensions = 384) {
    const vector = new Float32Array(dimensions);
    const normalized = normalizeText(text);
    if (!normalized) return Array.from(vector);

    const tokens = tokenize(text);
    
    // Ponderação de tokens de palavras
    for (const token of tokens) {
        const isBigram = token.includes("_");
        const weight = isBigram ? 1.5 : 1.0;
        const hash = murmur3(token);
        const idx = hash % dimensions;
        const sign = (hash & 0x80000000) ? -1 : 1;
        vector[idx] += sign * weight;

        // Subword n-grams (3 a 5 chars) para capturar variações morfológicas e códigos clínicos parciais
        if (!isBigram && token.length >= 3) {
            for (let len = 3; len <= Math.min(5, token.length); len++) {
                for (let start = 0; start <= token.length - len; start++) {
                    const sub = token.substring(start, start + len);
                    const subHash = murmur3(sub, 0x12345678);
                    const subIdx = subHash % dimensions;
                    const subSign = (subHash & 0x80000000) ? -1 : 1;
                    vector[subIdx] += subSign * 0.4;
                }
            }
        }
    }

    // Normalização L2 (vetor unitário para cosseno direto via dot-product)
    let sumSq = 0;
    for (let i = 0; i < dimensions; i++) {
        sumSq += vector[i] * vector[i];
    }
    const norm = Math.sqrt(sumSq) || 1.0;
    for (let i = 0; i < dimensions; i++) {
        vector[i] /= norm;
    }

    return Array.from(vector);
}

// Similaridade do cosseno entre dois vetores unitários
export function cosineSimilarity(vecA, vecB) {
    if (!vecA || !vecB || vecA.length !== vecB.length) return 0;
    let dot = 0;
    for (let i = 0; i < vecA.length; i++) {
        dot += vecA[i] * vecB[i];
    }
    return Math.max(0, Math.min(1, dot));
}

// Constrói índice BM25 invertido a partir da lista de chunks
export function buildBm25Index(chunks) {
    const docFrequencies = {};
    const invertedIndex = {};
    const docLengths = {};
    let totalLength = 0;

    for (const chunk of chunks) {
        const tokens = tokenize(`${chunk.titulo || chunk.secao || ""} ${chunk.content} ${chunk.indicador} ${chunk.tema}`);
        docLengths[chunk.id] = tokens.length;
        totalLength += tokens.length;

        const seenTerms = new Set();
        for (const term of tokens) {
            if (!invertedIndex[term]) {
                invertedIndex[term] = {};
            }
            invertedIndex[term][chunk.id] = (invertedIndex[term][chunk.id] || 0) + 1;

            if (!seenTerms.has(term)) {
                docFrequencies[term] = (docFrequencies[term] || 0) + 1;
                seenTerms.add(term);
            }
        }
    }

    const totalDocs = chunks.length || 1;
    const avgDocLength = totalLength / totalDocs;

    // Calcula IDF de Robertson-Spärck Jones
    const idf = {};
    for (const term in docFrequencies) {
        const df = docFrequencies[term];
        idf[term] = Math.log(1 + (totalDocs - df + 0.5) / (df + 0.5));
    }

    return {
        invertedIndex,
        docFrequencies,
        idf,
        docLengths,
        totalDocs,
        avgDocLength
    };
}

// Calcula score BM25 para um chunk
export function computeBm25Score(queryTokens, chunkId, bm25Index, k1 = 1.2, b = 0.75) {
    let score = 0;
    const docLen = bm25Index.docLengths[chunkId] || 1;
    const lenNorm = 1 - b + b * (docLen / (bm25Index.avgDocLength || 1));

    for (const term of queryTokens) {
        const postings = bm25Index.invertedIndex[term];
        if (!postings || !postings[chunkId]) continue;

        const tf = postings[chunkId];
        const idfVal = bm25Index.idf[term] || 0.1;
        const tfComponent = (tf * (k1 + 1)) / (tf + k1 * lenNorm);
        score += idfVal * tfComponent;
    }

    return score;
}

// Busca especializada em regras normativas da APS (exclui schemas técnicos do banco)
export function searchApsRules(query, knowledgeStore, options = {}) {
    const {
        indicador = null,
        tipo_conteudo = null,
        limit = 5,
        minScore = 0.04
    } = options;

    return hybridSearch(query, knowledgeStore, {
        indicador,
        tipo_conteudo,
        isRuleSearch: true,
        limit,
        minScore
    });
}

// Busca especializada em esquemas e modelagem do banco de dados (DATABASE-SCHEMA.md e migrations)
export function searchDbSchema(query, knowledgeStore, options = {}) {
    const {
        indicador = null,
        limit = 5,
        minScore = 0.04
    } = options;

    return hybridSearch(query, knowledgeStore, {
        indicador,
        isSchemaSearch: true,
        limit,
        minScore
    });
}

// Busca Híbrida Geral (Dense Vector + BM25 + Filtros Rígidos de Metadados)
export function hybridSearch(query, knowledgeStore, options = {}) {
    const {
        indicador = null,
        tipo_conteudo = null,
        tema = null,
        fonte = null,
        isRuleSearch = false,
        isSchemaSearch = false,
        limit = 5,
        minScore = 0.04
    } = options;

    const queryTokens = tokenize(query);
    const queryVector = generateEmbedding(query);

    const candidates = [];
    const chunks = knowledgeStore.chunks || [];
    const embeddings = knowledgeStore.embeddings || [];
    const bm25 = knowledgeStore.bm25;

    // 1. Filtragem estrita por metadados
    for (let i = 0; i < chunks.length; i++) {
        const chunk = chunks[i];

        // Se for busca de regras, exclui tabelas/schemas técnicos
        if (isRuleSearch && chunk.tipo_conteudo === "schema_db") {
            continue;
        }

        // Se for busca de schema de banco, exige schema_db
        if (isSchemaSearch && chunk.tipo_conteudo !== "schema_db") {
            continue;
        }

        // Filtro estrito por indicador (ex: C1..C7, CVAT, GERAL)
        // Evita contaminação entre indicadores distintos!
        if (indicador) {
            const indNorm = indicador.toUpperCase().trim();
            const chunkInd = (chunk.indicador || "").toUpperCase().trim();
            if (indNorm !== "TODOS" && chunkInd !== indNorm) {
                continue;
            }
        }

        // Filtro por tipo de conteúdo
        if (tipo_conteudo) {
            const tipoNorm = tipo_conteudo.toLowerCase().trim();
            const chunkTipo = (chunk.tipo_conteudo || "").toLowerCase().trim();
            if (chunkTipo !== tipoNorm) {
                continue;
            }
        }

        // Filtro por tema
        if (tema) {
            const temaNorm = tema.toLowerCase().trim();
            const chunkTema = (chunk.tema || "").toLowerCase().trim();
            if (chunkTema !== temaNorm) {
                continue;
            }
        }

        // Filtro por fonte
        if (fonte) {
            if (!chunk.fonte.toLowerCase().includes(fonte.toLowerCase())) {
                continue;
            }
        }

        candidates.push({
            index: i,
            chunk: chunk,
            embedding: embeddings[i]
        });
    }

    if (candidates.length === 0) {
        return [];
    }

    // 2. Pontuação Dense Vector e BM25
    let maxBm25 = 0;
    const scoredCandidates = candidates.map(c => {
        const cosSim = cosineSimilarity(queryVector, c.embedding);
        const bm25Raw = computeBm25Score(queryTokens, c.chunk.id, bm25);
        if (bm25Raw > maxBm25) maxBm25 = bm25Raw;
        return {
            chunk: c.chunk,
            cosSim,
            bm25Raw
        };
    });

    // 3. Combinação Híbrida Normalizada (50% Dense Vector + 50% BM25)
    const results = scoredCandidates.map(sc => {
        const bm25Norm = maxBm25 > 0 ? (sc.bm25Raw / maxBm25) : 0;
        const hybridScore = (0.50 * sc.cosSim) + (0.50 * bm25Norm);

        return {
            id: sc.chunk.id,
            indicador: sc.chunk.indicador,
            tema: sc.chunk.tema,
            tipo_conteudo: sc.chunk.tipo_conteudo,
            fonte: sc.chunk.fonte,
            secao: sc.chunk.secao || sc.chunk.titulo,
            pagina: sc.chunk.pagina || null,
            score: parseFloat(hybridScore.toFixed(4)),
            vector_score: parseFloat(sc.cosSim.toFixed(4)),
            lexical_score: parseFloat(bm25Norm.toFixed(4)),
            trecho: sc.chunk.content
        };
    });

    // 4. Ordenação decrescente por score híbrido
    results.sort((a, b) => b.score - a.score);

    return results
        .filter(r => r.score >= minScore)
        .slice(0, limit);
}

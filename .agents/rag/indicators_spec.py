import json

with open('.agents/rag/storage/oral_health_notes.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

with open('.agents/rag/storage/indicators_spec.md', 'w', encoding='utf-8') as out:
    for code in ['B1', 'B2', 'B3', 'B4', 'B5', 'B6']:
        d = data[code]
        out.write(f"# Indicador {code}: {d['file']}\n\n")
        lines = d['content'].split('\n')
        # find relevant sections
        for i, l in enumerate(lines):
            line = l.strip()
            if any(term in line.lower() for term in ['fórmula de cálculo', 'parâmetro', 'cbo utilizados', 'código no sigtap', 'códigos no sigtap', 'procedimentos', 'quadro']):
                out.write(f"### {line}\n")
                snippet = lines[i:min(i+35, len(lines))]
                out.write('\n'.join(snippet) + '\n\n---\n\n')

print("Gerado indicators_spec.md com sucesso!")

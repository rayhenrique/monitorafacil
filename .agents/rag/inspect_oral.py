import json

with open('.agents/rag/storage/oral_health_notes.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

with open('.agents/rag/storage/oral_health_summary.txt', 'w', encoding='utf-8') as out:
    for code, d in data.items():
        fname = d['file']
        out.write(f"\n===================== {code} : {fname} =====================\n")
        lines = d['content'].split('\n')
        for i, line in enumerate(lines):
            l = line.strip()
            # print items 18 to 32 from ficha
            if any(marker in l for marker in ['Fórmula', 'Formula', 'Método', 'Metodo', 'Parâmetro', 'Parametro', 'Polaridade', 'Unidade', 'CBO', 'SIGTAP', 'Quadro', 'Numerador:', 'Denominador:']):
                out.write(f"[L{i}] {l}\n")
                for j in range(1, 15):
                    if i + j < len(lines):
                        out.write(f"    {lines[i+j].strip()}\n")

print("OK oral_health_summary.txt gerado com sucesso!")

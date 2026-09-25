import json, re

with open('.agents/rag/storage/oral_health_notes.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

with open('.agents/rag/storage/oral_health_consolidated.md', 'w', encoding='utf-8') as out:
    for code, d in data.items():
        out.write(f"# INDICADOR {code} - {d['file']}\n\n")
        lines = d['content'].split('\n')
        # Extract section 18 to 32
        text = d['content']
        # regex search for Formula de Calculo
        m_formula = re.search(r'Fórmula de\s*Cálculo\s*(.*?)(?=Método de cálculo|24\s*Método)', text, re.DOTALL | re.IGNORECASE)
        if m_formula:
            out.write("### Fórmula de Cálculo:\n" + m_formula.group(1).strip() + "\n\n")

        m_param = re.search(r'Parâmetro\s*(.*?)(?=31\s*Classificação|Classificação)', text, re.DOTALL | re.IGNORECASE)
        if m_param:
            out.write("### Parâmetros:\n" + m_param.group(1).strip() + "\n\n")

        m_polaridade = re.search(r'Polaridade\s*(.*?)(?=23\s*Fórmula|Fórmula)', text, re.DOTALL | re.IGNORECASE)
        if m_polaridade:
            out.write("### Polaridade:\n" + m_polaridade.group(1).strip() + "\n\n")

        # Extract Quadros
        quadros = re.findall(r'(Quadro\s*\d+.*?)(?=(?:Quadro\s*\d+|REFERÊNCIAS|NOTA DE RODAPÉ|$))', text, re.DOTALL | re.IGNORECASE)
        for q in quadros:
            out.write("### " + q[:500].strip() + "\n\n")

print("Gerado oral_health_consolidated.md com sucesso!")

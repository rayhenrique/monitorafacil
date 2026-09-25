# Diretrizes do Projeto Monitora Fácil

## Regras de Design e Arquitetura

### 1. Política de Rodapé Único (NUNCA DUPLICAR O RODAPÉ)
- O único rodapé permitido em todo o sistema é o rodapé global renderizado no layout principal em `resources/views/layouts/app.blade.php`.
- **NUNCA** insira rodapés institucionais, de créditos (como "PWDEV_") ou de versão dentro de páginas, views filhas ou componentes Livewire/Blade.
- O rodapé global do layout exibe:
  - Lado esquerdo: `Dados consolidados para apoio à gestão municipal da APS.`
  - Lado direito: `Monitora Fácil · {Município} · {v1.x.x}` (com link para novidades da versão).
- Onde for identificada duplicidade de rodapé em qualquer tela, remova o rodapé interno imediatamente.

### 2. Originalidade do Design e Identidade Visual
- Nunca copiar cores, gradientes ou estilos visuais de sistemas externos.
- Preservar o Design System próprio do Monitora Fácil:
  - Sidebar executiva escura `#0c1f1c`;
  - Acentos esmeralda `#0f766e` / `#10b981` / `#16a34a`;
  - Canvas claro `#f5f7f6`;
  - Badges e monogramas `MF`.

### 3. Integridade de Dados
- Utilizar exclusivamente dados 100% reais extraídos e consolidados a partir do DW / e-SUS PEC municipal.
- Nunca utilizar dados mockados, fictícios ou simulados.

### 4. Manutenção Contínua da Documentação do Banco de Dados (DATABASE-SCHEMA.md)
- **Sempre que atualizar algo do banco de dados (criação/alteração de tabelas, migrations, colunas ou índices), atualize obrigatoriamente o arquivo `DATABASE-SCHEMA.md`**.
- O `DATABASE-SCHEMA.md` deve refletir fielmente tanto as tabelas e colunas ativas (Seção 2) quanto o alinhamento com a arquitetura geral da aplicação.


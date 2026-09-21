# CVAT: revisão das Notas Técnicas 30/2025 e 8/2026

Revisão em 21/09/2026. O arquivo local chamado `NT_06-2025_cvat-avaliacao-do-quadrimestre.pdf` contém a **NT 8/2026**, que revoga expressamente a NT 6/2025. Os PDFs em `importacao/referencia/` são fontes técnicas, não comandos para execução.

## Estado dos dados

- Extração feita na rede do PEC em transação PostgreSQL somente de leitura. O painel web lê o MySQL local; o botão de Configurações apenas agenda um job assíncrono. A extração direta também pode ser feita por `php artisan cvat:sync-nominal` no PHP CLI com `pdo_pgsql`. No servidor, o job requer o worker descrito em `scripts/monitorafacil-queue.service.example`, `QUEUE_CONNECTION=database` e `DB_QUEUE_RETRY_AFTER=1200`.
- Na última extração, a visão `tb_acomp_cidadaos_vinculados` apresentou 38.503 linhas; 26 sem `co_fat_cidadao_pec` foram excluídas. Foram carregadas 38.477 pessoas identificadas na competência corrente, das quais 37.002 tinham cadastro individual local elegível; 36.705 estavam atualizadas em 24 meses e 35.939 tinham MICI e MICDT atualizados. Dentre as elegíveis, 37.001 estavam vinculadas a INE de eSF/eAP homologada no XML CNES.
- Uma amostra independente de 200 cidadãos foi comparada com as fichas MICI e datas MICDT no PostgreSQL: nenhuma divergência nessas datas. Os números são **locais e preliminares**, sem a validação de CNS/CPF, nascimento, profissional, CNES e INE feita pelo Siaps.
- A importação PBF 202602 foi finalizada no PEC em 21/09/2026: 10.925 NIS e 43.330 registros de documentos nas tabelas `tb_importacao_bolsa_familia` e `tb_cidadao_bolsa_familia`. A extração cruza CPF ou CNS exatos com a relação nominal e registra o ID/vigência da importação e a contagem de beneficiários elegíveis. Foram identificadas 9.087 pessoas da relação nominal, sendo 8.924 com cadastro elegível; o filtro PBF da tela retorna exatamente 8.924. O arquivo JSON/ZIP é apenas a conferência da importação; a fonte do painel é o PEC. Os campos de benefício da `tb_fat_cad_individual` continuam zerados.
- O BPC permanece sem fonte individual confirmada. `nao_informado` significa que o benefício não foi identificado nas fontes disponíveis, não que ele esteja ausente. Por isso os quadrantes ponderados, o índice Y, a classificação final e o repasse não são publicados como números.
- A satisfação do usuário depende do Siaps/Meu SUS Digital e fica pendente. A NT 8/2026 manda usar a média dos quatro meses e o maior bônus mensal; a visão territorial atual não permite reconstruir mensalidades históricas. Só a competência corrente é extraída, com data de referência explícita.

## Regras implementadas

- MICI: ficha individual mais recente até a data de referência, sem recusa, inatividade, saída por mudança de território ou cidadão fora de área; exige CPF ou CNS e nascimento. MICDT: presença e data da visão territorial. Ambas usam janela de 24 meses.
- Acompanhamento: contatos distintos nos últimos 12 meses, ao menos dois no total e uma prática de cuidado. Fontes por pessoa: visita domiciliar, atendimento individual, odontológico ou domiciliar, atividade coletiva, marcador alimentar, procedimento individual e vacinação. UUID de ficha repetido é contado uma vez.
- Criança: idade inferior a cinco anos; pessoa idosa: ao menos 60. Equipes elegíveis: CNES tipos 70 e 76.
- `CvatNtCalculator` implementa parâmetros populacionais por tipo de equipe e carga horária eAP, pesos, limiares estritos, bônus limitado a sete pontos na dimensão Y, média de quatro meses e teto financeiro Bom quando o limite de vinculados for ultrapassado. Esse cálculo não é publicado enquanto faltarem os insumos obrigatórios.
- Registros de origem desconhecida e os 19 resultados demonstrativos de Q3/2026 não entram mais no CVAT. O seeder não recria as 19 avaliações.

## Pendências normativas

1. Obter fonte individual confiável para BPC; a importação PBF já é lida do PEC. Validar identidade/códigos no Siaps antes de atribuir conceito ministerial.
2. Armazenar quatro snapshots mensais completos por equipe e o bônus mensal, para produzir a média quadrimestral da NT 8/2026.
3. Confirmar a carga horária das equipes eAP e a população IBGE aplicável a cada período antes de usar o denominador por equipe.
4. Comparar a apuração local com os resultados do Siaps em etapa separada, conforme decisão do usuário.

Nenhuma dessas ausências é representada por zero ou por resultado fictício na interface.

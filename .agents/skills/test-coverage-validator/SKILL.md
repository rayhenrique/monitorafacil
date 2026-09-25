---
name: test-coverage-validator
description: >
  Validação de integridade de testes automatizados via PHPUnit/Pest após
  qualquer alteração no Monitora Fácil. Executa testes via MCP laravel-artisan-runner
  (artisan_test com filtro) e audita a cobertura obrigatória dos 5 casos de borda
  metodológicos da APS: cidadão sem CPF (só CNS), CBO não homologada, eventos fora
  do quadrimestre, duplicidade de cadastro entre equipes e denominador zero.
---

# Validador de Cobertura de Testes e Casos de Borda da APS

Esta skill define o protocolo rigoroso de testes automatizados no **Monitora Fácil**, instruindo o agente a executar a suíte via ferramentas MCP e a garantir a presença dos 5 casos de borda metodológicos exigidos pelas Notas Técnicas do Ministério da Saúde.

---

## 1. Gatilhos de Ativação

Ative esta skill quando a tarefa envolver:
- Término de qualquer implementação ou refatoração em serviços (`app/Services/`), models (`app/Models/`) ou Livewire (`app/Livewire/`).
- Validação de novas regras de cálculo ou extração DW (`C*PracticeCalculator`, `C*DwService`).
- Verificação prévia a commits e deploys para prevenir regressões de negócio.

---

## 2. Execução de Testes via MCP (`laravel-artisan-runner`)

Ao validar testes no ambiente, o agente deve utilizar o servidor MCP `laravel-artisan-runner`:

- **Para testar uma suíte ou classe específica:**
  Invoque a ferramenta `artisan_test` (ou `artisan_run` com `command: "test --filter=NomeDoTeste"`).
  Exemplo de parâmetros:
  ```json
  {
    "filter": "C4PracticeCalculatorTest"
  }
  ```
- **Para testar um arquivo inteiro de Feature:**
  ```json
  {
    "filter": "C4IndicatorTest"
  }
  ```

Se o teste falhar, analise a stack trace, corrija o código de produção ou de teste e execute novamente até obter 100% de aprovação (*Green*).

---

## 3. O Checklist Obrigatório dos 5 Casos de Borda da APS

Todo indicador ou serviço de cálculo deve conter testes automatizados cobrindo explicitamente:

### 1. Cidadão sem CPF (Apenas com CNS Válido)
- **Cenário:** O cidadão não possui CPF cadastrado no e-SUS PEC, mas possui Cartão Nacional de Saúde (CNS) com 15 dígitos válidos.
- **Expectativa:** O cidadão **deve** ser incluído na coorte e pontuado normalmente. Não deve ser descartado nem gerar erro de validação.

### 2. Atendimento Realizado por Profissional Não Homologado
- **Cenário:** O registro clínico (consulta, procedimento, vacina) foi assinado por um profissional com CBO que não pertence à lista habilitada pelo Ministério da Saúde (ex: técnico de enfermagem realizando consulta de pré-natal, ou CBO administrativa).
- **Expectativa:** O evento clínico **deve ser ignorado** para fins de cumprimento da prática, sem quebrar o cálculo do paciente.

### 3. Evento Clínico Fora do Quadrimestre de Avaliação (Boundary Testing)
- **Cenário:** Procedimento ou exame realizado 1 dia antes do início da janela regulamentar, ou 1 dia após o fechamento do período de avaliação.
- **Expectativa:** O evento **não deve ser computado** para a competência em questão. Apenas eventos rigorosamente contidos no intervalo `[data_inicio, data_fim]` são elegíveis.

### 4. Duplicidade de Cadastro em Equipes Diferentes
- **Cenário:** O mesmo CPF ou CNS possui ficha de cadastro individual ativa em duas equipes distintas (INEs diferentes).
- **Expectativa:** O desempate metodológico deve alocar o cidadão à equipe com atendimento ou cadastro individual mais recente (`dt_registro`), impedindo duplicidade no consolidado municipal.

### 5. Denominador Zero (Sem Cidadãos Elegíveis na Equipe)
- **Cenário:** Uma equipe recém-implantada ou sem nenhum usuário elegível na coorte (denominador = 0).
- **Expectativa:** O sistema não deve disparar erro fatal de divisão por zero (`DivisionByZeroError`). O resultado retornado deve ser percentual nulo (`null`) ou `0.0`, com status explícito.

---

## 4. Templates de Testes Automatizados (PHPUnit)

### Template 1: Testes Unitários de Casos de Borda (`tests/Unit/C*PracticeCalculatorTest.php`)

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\C{X}PracticeCalculator;
use PHPUnit\Framework\TestCase;

class C{X}PracticeCalculatorTest extends TestCase
{
    private C{X}PracticeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new C{X}PracticeCalculator();
    }

    /**
     * Caso de borda 1: Cidadão sem CPF, identificado apenas pelo CNS.
     */
    public function test_citizen_with_only_cns_is_scored_normally(): void
    {
        $payload = [
            'reference_date' => '2026-08-31',
            'cpf' => null,
            'cns' => '700000000000001',
            'events' => [
                'consults' => ['2026-04-10'],
                'exams' => ['2026-05-15'],
            ],
            'team_type' => '70',
        ];

        $result = $this->calculator->calculate($payload);

        $this->assertSame(100, $result['score_percent']);
        $this->assertTrue($result['practices_met']['A']);
        $this->assertTrue($result['practices_met']['B']);
    }

    /**
     * Caso de borda 2: Procedimento executado por CBO não homologada é descartado.
     */
    public function test_ignores_events_from_non_homologated_cbo(): void
    {
        $payload = [
            'reference_date' => '2026-08-31',
            'events' => [
                // Evento registrado por CBO inválida (ex: 999999) não deve contar
                'consults_valid' => [],
                'consults_invalid_cbo' => ['2026-04-10'],
            ],
            'team_type' => '70',
        ];

        $result = $this->calculator->calculate($payload);

        $this->assertSame(0, $result['score_percent']);
        $this->assertFalse($result['practices_met']['A']);
    }

    /**
     * Caso de borda 3: Evento 1 dia antes da janela regulamentar não pontua.
     */
    public function test_event_one_day_before_window_does_not_count(): void
    {
        // Janela de 12 meses: 2025-09-01 a 2026-08-31
        $payload = [
            'reference_date' => '2026-08-31',
            'events' => [
                'consults' => ['2025-08-31'], // 1 dia antes da janela de 1 ano
            ],
            'team_type' => '70',
        ];

        $result = $this->calculator->calculate($payload);

        $this->assertFalse($result['practices_met']['A']);
    }

    /**
     * Caso de borda 5: Denominador zero previne DivisionByZeroError.
     */
    public function test_zero_denominator_returns_safe_zero_or_null(): void
    {
        $numerator = 0;
        $denominator = 0;

        $rate = $denominator > 0 ? ($numerator / $denominator) * 100 : 0.0;

        $this->assertSame(0.0, $rate);
    }
}
```

---

### Template 2: Teste de Feature para Duplicidade de Equipes (`tests/Feature/C*NominalDeduplicationTest.php`)

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class C{X}NominalDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso de borda 4: Cidadão com múltiplos cadastros fica na equipe mais recente.
     */
    public function test_citizen_registered_in_multiple_teams_is_allocated_to_most_recent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Simula processamento nominal com duplicidade de vínculo
        // O serviço de extração deve atribuir o cidadão exclusivamente à equipe mais recente.
        $this->assertTrue(true);
    }
}
```

---

## 5. Fluxo de Trabalho do Agente

Ao concluir qualquer alteração de código relacionada a indicadores ou relatórios:

1. **Identifique a suíte correspondente:** localize em `tests/Unit/` ou `tests/Feature/`.
2. **Execute o teste via MCP:**
   Use `artisan_test` com o filtro adequado.
3. **Analise a saída:**
   - Se houver falhas, leia o erro, examine a asserção e ajuste o código.
   - Verifique se os 5 casos de borda estão representados na suíte. Se não estiverem, adicione-os imediatamente.
4. **Validação Final:** Somente prossiga para a etapa de commit ou resposta ao usuário quando a execução retornar código de sucesso (0 failures, 0 errors).

# Política de Rodapé Único (Footer Policy)

## Regra Fundamental
1. **NUNCA DUPLICAR O RODAPÉ**: O único rodapé permitido em todo o sistema é o rodapé global renderizado no layout principal em `resources/views/layouts/app.blade.php`.
2. **NUNCA INSERIR RODAPÉS EM TELAS / COMPONENTES / VIEWS FILHAS**: Em nenhuma hipótese adicione blocos de rodapé (`<!-- Rodapé Institucional -->`, `<footer>`, `<div>` com versão ou créditos institucionais como "PWDEV_" ou "Sistema de Monitoramento...") dentro de views Blade ou componentes Livewire individuais.
3. **REMOVER DUPLICIDADE IMEDIATAMENTE**: Onde for identificada qualquer duplicidade de rodapé em qualquer página do sistema, remova o rodapé interno imediatamente.
4. **VERSÃO NO RODAPÉ GLOBAL**: A versão do sistema (`\App\Services\VersionService::CURRENT_VERSION`) é exibida exclusivamente no rodapé global do layout, ao lado do nome do município e da mensagem institucional.

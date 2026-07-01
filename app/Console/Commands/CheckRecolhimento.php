<?php

namespace App\Console\Commands;

use App\Services\RecolhimentoService;
use Illuminate\Console\Command;

class CheckRecolhimento extends Command
{
    protected $signature = 'recolhimento:check';
    protected $description = 'Verifica regras de recolhimento e gera notificacoes';

    public function handle(RecolhimentoService $service)
    {
        $dias = \App\Models\RecolhimentoRegra::diasAntecedenciaParaHoje();

        if (!$dias) {
            $this->info('Nenhuma regra ativa para hoje. Nenhuma notificacao gerada.');
            return Command::SUCCESS;
        }

        $created = $service->gerarNotificacoes();
        $this->info("Criadas {$created} notificacoes de recolhimento.");
        return Command::SUCCESS;
    }
}

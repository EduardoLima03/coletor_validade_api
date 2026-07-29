<?php

namespace App\Console\Commands;

use App\Jobs\ImportColetasJob;
use App\Jobs\ImportProdutosJob;
use App\Models\Loja;
use App\Models\User;
use Illuminate\Console\Command;

class ImportCsvCommand extends Command
{
    protected $signature = 'import:csv
        {type : Tipo de importação: "produtos" ou "coletas"}
        {file : Caminho para o arquivo CSV}
        {--loja= : ID da loja (obrigatório para coletas)}
        {--user=1 : ID do usuário para registro do log}
        {--queue : Processar em fila em vez de síncrono}';

    protected $description = 'Importa produtos ou coletas de um arquivo CSV';

    public function handle(): int
    {
        $type = $this->argument('type');
        $filePath = $this->argument('file');
        $userId = (int) $this->option('user');
        $useQueue = (bool) $this->option('queue');

        if (!file_exists($filePath)) {
            $this->error("Arquivo não encontrado: {$filePath}");
            return Command::FAILURE;
        }

        if (!is_readable($filePath)) {
            $this->error("Arquivo sem permissão de leitura: {$filePath}");
            return Command::FAILURE;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário ID {$userId} não encontrado.");
            return Command::FAILURE;
        }

        switch ($type) {
            case 'produtos':
                return $this->importProdutos($filePath, $userId, $useQueue);

            case 'coletas':
                $lojaId = (int) $this->option('loja');
                if (!$lojaId) {
                    $this->error('A opção --loja é obrigatória para importação de coletas.');
                    return Command::FAILURE;
                }
                $loja = Loja::find($lojaId);
                if (!$loja) {
                    $this->error("Loja ID {$lojaId} não encontrada.");
                    return Command::FAILURE;
                }
                return $this->importColetas($filePath, $lojaId, $userId, $useQueue);

            default:
                $this->error("Tipo inválido: {$type}. Use 'produtos' ou 'coletas'.");
                return Command::FAILURE;
        }
    }

    private function importProdutos(string $filePath, int $userId, bool $useQueue): int
    {
        if ($useQueue) {
            ImportProdutosJob::dispatch($filePath, $userId);
            $this->info('Importação de produtos enviada para a fila.');
            return Command::SUCCESS;
        }

        $this->info('Iniciando importação de produtos...');
        $job = new ImportProdutosJob($filePath, $userId);
        $job->handle();
        $this->info('Importação de produtos concluída.');
        return Command::SUCCESS;
    }

    private function importColetas(string $filePath, int $lojaId, int $userId, bool $useQueue): int
    {
        if ($useQueue) {
            ImportColetasJob::dispatch($filePath, $lojaId, $userId);
            $this->info('Importação de coletas enviada para a fila.');
            return Command::SUCCESS;
        }

        $this->info('Iniciando importação de coletas...');
        $job = new ImportColetasJob($filePath, $lojaId, $userId);
        $job->handle();
        $this->info('Importação de coletas concluída.');
        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SplitCsvCommand extends Command
{
    protected $signature = 'csv:split
        {file : Caminho do arquivo CSV de origem}
        {--lines=5000 : Quantidade de linhas de dados por arquivo}
        {--output-dir= : Diretório de saída (default: mesmo diretório do arquivo)}';

    protected $description = 'Divide um CSV grande em arquivos menores preservando o cabeçalho';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("Arquivo não encontrado: {$filePath}");
            return Command::FAILURE;
        }

        $linesPerFile = (int) $this->option('lines');
        if ($linesPerFile < 1) {
            $this->error('--lines deve ser >= 1');
            return Command::FAILURE;
        }

        $outputDir = $this->option('output-dir');
        if (!$outputDir) {
            $outputDir = dirname(realpath($filePath));
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Não foi possível abrir o arquivo.");
            return Command::FAILURE;
        }

        $header = fgets($handle);
        if ($header === false) {
            $this->error("Arquivo vazio.");
            fclose($handle);
            return Command::FAILURE;
        }

        $pathInfo = pathinfo($filePath);
        $baseName = $pathInfo['filename'];

        $part = 0;
        $lineCount = 0;
        $currentHandle = null;

        while (!feof($handle)) {
            if ($currentHandle === null) {
                $part++;
                $partFile = "{$outputDir}/{$baseName}_parte{$part}.csv";
                $currentHandle = fopen($partFile, 'w');
                if (!$currentHandle) {
                    $this->error("Não foi possível criar: {$partFile}");
                    fclose($handle);
                    return Command::FAILURE;
                }
                fwrite($currentHandle, $header);
                $lineCount = 0;
                $this->info("Criando: {$partFile}");
            }

            $line = fgets($handle);
            if ($line === false) {
                break;
            }

            fwrite($currentHandle, $line);
            $lineCount++;

            if ($lineCount >= $linesPerFile) {
                fclose($currentHandle);
                $currentHandle = null;
            }
        }

        if ($currentHandle !== null) {
            fclose($currentHandle);
        }

        fclose($handle);

        $this->info("Arquivo dividido em {$part} partes em: {$outputDir}");

        return Command::SUCCESS;
    }
}

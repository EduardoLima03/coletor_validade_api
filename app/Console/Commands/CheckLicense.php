<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class CheckLicense extends Command
{
    protected $signature = 'license:check';
    protected $description = 'Verifica a validade da licença junto ao servidor de licenciamento';

    public function handle(LicenseService $licenseService)
    {
        $info = $licenseService->refresh();

        if (!$info) {
            $this->warn('Nenhuma licença configurada ou falha na validação.');
            return 0;
        }

        if ($info['expired']) {
            $this->error('Licença expirada em ' . $info['expires_at']->format('d/m/Y'));
            return 0;
        }

        $this->info('Licença válida. Pacote: ' . $info['package_name']);

        if ($info['days_remaining'] >= 0) {
            $this->line('Dias restantes: ' . $info['days_remaining']);
        }

        if ($info['max_users'] > 0) {
            $this->line(
                'Usuários: ' . $info['user_count'] . '/' . $info['max_users']
                . ' (' . round(($info['user_count'] / $info['max_users']) * 100) . '%)'
            );
        }

        return 0;
    }
}

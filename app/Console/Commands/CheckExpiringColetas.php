<?php

namespace App\Console\Commands;

use App\Models\Coleta;
use App\Models\Notification;
use Illuminate\Console\Command;

class CheckExpiringColetas extends Command
{
    protected $signature = 'coletas:check-expiring {--days=5 : Dias para considerar como proximo do vencimento}';
    protected $description = 'Verifica coletas proximas do vencimento e cria notificacoes';

    public function handle()
    {
        $days = (int) $this->option('days');
        $today = now()->startOfDay();
        $threshold = $today->copy()->addDays($days);

        $expiring = Coleta::with('user', 'loja')
            ->whereBetween('data_validade', [$today, $threshold])
            ->get()
            ->groupBy('user_id');

        $created = 0;
        $today = now()->startOfDay();

        $existing = Notification::where('type', 'expiry_alert')
            ->whereDate('created_at', $today)
            ->pluck('user_id')
            ->toArray();

        foreach ($expiring as $userId => $coletas) {
            if (!$userId) continue;
            if (in_array($userId, $existing)) continue;

            $count = $coletas->count();
            $lojaNomes = $coletas->pluck('loja.nome')->unique()->take(3)->implode(', ');

            Notification::create([
                'user_id' => $userId,
                'type' => 'expiry_alert',
                'title' => "{$count} coleta(s) próxima(s) do vencimento",
                'message' => "{$count} coleta(s) vence(m) nos próximos {$days} dias. Lojas: {$lojaNomes}.",
                'icon' => 'bi-exclamation-triangle',
                'color' => 'warning',
                'data' => [
                    'days' => $days,
                    'total' => $count,
                    'lojas' => $coletas->pluck('loja.nome')->unique()->values(),
                ],
            ]);

            $created++;
        }

        $this->info("Criadas {$created} notificações de vencimento.");

        return Command::SUCCESS;
    }
}

<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LicenseService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('app.license_api_url', 'https://licenciamento.seusite.com/api');
    }

    public function validate(): ?array
    {
        $setting = Setting::first();

        if (!$setting || !$setting->license_key) {
            return null;
        }

        $cacheKey = 'license_validation_' . $setting->license_key;

        return Cache::remember($cacheKey, 3600, function () use ($setting) {
            return $this->check($setting->license_key);
        });
    }

    public function refresh(): ?array
    {
        $setting = Setting::first();

        if (!$setting || !$setting->license_key) {
            return null;
        }

        $cacheKey = 'license_validation_' . $setting->license_key;
        Cache::forget($cacheKey);

        $result = $this->check($setting->license_key);

        if ($result) {
            Cache::put($cacheKey, $result, 3600);
        }

        return $result;
    }

    private function check(string $licenseKey): ?array
    {
        try {
            $response = Http::timeout(10)->get($this->apiUrl . '/validate/' . $licenseKey, [
                'domain' => request()->getHttpHost(),
                'url' => config('app.url'),
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (!$data || !($data['valid'] ?? false)) {
                return null;
            }

            $expiresAt = $data['expires_at'] ? Carbon::parse($data['expires_at']) : null;

            return [
                'valid' => true,
                'license_key' => $licenseKey,
                'package_name' => $data['package_name'] ?? 'N/A',
                'max_users' => (int) ($data['max_users'] ?? 0),
                'expires_at' => $expiresAt,
                'days_remaining' => $expiresAt ? max(0, now()->startOfDay()->diffInDays($expiresAt->startOfDay(), false)) : -1,
                'expired' => $expiresAt ? $expiresAt->isPast() : false,
                'user_count' => User::count(),
            ];
        } catch (\Exception $e) {
            \Log::warning('Falha ao validar licença: ' . $e->getMessage());
            return null;
        }
    }
}

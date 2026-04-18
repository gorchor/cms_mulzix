<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CoffeeMarketController extends Controller
{
    public function price(): JsonResponse
    {
        $cacheSeconds = 300;

        $data = Cache::remember('coffee_market_price_hybrid', now()->addSeconds($cacheSeconds), function () {
            $yahoo = $this->fromYahoo();

            if ($yahoo !== null) {
                return $yahoo;
            }

            $twelve = $this->fromTwelveData();

            if ($twelve !== null) {
                return $twelve;
            }

            return [
                'symbol' => 'KC',
                'interval' => 'fallback',
                'currency' => 'USD',
                'price' => 225.40,
                'previous_close' => 223.00,
                'change' => 2.40,
                'change_percent' => 1.08,
                'last_datetime' => now()->toDateTimeString(),
                'source' => 'fallback',
                'cached' => false,
                'fallback' => true,
            ];
        });

        return response()->json($data);
    }

    private function fromYahoo(): ?array
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry(2, 500)
                ->get('https://query1.finance.yahoo.com/v8/finance/chart/KC=F');

            if (! $response->successful()) {
                throw new \RuntimeException('Yahoo HTTP '.$response->status());
            }

            $json = $response->json();
            $result = $json['chart']['result'][0] ?? null;
            $meta = $result['meta'] ?? null;

            if (! $meta) {
                throw new \RuntimeException('Respuesta inválida de Yahoo');
            }

            $price = (float) ($meta['regularMarketPrice'] ?? 0);
            $previousClose = (float) ($meta['previousClose'] ?? $price);

            if ($price <= 0) {
                throw new \RuntimeException('Yahoo devolvió precio vacío');
            }

            $change = $price - $previousClose;
            $changePercent = $previousClose > 0 ? ($change / $previousClose) * 100 : 0;

            return [
                'symbol' => 'KC=F',
                'interval' => 'live',
                'currency' => 'USD',
                'price' => round($price, 2),
                'previous_close' => round($previousClose, 2),
                'change' => round($change, 2),
                'change_percent' => round($changePercent, 2),
                'last_datetime' => now()->toDateTimeString(),
                'source' => 'yahoo',
                'cached' => true,
                'fallback' => false,
            ];
        } catch (\Throwable $e) {
            Log::warning('Yahoo coffee error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function fromTwelveData(): ?array
    {
        try {
            $symbol = config('services.twelvedata.coffee_symbol', 'KC1');
            $interval = config('services.twelvedata.interval', '1day');
            $apiKey = config('services.twelvedata.key');
            $baseUrl = rtrim(config('services.twelvedata.base_url', 'https://api.twelvedata.com'), '/');

            if (empty($apiKey)) {
                throw new \RuntimeException('API key vacía');
            }

            $response = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry(2, 500)
                ->get($baseUrl . '/time_series', [
                    'symbol' => $symbol,
                    'interval' => $interval,
                    'outputsize' => 2,
                    'apikey' => $apiKey,
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Twelve Data HTTP '.$response->status());
            }

            $json = $response->json();

            if (
                !is_array($json) ||
                (isset($json['status']) && $json['status'] === 'error') ||
                empty($json['values']) ||
                !is_array($json['values'])
            ) {
                throw new \RuntimeException($json['message'] ?? 'Respuesta inválida de Twelve Data');
            }

            $latest = $json['values'][0] ?? null;
            $previous = $json['values'][1] ?? $latest;

            if (!$latest || !isset($latest['close'])) {
                throw new \RuntimeException('Sin cierre reciente');
            }

            $price = (float) $latest['close'];
            $previousClose = isset($previous['close']) ? (float) $previous['close'] : $price;

            $change = $price - $previousClose;
            $changePercent = $previousClose > 0 ? ($change / $previousClose) * 100 : 0;

            return [
                'symbol' => $json['meta']['symbol'] ?? $symbol,
                'interval' => $json['meta']['interval'] ?? $interval,
                'currency' => 'USD',
                'price' => round($price, 2),
                'previous_close' => round($previousClose, 2),
                'change' => round($change, 2),
                'change_percent' => round($changePercent, 2),
                'last_datetime' => $latest['datetime'] ?? now()->toDateTimeString(),
                'source' => 'twelvedata',
                'cached' => true,
                'fallback' => false,
            ];
        } catch (\Throwable $e) {
            Log::warning('Twelve Data coffee error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
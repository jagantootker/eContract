<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService
{
    protected string $baseUrl;
    protected ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = config('api.base_url');
    }

    public function withAuth(): static
    {
        $clone = clone $this;
        $clone->token = session('api_token');
        return $clone;
    }

    protected function client()
    {
        $client = Http::baseUrl($this->baseUrl)
            ->timeout(30)
            ->acceptJson();

        if ($this->token) {
            $client = $client->withToken($this->token);
        }

        return $client;
    }

    // ── GET ──────────────────────────────────────────────────────────────────
    public function get(string $endpoint, array $params = []): array
    {
        try {
            $response = $this->client()->get($endpoint, $params);

            return $this->parse($response);
        } catch (\Throwable $e) {
            Log::error("ApiService GET {$endpoint}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ralat sambungan ke pelayan API.'];
        }
    }

    // ── POST ─────────────────────────────────────────────────────────────────
    public function post(string $endpoint, array $data = []): array
    {
        try {
            $response = $this->client()->post($endpoint, $data);

            return $this->parse($response);
        } catch (\Throwable $e) {
            Log::error("ApiService POST {$endpoint}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ralat sambungan ke pelayan API.'];
        }
    }

    // ── PUT ──────────────────────────────────────────────────────────────────
    public function put(string $endpoint, array $data = []): array
    {
        try {
            $response = $this->client()->put($endpoint, $data);

            return $this->parse($response);
        } catch (\Throwable $e) {
            Log::error("ApiService PUT {$endpoint}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ralat sambungan ke pelayan API.'];
        }
    }

    // ── DELETE ───────────────────────────────────────────────────────────────
    public function delete(string $endpoint): array
    {
        try {
            $response = $this->client()->delete($endpoint);

            return $this->parse($response);
        } catch (\Throwable $e) {
            Log::error("ApiService DELETE {$endpoint}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ralat sambungan ke pelayan API.'];
        }
    }

    // ── Parse Response ───────────────────────────────────────────────────────
    protected function parse(Response $response): array
    {
        if ($response->status() === 401 && $this->token) {
            // Only force logout when an authenticated API session expires.
            session()->forget(['api_token', 'user', 'roles']);

            return [
                'success' => false,
                'message' => 'Sesi anda telah tamat. Sila log masuk semula.',
                'status'  => 401,
            ];
        }

        $body = $response->json();

        if (! is_array($body)) {
            return [
                'success' => false,
                'message' => 'Respons API tidak sah.',
                'status'  => $response->status(),
            ];
        }

        return array_merge(['status' => $response->status()], $body);
    }
}

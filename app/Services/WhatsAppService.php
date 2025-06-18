<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $instanceId;
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->instanceId = config('services.ultramsg.instance_id');
        $this->token = config('services.ultramsg.token');
        $this->baseUrl = "https://api.ultramsg.com/{$this->instanceId}/messages/chat";
    }

    public function sendMessage(string $to, string $message): array
    {
        $response = Http::asForm()->post($this->baseUrl, [
            'token' => $this->token,
            'to'    => $this->formatPhone($to),
            'body'  => $message,
        ]);

        return $response->json();
    }

    private function formatPhone(string $phone): string
    {
        return str_starts_with($phone, '+') ? $phone : '+237' . ltrim($phone, '0');
    }
}

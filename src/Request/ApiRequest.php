<?php

namespace SwebApi\Request;

use SwebApi\Logger\FileLogger;
use SwebApi\TokenStorage;

class ApiRequest
{
    private FileLogger $logger;
    private TokenStorage $tokenStorage;
    private string $baseUrl;

    public function __construct(
        FileLogger $logger,
        TokenStorage $tokenStorage,
        string $baseUrl
    ) {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->baseUrl = $baseUrl;
    }

    public function send(string $endpoint, string $method, array $data, bool $withToken = true): array
    {
        $url = $this->baseUrl . $endpoint;
        $data = json_encode([
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $data,
        ]);

        $headers = [
            'Content-Type: application/json',
        ];

        if ($withToken) {
            $headers[] = 'Authorization: Bearer ' . $this->tokenStorage->getToken();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->logger->log("Request to {$url}: " . $data);
        $this->logger->log("Response: " . $response);

        if ($response === false) {
            throw new \RuntimeException('API request failed');
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response');
        }

        if (isset($decoded['error']) || $httpCode >= 400) {
            $error = $decoded['error'] ?? 'Unknown error';
            throw new \RuntimeException("API error: {$error['message']}", $httpCode);
        }

        return $decoded;
    }
}
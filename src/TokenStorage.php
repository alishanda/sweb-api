<?php

namespace SwebApi;

class TokenStorage
{
    private string $tokenFile;

    public function __construct(string $tokenFile)
    {
        $this->tokenFile = $tokenFile;
    }

    public function getToken(): ?string
    {
        if (!file_exists($this->tokenFile)) {
            return null;
        }

        $token = file_get_contents($this->tokenFile);
        return $token ?: null;
    }

    public function saveToken(string $token): void
    {
        file_put_contents($this->tokenFile, $token);
    }

    public function clearToken(): void
    {
        if (file_exists($this->tokenFile)) {
            unlink($this->tokenFile);
        }
    }
}
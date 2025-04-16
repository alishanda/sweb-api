<?php

namespace SwebApi\Tests;

use PHPUnit\Framework\TestCase;
use SwebApi\Logger\FileLogger;
use SwebApi\SwebApiClient;
use SwebApi\TokenStorage;

class SwebApiClientTest extends TestCase
{
    private SwebApiClient $client;
    private TokenStorage $tokenStorage;
    private string $logFile;
    private string $tokenFile;

    protected function setUp(): void
    {
        $this->logFile = __DIR__ . '/../logs/test.log';
        $this->tokenFile = __DIR__ . '/../tmp/test_token.txt';

        @mkdir(dirname($this->logFile), 0777, true);
        @mkdir(dirname($this->tokenFile), 0777, true);

        $logger = new FileLogger($this->logFile);
        $this->tokenStorage = new TokenStorage($this->tokenFile);

        $this->client = new SwebApiClient(
            $_ENV['SWEB_LOGIN'] ?? 'test',
            $_ENV['SWEB_PASSWORD'] ?? 'test',
            $logger,
            $this->tokenStorage,
            $_ENV['SWEB_API_URL'] ?? 'https://api.sweb.ru'
        );
    }

    protected function tearDown(): void
    {
        @unlink($this->logFile);
        @unlink($this->tokenFile);
    }

    public function testGetAuthToken(): void
    {
        $token = $this->client->getAuthToken();
        $this->assertNotEmpty($token);
        $this->assertIsString($token);

        $this->assertNotEmpty($this->tokenStorage->getToken());
        $this->assertIsString($this->tokenStorage->getToken());
    }

    public function testAddDomain(): void
    {
        $randomDomain = 'test' . bin2hex(random_bytes(4)) . '.ru';

        $this->client->getAuthToken();
        $result = $this->client->addDomain($randomDomain);

        $this->assertTrue($result);
    }

    public function testAddExistingDomain(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Домен уже добавлен на другой аккаунт');

        $this->client->getAuthToken();
        $this->client->addDomain('example.ru');
    }
}
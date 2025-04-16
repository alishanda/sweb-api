<?php

namespace SwebApi;

use SwebApi\Exception\ApiException;
use SwebApi\Exception\AuthException;
use SwebApi\Logger\FileLogger;
use SwebApi\Request\ApiRequest;

class SwebApiClient
{
    private ApiRequest $request;
    private TokenStorage $tokenStorage;
    private string $login;
    private string $password;

    public function __construct(
        string $login,
        string $password,
        FileLogger $logger,
        TokenStorage $tokenStorage,
        string $baseUrl
    ) {
        $this->login = $login;
        $this->password = $password;
        $this->request = new ApiRequest($logger, $tokenStorage, $baseUrl);
        $this->tokenStorage = $tokenStorage;
    }

    public function getAuthToken(): string
    {
        if ($token = $this->tokenStorage->getToken()) {
            return $token;
        }

        $response = $this->request->send('/notAuthorized', 'getToken', [
                'login' => $this->login,
                'password' => $this->password,
        ], false);

        if (empty($response['result'])) {
            throw new AuthException('Failed to get auth token');
        }

        $this->tokenStorage->saveToken($response['result']);
        return $response['result'];
    }

    public function addDomain(
        string $domain,
        string $prolongType = 'manual',
        ?string $dir = '/тест'
    ): bool {
        $params = [
            'domain' => $domain,
            'prolongType' => $prolongType
        ];

        if ($dir !== null) {
            $params['dir'] = $dir;
        }

        try {
            $response = $this->request->send('/domains', 'move', $params);
            return isset($response['result']) && $response['result'] === 1;
        } catch (\RuntimeException $e) {
            throw new ApiException("Add domain error: " . $e->getMessage(), 0, $e);
        }
    }
}
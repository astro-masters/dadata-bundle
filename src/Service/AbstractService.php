<?php

declare(strict_types=1);

namespace Velhron\DadataBundle\Service;

use JsonException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Velhron\DadataBundle\Exception\DadataException;
use Velhron\DadataBundle\Model\Request\AbstractRequest;
use Velhron\DadataBundle\RequestFactory;
use Velhron\DadataBundle\ResponseFactory;

abstract class AbstractService
{
    /**
     * @var string API-ключ
     */
    protected $token;

    /**
     * @var string Секретный ключ для стандартизации
     */
    protected $secret;

    /**
     * @var HttpClientInterface HTTP-клиент
     */
    protected $httpClient;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    public function __construct(
        string $token,
        string $secret,
        HttpClientInterface $httpClient,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory
    ) {
        $this->token = $token;
        $this->secret = $secret;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws DadataException
     */
    abstract protected function query(AbstractRequest $request): array;

    protected function decodeResponse(ResponseInterface $response): array
    {
        try {
            $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new DadataException($exception);
        }

        if (!is_array($data)) {
            throw new DadataException(new JsonException('Ответ должен содержать объект или массив'));
        }

        return $data;
    }
}

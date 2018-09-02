<?php

namespace OveD\Apm\Request;

use Carbon\Carbon;

class RequestResponseData
{
    const MAX_BODY_LENGTH = 3000;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $requestBody;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $responseBody;

    /**
     * @var int
     */
    private $responseTimeMilliseconds;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var Carbon
     */
    private $requestedAt;
    /**
     * @var int
     */
    private $userId;

    /**
     * RequestResponseData constructor.
     * @param string $uuid
     * @param $userId
     * @param string $requestBody
     * @param string $method
     * @param string $url
     * @param string $ip
     * @param int $statusCode
     * @param string $responseBody
     * @param int $responseTimeMilliseconds
     * @param array $headers
     * @param Carbon $requestedAt
     */
    public function __construct(string $uuid, $userId, string $requestBody, string $method, string $url, string $ip, int $statusCode, string $responseBody, int $responseTimeMilliseconds, array $headers, Carbon $requestedAt)
    {
        $this->uuid = $uuid;
        $this->userId = $userId;
        $this->method = $method;
        $this->url = $url;
        $this->ip = $ip;
        $this->statusCode = $statusCode;
        $this->responseTimeMilliseconds = $responseTimeMilliseconds;
        $this->headers = $headers;
        $this->requestedAt = $requestedAt;

        $this->requestBody = substr($requestBody, 0, self::MAX_BODY_LENGTH);
        $this->responseBody = substr($responseBody, 0, self::MAX_BODY_LENGTH);

        if (strlen($requestBody) >= self::MAX_BODY_LENGTH) {
            $this->requestBody .= '[TRUNCATED]';
        }

        if (strlen($responseBody) >= self::MAX_BODY_LENGTH) {
            $this->responseBody .= '[TRUNCATED]';
        }
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getRequestBody(): string
    {
        return $this->requestBody;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * @return int
     */
    public function getResponseTimeMilliseconds(): int
    {
        return $this->responseTimeMilliseconds;
    }

    /**
     * @return string
     */
    public function getHeaders(): string
    {
        return json_encode($this->headers);
    }

    /**
     * @return Carbon
     */
    public function getRequestedAt(): Carbon
    {
        return $this->requestedAt;
    }
}

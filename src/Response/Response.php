<?php

declare(strict_types=1);
/**
 * This file is part of l1n6yun/aliyun-sls.
 *
 * @link     https://github.com/l1n6yun/aliyun-sls
 * @contact  l1n6yun@gmail.com
 * @license  https://github.com/l1n6yun/aliyun-sls/blob/master/LICENSE
 */

namespace L1n6yun\AliyunSls\Response;

class Response
{
    private array $headers;

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    public function getAllHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $key): string
    {
        return $this->headers[$key] ?? '';
    }

    public function getRequestId(): string
    {
        return $this->headers['x-log-requestid'] ?? '';
    }
}

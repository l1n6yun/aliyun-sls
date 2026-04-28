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

class PutLogsResponse extends Response
{
    /**
     * PutLogsResponse constructor.
     */
    public function __construct(array $headers)
    {
        parent::__construct($headers);
    }
}

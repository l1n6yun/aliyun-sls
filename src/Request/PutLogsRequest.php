<?php

declare(strict_types=1);
/**
 * This file is part of l1n6yun/aliyun-sls.
 *
 * @link     https://github.com/l1n6yun/aliyun-sls
 * @contact  l1n6yun@gmail.com
 * @license  https://github.com/l1n6yun/aliyun-sls/blob/master/LICENSE
 */

namespace L1n6yun\AliyunSls\Request;

class PutLogsRequest extends Request
{
    /**
     * @var string logstore name
     */
    private string $logstore;

    /**
     * @var string topic name
     */
    private string $topic;

    /**
     * @var string source of the logs
     */
    private string $source;

    /**
     * @var array LogItem array, log data
     */
    private array $logitems;

    public function __construct($project = null, $logstore = null, $topic = null, $source = null, $logitems = null)
    {
        parent::__construct($project);
        $this->logstore = $logstore;
        $this->topic = $topic;
        $this->source = $source;
        $this->logitems = $logitems;
    }

    public function getLogstore(): string
    {
        return $this->logstore;
    }

    public function setLogstore(string $logstore): void
    {
        $this->logstore = $logstore;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getLogitems(): array
    {
        return $this->logitems;
    }

    public function setLogitems(array $logitems): void
    {
        $this->logitems = $logitems;
    }
}

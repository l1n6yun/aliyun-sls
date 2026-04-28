<?php

declare(strict_types=1);
/**
 * This file is part of l1n6yun/aliyun-sls.
 *
 * @link     https://github.com/l1n6yun/aliyun-sls
 * @contact  l1n6yun@gmail.com
 * @license  https://github.com/l1n6yun/aliyun-sls/blob/master/LICENSE
 */

namespace L1n6yun\AliyunSls;

class LogItem
{
    /**
     * @var int time of the log item, the default time if the now time
     */
    private $time;

    /**
     * @var array the data of the log item, including many key/value pairs
     */
    private $contents;

    public function __construct($time = null, $contents = null)
    {
        if (! $time) {
            $time = time();
        }
        $this->time = $time;
        if ($contents) {
            $this->contents = $contents;
        } else {
            $this->contents = [];
        }
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): void
    {
        $this->time = $time;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }
}

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

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ClientInterface::class => Client::class,
            ],
            'commands' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for aliyun sls.',
                    'source' => __DIR__ . '/../publish/aliyun_sls.php',
                    'destination' => BASE_PATH . '/config/autoload/aliyun_sls.php',
                ],
            ],
        ];
    }
}

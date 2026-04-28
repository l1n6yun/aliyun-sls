<?php

declare(strict_types=1);
/**
 * This file is part of l1n6yun/aliyun-sls.
 *
 * @link     https://github.com/l1n6yun/aliyun-sls
 * @contact  l1n6yun@gmail.com
 * @license  https://github.com/l1n6yun/aliyun-sls/blob/master/LICENSE
 */
use function Hyperf\Support\env;

return [
    'endpoint' => env('ALIYUN_SLS_ENDPOINT', 'cn-beijing.log.aliyuncs.com'),
    'access_key' => env('ALIYUN_SLS_AK', ''),
    'secret_key' => env('ALIYUN_SLS_SK', ''),
    'project' => env('ALIYUN_SLS_PROJECT', ''),
    'logstore' => env('ALIYUN_SLS_LOGSTORE', ''),
];

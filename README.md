# aliyun-sls

基于 [Hyperf](https://hyperf.io/) 框架的阿里云日志服务（SLS）SDK 组件，支持通过 Protobuf 编码写入日志。

## 环境要求

- PHP >= 8.3
- Hyperf >= 3.1
- Swoole 扩展

## 安装

```bash
composer require l1n6yun/aliyun-sls
```

## 配置

安装后发布配置文件：

```bash
php bin/hyperf.php vendor:publish l1n6yun/aliyun-sls
```

发布后将在 `config/autoload/aliyun_sls.php` 生成配置文件：

```php
return [
    'endpoint' => env('ALIYUN_SLS_ENDPOINT', 'cn-beijing.log.aliyuncs.com'),
    'access_key' => env('ALIYUN_SLS_AK', ''),
    'secret_key' => env('ALIYUN_SLS_SK', ''),
    'project' => env('ALIYUN_SLS_PROJECT', ''),
    'logstore' => env('ALIYUN_SLS_LOGSTORE', ''),
];
```

在 `.env` 文件中配置以下环境变量：

| 环境变量 | 说明 | 示例 |
| --- | --- | --- |
| `ALIYUN_SLS_ENDPOINT` | SLS 服务端点 | `cn-hangzhou.log.aliyuncs.com` |
| `ALIYUN_SLS_AK` | 阿里云 AccessKey ID | `LTAI5t...` |
| `ALIYUN_SLS_SK` | 阿里云 AccessKey Secret | `GJ8qW...` |
| `ALIYUN_SLS_PROJECT` | SLS 项目名称 | `my-project` |
| `ALIYUN_SLS_LOGSTORE` | SLS 日志库名称 | `my-logstore` |

## 使用

### 接入 Monolog Handler（推荐）

通过自定义 Monolog Handler 将日志自动写入 SLS，无需手动调用：

```php
<?php

namespace App\Common\Handler;

use Hyperf\Di\Annotation\Inject;
use L1n6yun\AliyunSls\ClientInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class LogSlsHandler extends AbstractProcessingHandler
{
    #[Inject]
    protected ClientInterface $sls;

    protected function write(LogRecord $record): void
    {
        $saveData = $record['context'];
        $saveData['channel'] = $record['channel'];
        $saveData['message'] = is_array($record['message']) ? json_encode($record['message']) : $record['message'];
        $saveData['level_name'] = $record['level_name'];
        // 阿里云日志不能有空字段
        foreach ($saveData as &$v) {
            if (! $v) {
                $v = 0;
            }
        }
        unset($v);
        $this->sls->putLogs($saveData);
    }
}
```

然后在 `config/autoload/logger.php` 中注册 Handler：

```php
return [
    'default' => [
        'handlers' => [
            [
                'class' => App\Common\Handler\LogSlsHandler::class,
                'constructor' => [
                    'level' => Monolog\Logger::DEBUG,
                ],
            ],
        ],
    ],
];
```

### 直接调用

通过容器注入 `ClientInterface` 即可写入日志：

```php
use L1n6yun\AliyunSls\ClientInterface;

class LogService
{
    public function __construct(private ClientInterface $client) {}

    public function write(): void
    {
        $this->client->putLogs([
            'level' => 'info',
            'message' => 'Hello SLS!',
        ]);
    }
}
```

### 指定 Topic

```php
$this->client->putLogs(
    contents: ['message' => '用户登录'],
    topic: 'user_login'
);
```

### 动态指定 Project 和 Logstore

```php
$this->client->putLogs(
    contents: ['message' => '跨项目日志'],
    topic: 'order',
    project: 'another-project',
    logstore: 'another-logstore'
);
```

## API

### `ClientInterface::putLogs()`

```php
public function putLogs(
    array $contents = [],      // 日志内容，键值对形式
    string $topic = '',        // 日志主题
    ?string $project = null,   // 项目名称，为 null 时使用配置值
    ?string $logstore = null   // 日志库名称，为 null 时使用配置值
);
```

> **限制**：每次写入日志条数上限 4096 条，数据量上限 3MB。

## 相关链接

- [阿里云日志服务文档](https://help.aliyun.com/product/28958.html)
- [Hyperf 框架](https://hyperf.io/)

## License

[MIT](LICENSE)

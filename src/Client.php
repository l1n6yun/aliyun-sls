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

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Guzzle\ClientFactory;
use L1n6yun\AliyunSls\Request\PutLogsRequest;
use L1n6yun\AliyunSls\Response\PutLogsResponse;
use RuntimeException;

use function hyperf\support\make;

class Client implements ClientInterface
{
    public const string API_VERSION = '0.6.0';

    public const string USER_AGENT = 'log-php-sdk-v-0.6.0';

    private \GuzzleHttp\Client $client;

    private ConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        $this->client = $container->get(ClientFactory::class)->create();
        $this->config = $container->get(ConfigInterface::class);
    }

    public function putLogs(array $contents = [], string $topic = '', ?string $project = null, ?string $logstore = null)
    {
        $project = $project ?: $this->config->get('aliyun_sls.project', '');
        $logstore = $logstore ?: $this->config->get('aliyun_sls.logstore', '');

        $source = LogUtil::getLocalIp();
        $logitems = [make(LogItem::class, [time(), $contents])];
        $request = make(PutLogsRequest::class, [$project, $logstore, $topic, $source, $logitems]);

        if (count($request->getLogitems()) > 4096) {
            throw new RuntimeException('PutLogs 接口每次可以写入的日志组数据量上限为4096条!');
        }

        $logGroup = make(LogGroup::class);
        $logGroup->setTopic($request->getTopic());
        $logGroup->setSource($request->getSource());

        foreach ($request->getLogitems() as $logItem) {
            $log = make(Log::class);
            $log->setTime($logItem->getTime());
            $content = $logItem->getContents();
            foreach ($content as $key => $value) {
                $content = make(LogContent::class);
                $content->setKey($key);
                $content->setValue($value);
                $log->addContents($content);
            }

            $logGroup->addLogs($log);
        }

        $body = LogUtil::toBytes($logGroup);
        unset($logGroup);
        $bodySize = strlen($body);
        if ($bodySize > 3 * 1024 * 1024) {
            throw new RuntimeException('PutLogs 接口每次可以写入的日志组数据量上限为3MB!');
        }
        $params = [];
        $headers = [];
        $headers['x-log-bodyrawsize'] = $bodySize;
        $headers['x-log-compresstype'] = 'deflate';
        $headers['Content-Type'] = 'application/x-protobuf';
        $body = gzcompress($body, 6);
        $resource = '/logstores/' . $logstore . '/shards/lb';
        [$resp, $header] = $this->send('POST', $project, $body, $resource, $params, $headers);
        $requestId = isset($header['x-log-requestid']) ? $header['x-log-requestid'][0] : '';
        $this->parseToJson($resp, $requestId);
        return make(PutLogsResponse::class, [$header]);
    }

    private function send(string $method, string $project, string $body, string $resource, array $params, array $headers): array
    {
        $accessKey = $this->config->get('aliyun_sls.access_key', '');
        $secretKey = $this->config->get('aliyun_sls.secret_key', '');
        $endpoint = $this->config->get('aliyun_sls.endpoint', '');

        if ($body) {
            $headers['Content-Length'] = strlen($body);
            $headers['x-log-bodyrawsize'] = $headers['x-log-bodyrawsize'] ?? 0;
            $headers['Content-MD5'] = LogUtil::calMD5($body);
        } else {
            $headers['Content-Length'] = 0;
            $headers['x-log-bodyrawsize'] = 0;
            $headers['Content-Type'] = '';
        }
        $headers['x-log-apiversion'] = self::API_VERSION;
        $headers['x-log-signaturemethod'] = 'hmac-sha1';
        $host = "{$project}.{$endpoint}";
        $headers['Host'] = $host;
        $headers['User-Agent'] = self::USER_AGENT;
        $headers['Date'] = $this->GetGMT();
        $signature = LogUtil::getRequestAuthorization($method, $resource, $secretKey, $params, $headers);
        $headers['Authorization'] = "LOG {$accessKey}:{$signature}";
        $url = "http://{$host}{$resource}";
        if ($params) {
            $url .= '?' . LogUtil::urlEncode($params);
        }

        return $this->sendRequest($method, $url, $body, $headers);
    }

    private function GetGMT(): string
    {
        return gmdate('D, d M Y H:i:s') . ' GMT';
    }

    private function sendRequest(string $method, string $url, string $body, array $headers): array
    {
        try {
            $response = $this->client->request($method, $url, ['body' => $body, 'headers' => $headers]);
            $responseCode = $response->getStatusCode();
            $header = $response->getHeaders();
            $resBody = (string) $response->getBody();
        } catch (Exception|GuzzleException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        $requestId = isset($header['x-log-requestid']) ? $header['x-log-requestid'][0] : '';
        if ($responseCode == 200) {
            return [$resBody, $header];
        }
        $exJson = $this->parseToJson($resBody, $requestId);
        if (isset($exJson['error_code'], $exJson['error_message'])) {
            throw new RuntimeException("{$exJson['error_message']};requestId:{$requestId}", $exJson['error_code']);
        }
        if ($exJson) {
            $exJson = ' The return json is ' . json_encode($exJson);
        } else {
            $exJson = '';
        }
        throw new RuntimeException("Request is failed. Http code is {$responseCode}.{$exJson};requestId:{$requestId}");
    }

    private function parseToJson(string $resBody, string $requestId)
    {
        if (! $resBody) {
            return null;
        }
        $result = json_decode($resBody, true);
        if ($result === null) {
            throw new RuntimeException("Bad format,not json;requestId:{$requestId}");
        }
        return $result;
    }
}

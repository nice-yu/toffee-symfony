<?php
declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * http 请求工具类
 */
class HttpClientUtils
{
    /**
     * 发起请求
     * @param string $url
     * @param string $method
     * @param array $parameter
     * @param array $headers
     * @return array|null
     */
    public static function HttpClient(string $url, string $method, array $parameter = [], array $headers = []): ?array
    {
        if ($method === 'GET') {
            $body = ['query' => $parameter ];
        } else {
            $jsonData = json_encode($parameter, JSON_UNESCAPED_UNICODE);
            $body = [
                'body' => $jsonData,
                'headers' => array_merge([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ], $headers)
            ];
        }

        try {
            /** 发起请求 */
            $client     = HttpClient::create(['verify_peer' => false, 'http_version' => '1.1', 'timeout' => 30]);
            $response   = $client->request($method, $url, $body);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                return null;
            }

            /** 获取到数据信息 */
            return $response->toArray();
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface) {
            return null;
        }
    }

    /**
     * 下载文件内容
     * @param string $url
     * @return array|null ['content' => string, 'contentType' => string]
     * @uses downloadFile
     */
    public static function downloadFile(string $url): ?array
    {
        try {
            $client = HttpClient::create(['verify_peer' => false, 'http_version' => '1.1', 'timeout' => 30]);
            $response = $client->request('GET', $url);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                return null;
            }

            $content = $response->getContent();
            $contentType = $response->getHeaders()['content-type'][0] ?? '';

            return [
                'content' => $content,
                'contentType' => $contentType,
            ];
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface) {
            return null;
        }
    }
}

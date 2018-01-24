<?php
/**
 * Desc: guzzle 并发请求
 * Created by PhpStorm.
 * User: jasong
 * Date: 2017/12/14 15:53
 */

namespace guzzleUsing;

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use guzzleUsing\Library\CalcTime;


class ConcurrentRequests
{
    //send multiple requests concurrently using promises and asynchronous requests
    public function ConcurrentRequestsUsingPromise()
    {
        CalcTime::start();

        $client = new Client(['base_uri' => 'https://www.yundun.com']);

        // Initiate each request but do not block
        $promises = [
            'friendlink' => $client->getAsync('/api/V4/site.friendlink'),
            'report'     => $client->getAsync('/api/V4/site.today.report.allplat')
        ];

        // Wait on all of the requests to complete. Throws a ConnectException
        // if any of the requests fail
        try {
            $results = Promise\unwrap($promises);
            echo $results['friendlink']->getBody();
            echo "\n";
            echo $results['report']->getBody();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

        // Wait for the requests to complete, even if some of them fail
        $results = Promise\settle($promises)->wait();

        // You can access each result using the key provided to the unwrap
        // function.
        echo $results['friendlink']['value']->getHeader('Content-Length')[0];
        echo $results['report']['value']->getHeader('Content-Length')[0];

        CalcTime::end();
        CalcTime::echoUseTime(__FUNCTION__);
    }

    //use the GuzzleHttp\Pool object when you have an indeterminate amount of requests you wish to send
    public function ConcurrentRequestsUsingPool()
    {
        CalcTime::start();

        $client = new Client();

//        $requests = function ($total) {
//            $uri = 'http://apiv4.yundun.cn/';
//            for ($i = 0; $i < $total; $i++) {
//                yield new Request('GET', $uri);
//            }
//        };

        $requests = function ($total) use ($client) {
            $uri = 'http://apiv4.yundun.cn/';
            for ($i = 0; $i < $total; $i++) {
                yield function () use ($client, $uri) {
                    return $client->getAsync($uri);
                };
            }
        };


        $pool = new Pool($client, $requests(100), [
            'concurrency' => 100,
            'fulfilled'   => function ($response, $index) {
                // this is delivered each successful response
                echo 'index:' . $index . "\n";
                echo "fulfilled\n";
            },
            'rejected'    => function ($reason, $index) {
                // this is delivered each failed request
                echo 'index:' . $index . "\n";
                echo "rejected\n";
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        CalcTime::end();
        CalcTime::echoUseTime(__FUNCTION__);
    }


    public function syncRequest()
    {
        CalcTime::start();
        $uri    = 'http://www.baidu.com/';
        $client = new Client();
        for ($i = 0; $i < 100; $i++) {

            $response = $client->get($uri);
            echo $response->getStatusCode();
        }
        CalcTime::end();
        CalcTime::echoUseTime(__FUNCTION__);
    }

    public function asyncRequest()
    {
        CalcTime::start();
        $uri    = 'http://www.baidu.com/';
        $client = new Client();
        for ($i = 0; $i < 100; $i++) {

            $promise = $client->getAsync($uri);
            $promise->then(
                function (ResponseInterface $res) {
                    echo $res->getStatusCode() . "\n";
                },
                function (RequestException $e) {
                    echo $e->getMessage() . "\n";
                    echo $e->getRequest()->getMethod();
                }
            );
            $promise->wait();
        }

        CalcTime::end();
        CalcTime::echoUseTime(__FUNCTION__);
    }

}

$concurrentRequestsObj = new ConcurrentRequests();
echo "<============ ConcurrentRequestsUsingPromise call start ========>\n";
//$concurrentRequestsObj->ConcurrentRequestsUsingPromise();
echo "<============ ConcurrentRequestsUsingPromise call end   =========>\n";


echo "<============ ConcurrentRequestsUsingPool call start ========>\n";
$concurrentRequestsObj->ConcurrentRequestsUsingPool();
echo "<============ ConcurrentRequestsUsingPool call end   =========>\n";


echo "<============ syncRequest call start ========>\n";
$concurrentRequestsObj->syncRequest();
echo "<============ syncRequest call end   =========>\n";


echo "<============ asyncRequest call start ========>\n";
$concurrentRequestsObj->asyncRequest();
echo "<============ asyncRequest call end   =========>\n";
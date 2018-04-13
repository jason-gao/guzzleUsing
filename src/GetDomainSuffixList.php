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


class GetDomainSuffixList
{

    public function getDomainSuffixList()
    {
        $uri    = 'https://publicsuffix.org/list/public_suffix_list.dat';
        $client = new Client();

        $response = $client->get($uri);

        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
            $body       = $response->getBody();
            $suffixList = $this->formatSuffixList($body);

            return $suffixList;
        }

        return [];
    }


    public function formatSuffixList($body)
    {
        $arr = array_filter(explode("\n", $body));

        foreach ($arr as $key => $line) {
            if (false !== strpos($line, '//')) {
                unset($arr[$key]);
            }
        }

        return array_values($arr);
    }


}

$GetDomainSuffixListObj = new GetDomainSuffixList();

$suffixList = $GetDomainSuffixListObj->getDomainSuffixList();

print_r($suffixList);

<?php

namespace Rs\VersionEye\Api;

use Rs\VersionEye\Http\HttpClient;

/**
 * BaseApi
 *
 * @author Robert Schönthal <robert.schoenthal@sinnerschrader.com>
 */
abstract class BaseApi
{
    /**
     * @var HttpClient
     */
    protected $client;
    private $token;

    /**
     * @param HttpClient $client
     * @param string     $token
     */
    public function __construct(HttpClient $client, $token = null)
    {
        $this->client = $client;
        $this->token = $token;
    }

    /**
     * performs the request
     *
     * @param  string $url
     * @param  string $method
     * @param  array  $params
     * @return array
     */
    protected function request($url, $method = 'GET', array $params = [])
    {
        if ($this->token) {
            $delimiter = strstr($url, '?') ? '&' : '?';

            $url = sprintf('%s%sapi_key=%s', $url, $delimiter, $this->token);
        }

        $url = $this->sanitizeQuery($url);

        return $this->client->request($method, $url, $params);
    }

    /**
     * converts names to the needed url path format
     *
     * @param  string $name
     * @return string
     */
    protected function transform($name)
    {
        return str_replace(['/', '.'], [':', '~'], $name);
    }

    /**
     * removes empty query string parameters
     *
     * @param string $query
     * @return string
     */
    private function sanitizeQuery($query)
    {
        $parts = parse_url($query);
        $path = $parts['path'];

        if (!isset($parts['query'])) {
            return $query;
        }

        $vars = explode('&', $parts['query']);

        $final = array();

        if(!empty($vars)) {
            foreach($vars as $var) {
                $parts = explode('=', $var);

                $key = $parts[0];
                $val = $parts[1];

                if(!array_key_exists($key, $final) && !empty($val))
                    $final[$key] = $val;
            }
        }

        return $path.'?'.http_build_query($final);
    }
}

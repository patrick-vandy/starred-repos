<?php

namespace framework\model;

use framework\core\Config;
use framework\core\Model;


/**
 * Class GitHubAPI for interacting with the GitHub API.
 *
 * @package framework\model
 */
class GitHubAPI extends Model
{

    const API_URL = 'https://api.github.com';

    private $token;


    /**
     * GitHubAPI constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->token = Config::get('github_token', false);
    }


    /**
     * Searches GitHub repositories using their search API.
     *
     * $term is the text to search for. It can also include
     * specific syntax such as language:php (see GitHub API
     * docs for details)
     *
     * $sort is the field to sort by and $order is the sort
     * direction (ASC or DESC)
     *
     * @param string $term
     * @param string $sort
     * @param string $order
     * @return array
     * @throws \Exception
     */
    public function search_repos($term, $sort = '', $order = '')
    {
        $url = self::API_URL . '/search/repositories';

        $query = ['q' => $term, 'sort' => $sort, 'order' => $order];

        if ($this->token)
        {
            $query['access_token'] = $this->token;
        }

        $url .= '?' . http_build_query($query);

        return $this->make_request($url);
    }


    /**
     * Make a request to the GitHub api using the URL provided.
     *
     * $opts is an array the can be used to override or provide
     * extra CURL options.
     *
     * The response is parsed and returns an array with keys for
     * 'headers' and 'body'. Headers are further parsed into an
     * associative array where the key is the header name and the
     * value is the header value. The name used for the key is
     * converted to lower case for consistency.
     *
     * If the curl request returns a false response or a non success
     * http status code an exception is thrown.
     *
     * @param string $url
     * @param array $extra_opts
     * @return array
     * @throws \Exception
     */
    public function make_request($url, $extra_opts = [])
    {
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: PHP-Assessment-App'
        ];

        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => 1
        ];

        foreach ($extra_opts as $opt => $value)
        {
            $opts[$opt] = $value;
        }

        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);

        if ($response === false)
        {
            throw new \Exception(curl_error($ch));
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status >= 400)
        {
            throw new \Exception("$status status code returned from cURL request!");
        }

        return $this->_parse_response($response);
    }


    /**
     * Parse a response from a REST call.
     *
     * Returns an associative array of 'headers' and 'body'. Headers
     * are parsed into an associative array where the key is the header
     * name (converted to lowercase for consistency).
     *
     * If content-type is json the body is decoded using json_decode and
     * body becomes an associative array of the json key/value pairs.
     *
     * @param $response
     * @return array
     */
    private function _parse_response($response)
    {
        $headers = [];

        list ($raw_headers, $raw_body) = explode("\r\n\r\n", $response, 2);

        foreach (explode("\r\n", $raw_headers) as $header)
        {
            if (strpos($header, ':') !== false)
            {
                list ($name, $value) = explode(':', $header, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }

        if (!empty($headers['link']))
        {
            $headers['link'] = $this->_parse_link_header($headers['link']);
        }

        if (strpos($headers['content-type'], 'json') !== false)
        {
            $body = json_decode($raw_body, true);
        }
        else
        {
            $body = $raw_body;
        }

        return [
            'headers' => $headers,
            'body' => $body
        ];
    }


    /**
     * Parse a link header into an associative array.
     *
     * If a link header follows standard format, the header is
     * parsed and returns an array with each URL as an element
     * and the rel for that URL as the element's key.
     *
     * @param $header
     * @return array
     */
    private function _parse_link_header($header)
    {
        $links = [];

        foreach (explode(',', $header) as $link)
        {
            list ($url, $rel) = explode('; rel=', trim($link), 2);
            $url = trim($url, '<>');
            $rel = trim($rel, '"');
            $links[$rel] = $url;
        }

        return $links;
    }

}
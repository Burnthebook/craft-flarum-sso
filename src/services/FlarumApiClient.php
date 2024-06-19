<?php

namespace burnthebook\craftflarumsso\services;

use Craft;
use yii\log\Logger;
use Josantonius\Cookie\Cookie;
use GuzzleHttp\Client as HttpClient;

class FlarumApiClient 
{
    /**
     * Init Flarum PHP API Client
     * 
     * @param   string $endpoint The URL (with http://) to your Flarum installation
     * @param   string $apiKey The API Key set in the Flarum api_keys database table
     * @param   array $cookieOptions The Options for the Cookie set by this API Client
     * 
     * Note that your Cookie Options must contain the required key of 'domain'.
     * This domain must be the domain name of your Flarum installation.
     * Because we are using cookies it is recommended that 
     * your Flarum and SSO are on the same root domain.
     *
     * sso.domain.com and flarum.domain.com will have a config of:
     * @example url://dev/null  ['cookie_options' => ['domain' => 'domain.com']]
     */
    public function __construct(
        public string $endpoint, 
        public string $apiKey, 
        public array $cookieOptions
    ) {}

    /**
     * Make a request to the Flarum API
     * 
     * @param   string $method Request Method, HTTP Verb, <GET,POST,PUT,PATCH,DELETE>
     * @param   string $url The API endpoint to call, e.g. /api/token
     * @param   array $options An array of options containing `form_params` and `authorization`
     * 
     * @return  array ['error' => bool, 'data' => ?array|string, 'errors' => ?array]
     */
    public function request(string $method, string $url, array $options)
    {
        // Init HttpClient
        $client = new HttpClient(['base_uri' => $this->endpoint]);
        // Trim any trailing slashes from endpoint.
        $endpoint = rtrim($this->endpoint, '/');
        try {
            // Set Default headers with UA and Accept
            $headers = [
                'User-Agent' => 'php-flarum-api-client/1.0',
                'Accept' => 'application/vnd.api+json, application/json',
            ];

            // Get authorization header
            if (empty($options['authorization'])) {
                $headers['Authorization'] = 'Token ' . $this->apiKey;
            } else {
                $headers['Authorization'] = $options['authorization'];
            }

            // Merge the headers into options
            $options['headers'] = $headers;

            // Make the request to the API
            $response = $client->request($method, $endpoint . $url, $options);

            // Get the response body as a stdClass
            $body = json_decode($response->getBody());

            // return standardised data format
            return ['error' => false, 'data' => $body, 'errors' => []];

        } catch(\GuzzleHttp\Exception\ClientException $e) {
            // Got an error?
            $response = $e->getResponse();

            // Get the response body as a stdClass
            $body = json_decode($response->getBody());

            // Log the error
            Craft::getLogger()->log("Error in request. \r\n URL: " . $url . " \r\nException: " . $response->getBody(), Logger::LEVEL_INFO, 'flarum-sso');

            // return standardised data format
            return ['error' => true, 'data' => $response->getReasonPhrase(), 'errors' => $body->errors];
        }
    }

    /**
     * Get a token from the Flarum REST API 
     * 
     * @url POST {$this->endpoint}/api/token
     * 
     * @param   string $username
     * @param   string $password
     * 
     * @return  array ['error' => bool, 'data' => ?array|string, 'errors' => ?array]
     */
    public function getToken(string $username, string $password) : array 
    {
        return $this->request('POST', '/api/token', [
            'form_params' => [
                'identification' => $username,
                'password' => $password,
            ]
        ]);
    }

    /**
     * Check if a user exists in Flarum
     * 
     * @param   string $username The username to check
     * 
     * @return  bool Indicates whether the user exists or not.
     */
    public function checkUserExists(string $username) : bool
    {
        $check = $this->getUserByName($username);
        return (($check['error'] == false) && ($check['data']->data->id));
    }

    /**
     * Get the user by their username
     * 
     * @param   string $username The username to check
     * 
     * @return  array API Response
     */
    public function getUserByName(string $username) : array
    {
        return $this->request('GET', '/api/users/'. $username . '?bySlug=1', []);
    }

    /**
     * Create an account in Flarum
     * 
     * @param   array $userDetails ['username' => string, 'email' => string, 'password' => string]
     * 
     * @return  array ['error' => bool, 'data' => ?array|string, 'errors' => ?array]
     */
    public function createAccount(array $userDetails)
    {
        return $this->request('POST', '/api/users', [
            'form_params' => [
                'data' => [
                    'attributes' => [
                        'username' => $userDetails['username'],
                        'email' => $userDetails['email'],
                        'password' => $userDetails['password'],
                    ]
                ]
            ]
        ]);
    }

    /**
     * Change an account password in Flarum
     * 
     * @param   array $user ['username' => string, 'email' => string, 'password' => string]
     * 
     * @return  array ['error' => bool, 'data' => ?array|string, 'errors' => ?array]
     */
    public function changePassword(array $user)
    {
        $flarumUser = $this->getUserByName($user['username']);
        $flarumUserId = (int) $flarumUser['data']->data->id;

        return $this->request('PATCH', '/api/users/' . $flarumUserId, [
            'json' => [
                'data' => [
                    'attributes' => [
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'password' => $user['password'],
                    ]
                ]
            ]
        ]);
    }

    /**
     * Sets a cookie
     * 
     * @param   string $name The name of the cookie
     * @param   string $payload The payload to store as the cookie value
     * @param   bool $longLived Is this cookie a long lived cookie, e.g. remember cookie
     * 
     * @return \Josantonius\Cookie\Cookie
     */
    public function setCookie(string $name, string $payload, bool $longLived = false) : Cookie
    {
        $cookie = new Cookie(
            domain: $this->cookieOptions['domain'],
            expires: $longLived ? 'now +1 year' : 'now +1 hour',
            httpOnly: array_key_exists('http_only', $this->cookieOptions) ? $this->cookieOptions['http_only'] : true,
            path: array_key_exists('path', $this->cookieOptions) ? $this->cookieOptions['path'] : '/',
            raw: true,
            sameSite: array_key_exists('same_site', $this->cookieOptions) ? $this->cookieOptions['same_site'] : 'strict',
            secure: array_key_exists('secure_only', $this->cookieOptions) ? $this->cookieOptions['secure_only'] : true,
        );

        $prefix = array_key_exists('prefix', $this->cookieOptions) ? $this->cookieOptions['prefix'] : 'flarum_';

        $cookie->set($prefix . $name, $payload);

        return $cookie;
    }

    /**
     * Removes a cookie
     * 
     * @param   string $name The name of the cookie
     */
    public function deleteCookie(string $name) : Cookie
    {
        $cookie = new Cookie(
            domain: $this->cookieOptions['domain'],
            expires: 'now +1 minute',
            httpOnly: array_key_exists('http_only', $this->cookieOptions) ? $this->cookieOptions['http_only'] : true,
            path: array_key_exists('path', $this->cookieOptions) ? $this->cookieOptions['path'] : '/',
            raw: true,
            sameSite: array_key_exists('same_site', $this->cookieOptions) ? $this->cookieOptions['same_site'] : 'strict',
            secure: array_key_exists('secure_only', $this->cookieOptions) ? $this->cookieOptions['secure_only'] : true,
        );
        
        $prefix = array_key_exists('prefix', $this->cookieOptions) ? $this->cookieOptions['prefix'] : 'flarum_';
        
        $cookie->pull($prefix . $name);
        $cookie->remove($prefix . $name);

        return $cookie;
    }
}
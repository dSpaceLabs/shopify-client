<?php
/**
 */

namespace Dspacelabs\Component\Shopify;

/**
 * Shopify Client
 */
class Client
{
    /**
     * List of Shopify scopes
     */
    const SCOPE_READ_CONTENT       = 'read_content';
    const SCOPE_WRITE_CONTENT      = 'write_content';
    const SCOPE_READ_THEMES        = 'read_themes';
    const SCOPE_WRITE_THEMES       = 'write_themes';
    const SCOPE_READ_PRODUCTS      = 'read_products';
    const SCOPE_WRITE_PRODUCTS     = 'write_products';
    const SCOPE_READ_CUSTOMERS     = 'read_customers';
    const SCOPE_WRITE_CUSTOMERS    = 'write_customers';
    const SCOPE_READ_ORDERS        = 'read_orders';
    const SCOPE_WRITE_ORDERS       = 'write_orders';
    const SCOPE_READ_SCRIPT_TAGS   = 'read_script_tags';
    const SCOPE_WRITE_SCRIPT_TAGS  = 'write_script_tags';
    const SCOPE_READ_FULFILLMENTS  = 'read_fulfillments';
    const SCOPE_WRITE_FULFILLMENTS = 'write_fulfillments';
    const SCOPE_READ_SHIPPING      = 'read_shipping';
    const SCOPE_WRITE_SHIPPING     = 'write_shipping';

    /**
     * API Key
     *
     * @var string
     */
    protected $key;

    /**
     * Secret Key
     *
     * @var string
     */
    protected $secret;

    /**
     * @var string|null
     */
    protected $shop;

    /**
     * @var string|null
     */
    protected $token;

    /**
     * @var array
     */
    protected $scopes;

    /**
     * Initialize a shopify client by passing in the API Key and the Secret
     *
     * @api
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->key    = $key;
        $this->secret = $secret;
        $this->scopes = array();
    }

    /**
     * Set the shopify shop that this is being used for
     *
     * Accepted values:
     * - example
     * - example.myshopify.com
     *
     * @api
     * @param string $shop
     * @return self
     */
    public function setShop($shop)
    {
        $this->shop = preg_replace('/\.myshopify\.com/i', '', $shop);

        return $this;
    }

    /**
     * Returns the shopify shop that the client is set to. It will return null
     * if no shopify shop has been set
     *
     * @api
     * @return string|null
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set all the scopes at once. This WILL overwrite any previous scopes you
     * had set.
     *
     * @api
     * @param array $scopes
     * @return self
     */
    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * Add a scope to the object
     *
     * @api
     * @param string $scope
     * @return self
     */
    public function addScope($scope)
    {
        $this->scopes[] = $scope;

        return $this;
    }

    /**
     * Returns an array of scopes that have been set
     *
     * @api
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Set the access token to use with requests
     *
     * @api
     * @param string $token
     * @return self
     */
    public function setAccessToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Generates the URI used to authorize application
     *
     * @param string $redirectUri
     * @param integer $nonce
     * @return string
     */
    public function getAuthorizationUrl($redirectUri, $nonce)
    {
        $url = 'https://';
        $url .= $this->shop;
        $url .= '.myshopify.com/admin/oauth/authorize?client_id=';
        $url .= $this->key;
        $url .= '&scope=';
        $url .= urlencode(implode(',', $this->scopes));
        $url .= '&redirect_uri=';
        $url .= urlencode($redirectUri);
        $url .= '&state=';
        $url .= $nonce;

        return $url;
    }

    /**
     * Returns the Access Token or false on failure
     *
     * @param string $code
     * @return string|false
     */
    public function getAccessToken($code)
    {
        if (null != $this->token) {
            return $this->token;
        }

        $url = 'https://';
        $url .= $this->shop;
        $url .= '.myshopify.com/admin/oauth/access_token?client_id=';
        $url .= $this->key;
        $url .= '&client_secret=';
        $url .= $this->secret;
        $url .= '&code=';
        $url .= $code;

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (null === $response || !isset($response['access_token'])) {
            return false;
        }

        return $this->token = $response['access_token'];
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return 'https://'.$this->shop.'.myshopify.com';
    }

    /**
     */
    public function call($method, $path, $body = null)
    {
        $method = strtoupper($method);
        $url = $this->getBaseUri().$path;
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => array(
                'X-Shopify-Access-Token: '.$this->token
            ),
        ));
        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? http_build_query($body) : $body);
        } elseif ('GET' === $method && is_array($body) && !empty($body)) {
            $url = sprintf('%s?%s', $url, http_build_query($body));
            //var_dump($url);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        $response = json_decode(curl_exec($ch), true);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno || $error) {
            throw new \Exception(sprintf('%s: %s', $errno, $error));
        }

        if (isset($response['errors'])) {
            //var_dump($response);
            if (is_array($response['errors'])) {
                $msg = array();
                foreach ($response['errors'] as $k => $v) {
                    $msg[] = sprintf('%s: %s', $k, $v[0]);
                }

                throw new \Exception(implode($msg, "\n"));
            }
            throw new \Exception($response['errors']);
        }

        return $response;
    }

    /**
     * Validates request query
     *
     * @param array $query
     * @return boolean
     */
    public function isValid(array $query)
    {
        if (empty($query['hmac']) || empty($query['signature'])) {
            return false;
        }

        $hmac      = $query['hmac'];
        $signature = $query['signature'];

        unset($query['hmac'], $query['signature']);

        ksort($query);

        $parts = array();
        foreach ($query as $k => $v) {
            $parts[] = sprintf('%s=%s', $k, $v);
        }
        $msg = implode('&', $parts);
        $digest = hash_hmac('sha256', $msg, $this->secret);

        return ($digest === $hmac);
    }
}

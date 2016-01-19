<?php
/**
 */

namespace Dspacelabs\Component\Shopify;

/**
 */
class Client
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $shop;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $scopes;

    /**
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * @param string $shop
     * @return self
     */
    public function setShop($shop)
    {
        $this->shop = preg_replace('/\.myshopify\.com/i', '', $shop);

        return $this;
    }

    /**
     * @return string
     */
    public function getShop()
    {
        return $this->shop;
    }

    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
    }

    public function setAccessToken($token)
    {
        $this->token = $token;
    }

    public function getAuthorizationUrl($redirectUri, $nonce)
    {
        $url = 'https://';
        $url .= $this->shop;
        $url .= '.myshopify.com/admin/oauth/authorize?client_id=';
        $url .= $this->key;
        $url .= '&scope=';
        $url .= urlencode($this->scopes);
        $url .= '&redirect_uri=';
        $url .= urlencode($redirectUri);
        $url .= '&state=';
        $url .= $nonce;

        return $url;
    }

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

    public function getBaseUri()
    {
        return 'https://'.$this->shop.'.myshopify.com';
    }

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

    public function isValid($query)
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

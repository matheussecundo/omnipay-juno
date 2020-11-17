<?php

/**
 * Stripe Abstract Request.
 */

namespace Omnipay\Juno\Message;

require_once __DIR__.'/../../../../../vendor/autoload.php';

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Omnipay\Common\Exception\InvalidRequestException;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveBaseUrl = 'https://api.juno.com.br';
    protected $testBaseUrl = 'https://sandbox.boletobancario.com/api-integration';

    protected $authLiveBaseUrl = 'https://api.juno.com.br/authorization-server';
    protected $authTestBaseUrl = 'https://sandbox.boletobancario.com/authorization-server';
    protected $authContentType = 'application/x-www-form-urlencoded';
    protected $authAccept = 'application/json;charset=UTF-8';
    protected $authEndpoint = 'oauth/token';

    public function getClientId()
    {
        return $this->getParameter('clientId');
    }

    public function setClientId($value)
    {
        return $this->setParameter('clientId', $value);
    }

    public function getClientSecret()
    {
        return $this->getParameter('clientSecret');
    }

    public function setClientSecret($value)
    {
        return $this->setParameter('clientSecret', $value);
    }

    public function getJunoVersion()
    {
        return $this->getParameter('junoVersion');
    }

    public function setJunoVersion($value)
    {
        return $this->setParameter('junoVersion', $value);
    }

    public function getResourceToken()
    {
        return $this->getParameter('resourceToken');
    }

    public function setResourceToken($value)
    {
        return $this->setParameter('resourceToken', $value);
    }

    public function getContentType()
    {
        return 'application/json;charset=UTF-8';
    }

    public function getAccept()
    {
        return 'application/json;charset=UTF-8';
    }

    public function getBaseUrl()
    {
        if ($this->getTestMode() == FALSE)
        {
            return $this->liveBaseUrl;
        }
        return $this->testBaseUrl;
    }

    private function getAuthBaseUrl()
    {
        if ($this->getTestMode() == FALSE)
        {
            return $this->authLiveBaseUrl;
        }
        return $this->authTestBaseUrl;
    }

    private function getAuthorization()
    {
        $cache = new FilesystemAdapter();

        $junoBearerToken = $cache->get('junoBearerToken', function (ItemInterface $item) {
            $url = $this->urlMerge($this->getAuthBaseUrl(), $this->authEndpoint);

            $headers = [
                'Authorization' => $this->getBasicAuthorization(),
                'Content-Type' => $this->authContentType,
                'Accept' => $this->authAccept,
            ];
    
            $data = ['grant_type' => 'client_credentials'];
            $body = http_build_query($data, '', '&');
    
            $httpResponse = $this->httpClient->request(
                'POST',
                $url,
                $headers,
                $body,
            );
    
            $responseBody = $httpResponse->getBody()->getContents();
    
            var_dump($url, $responseBody);

            $data = json_decode($responseBody, true);
    
            $item->expiresAfter($data['expires_in']);
        
            return $data['access_token'];
        });

        return 'Bearer ' . $junoBearerToken;
    }

    private function getBasicAuthorization()
    {
        return 'Basic ' . base64_encode($this->getClientId() . ':' . $this->getClientSecret());
    }

    abstract public function getHttpMethod();

    abstract public function getEndpoint();

    /**
     * @return array
     */
    public function getHeaders()
    {
        $headers = array();

        if ($this->getJunoVersion()) {
            $headers['X-Api-Version'] = $this->getJunoVersion();
        }

        if ($this->getResourceToken()) {
            $headers['X-Resource-Token'] = $this->getResourceToken();
        }

        return $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data)
    {
        $headers = array_merge(
            $this->getHeaders(),
            array('Authorization' => $this->getAuthorization()),
            array('Content-Type' => $this->getContentType()),
            array('Accept' => $this->getAccept()),
        );

        $body = $data ? json_encode($data) : null;

        $httpResponse = $this->httpClient->request(
            $this->getHttpMethod(),
            $this->urlMerge($this->getBaseUrl(), $this->getEndpoint()),
            $headers,
            $body
        );

        return $this->createResponse($httpResponse->getBody()->getContents(), $httpResponse->getHeaders());
    }

    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }


    /**
     * Combine two urls.
     *
     * The urls can be either a string or url parts that consist of:
     *
     *     scheme, host, port, user, pass, path, query, fragment
     *
     * If passed in as parts in an array, the query parameter can be either
     * a string or an array of name/value key pairs.  The query parameters
     * will be added on to the ones from the original url.  If you want to
     * remove query parameters, or any other parts of the url, you need to
     * pass the value in as null.
     *
     * Examples:
     *
     *     urlMerge(
     *         '/tests/section/people?id=9405',
     *         array('query' => array('found' => true, 'id' => null))
     *     );
     *
     *     urlMerge(
     *         'http://www.example.com/',
     *         array('scheme' => 'https', 'query' => 'foo=bar&test=1'
     *     );
     *
     *     urlMerge(
     *         array('path' => '/tests/item', 'query' => 'id=9405'),
     *         'http://www.example.com'
     *     );
     *
     * @param  string|array $original
     * @param  string|array $new
     * @return string
     */
    private function urlMerge($original, $new)
    {
        if (is_string($original)) {
            $original = parse_url($original);
        }
        if (is_string($new)) {
            $new = parse_url($new);
        }
        $qs = null;
        if (!empty($original['query']) && is_string($original['query'])) {
            parse_str($original['query'], $original['query']);
        }
        if (!empty($new['query']) && is_string($new['query'])) {
            parse_str($new['query'], $new['query']);
        }
        if (isset($original['query']) || isset($new['query'])) {
            if (!isset($original['query'])) {
                $qs = $new['query'];
            } elseif (!isset($new['query'])) {
                $qs = $original['query'];
            } else {
                $qs = array_merge($original['query'], $new['query']);
            }
        }
        $result = array_merge($original, $new);
        $result['query'] = $qs;
        foreach ($result as $k => $v) {
            if ($v === null) {
                unset($result[$k]);
            }
        }
        if (!empty($result['query'])) {
            $result['query'] = http_build_query($result['query']);
        }
        if ($result['path'][0] != '/') {
            $result['path'] = "/{$result['path']}";
        }
        return (isset($result['scheme']) ? "{$result['scheme']}://" : '')
            . (isset($result['user']) ? $result['user']
                . (isset($result['pass']) ? ":{$result['pass']}" : '').'@' : '')
            . (isset($result['host']) ? $result['host'] : '')
            . (isset($result['port']) ? ":{$result['port']}" : '')
            . (isset($result['path']) ? $result['path'] : '')
            . (!empty($result['query']) ? "?{$result['query']}" : '')
            . (isset($result['fragment']) ? "#{$result['fragment']}" : '');
    }
}
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
    protected $authEndpoint = 'oauth/token';
    

    public function getContentType()
    {
        return 'application/json;charset=UTF-8';
    }

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
            $headers = [
                'Authorization' => $this->getBasicAuthorization(),
                'Content-Type' => $this->authContentType,
            ];
    
            $data = ['grant_type' => 'client_credentials'];
            $body = http_build_query($data, '', '&');
    
            $httpResponse = $this->httpClient->request(
                'POST',
                $this->getAbsoluteURL($this->getAuthBaseUrl(), $this->authEndpoint),
                $headers,
                $body,
            );
    
            $responseBody = $httpResponse->getBody()->getContents();
    
            var_dump($responseBody);
    
            $item->expiresAfter($responseBody['expires_in']);
        
            return $responseBody['access_token'];
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
        );

        $body = $data ? json_encode($data) : null;

        $httpResponse = $this->httpClient->request(
            $this->getHttpMethod(),
            $this->getAbsoluteURL($this->getBaseUrl(), $this->getEndpoint()),
            $headers,
            $body
        );

        return $this->createResponse($httpResponse->getBody()->getContents(), $httpResponse->getHeaders());
    }

    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }

    private function getAbsoluteURL($root, $endpoint)
    {
        $url_parts = parse_url($endpoint);
        return http_build_url($root, $url_parts, HTTP_URL_JOIN_PATH);
    }
}
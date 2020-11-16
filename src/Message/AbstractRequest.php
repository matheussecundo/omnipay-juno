<?php

/**
 * Stripe Abstract Request.
 */

namespace Omnipay\Juno\Message;

use Omnipay\Common\Exception\InvalidRequestException;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{

    public function getLiveRootUrl()
    {
        return 'https://api.juno.com.br';
    }

    public function getTestRootUrl()
    {
        return 'https://sandbox.boletobancario.com/api-integration';
    }

    public function getContentType()
    {
        return 'application/json;charset=UTF-8';
    }

    public function getTestMode()
    {
        return $this->getParameter('testMode');
    }

    public function setTestMode($value)
    {
        return $this->setParameter('testMode', $value);
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

    public function getRootUrl()
    {
        if ($this->getTestMode() == FALSE)
        {
            return $this->getLiveRootUrl();
        }
        return $this->getTestRootUrl();
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
            array('Authorization' => 'Basic ' . base64_encode($this->getClientId() . ':' . $this->getClientSecret())),
            array('Content-Type' => $this->getContentType()),
        );

        $body = $data ? http_build_query($data, '', '&') : null;

        $httpResponse = $this->httpClient->request(
            $this->getHttpMethod(),
            $this->getAbsoluteURL($this->getRootUrl(), $this->getEndpoint()),
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
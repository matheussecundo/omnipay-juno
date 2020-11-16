<?php

namespace Omnipay\Juno\Message;

use Omnipay\Common\Exception\InvalidRequestException;

class AuthorizeRequest extends AbstractRequest
{

    public function getLiveRootUrl()
    {
        return 'https://api.juno.com.br/authorization-server';
    }

    public function getTestRootUrl()
    {
        return 'https://sandbox.boletobancario.com/authorization-server';
    }

    public function getContentType()
    {
        return 'application/x-www-form-urlencoded';
    }

    public function getHttpMethod()
    {
        return 'POST';
    }

    public function getEndpoint()
    {
        return 'oauth/token';
    }

    public function getData()
    {
        $data = [
            'grant_type' => 'client_credentials',
        ];

        return $data;
    }

    protected function createResponse($data)
    {
        return $this->response = new AuthorizeResponse($this, $data);
    }
}
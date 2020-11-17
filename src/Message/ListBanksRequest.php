<?php

namespace Omnipay\Juno\Message;

use Omnipay\Common\Exception\InvalidRequestException;

class ListBanksRequest extends AbstractRequest
{

    public function getHttpMethod()
    {
        return 'GET';
    }

    public function getEndpoint()
    {
        return 'data/banks';
    }

    public function getData()
    {
        $data = [
            
        ];

        return $data;
    }
}
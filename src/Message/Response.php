<?php

namespace Omnipay\Juno\Message;

use Omnipay\Common\Message\AbstractResponse;

class Response extends AbstractResponse
{
    /**
     * @var array
     */
    protected $headers = [];

    public function __construct($request, $data, $headers = [])
    {
        $this->request = $request;
        $this->data = json_decode($data, true);
        $this->headers = $headers;
    }

    public function isSuccessful()
    {
        return !isset($this->data['error']);
    }

    public function getResourceToken()
    {
        return isset($this->data['resourceToken']) ? $this->data['resourceToken'] : NULL;
    }
}
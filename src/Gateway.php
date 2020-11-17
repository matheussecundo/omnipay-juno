<?php

namespace Omnipay\Juno;

use Omnipay\Common\AbstractGateway;
use Message;

/**
 * Gateway Class
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Juno';
    }

    /**
     * @param array $parameters
     * @return ListBanksRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest(
            Message\ListBanksRequest::class,
            $parameters
        );
    }
}
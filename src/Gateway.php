<?php

namespace Omnipay\Juno;

use Omnipay\Common\AbstractGateway;
use Omnipay\Juno\Message;

/**
 * Gateway Class
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Juno';
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

    public function getTestClientId()
    {
        return $this->getParameter('testClientId');
    }

    public function setTestClientId($value)
    {
        return $this->setParameter('testClientId', $value);
    }

    public function getTestClientSecret()
    {
        return $this->getParameter('testClientSecret');
    }

    public function setTestClientSecret($value)
    {
        return $this->setParameter('testClientSecret', $value);
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

    public function getTestResourceToken()
    {
        return $this->getParameter('testResourceToken');
    }

    public function setTestResourceToken($value)
    {
        return $this->setParameter('testResourceToken', $value);
    }

    /**
     * @param array $parameters
     * @return CreateDigitalAccountRequest
     */
    public function createDigitalAccount(array $parameters = array())
    {
        return $this->createRequest(
            Message\CreateDigitalAccountRequest::class,
            $parameters
        );
    }

    /**
     * @param array $parameters
     * @return FindDigitalAccountRequest
     */
    public function findDigitalAccount(array $parameters = array())
    {
        return $this->createRequest(
            Message\FindDigitalAccountRequest::class,
            $parameters
        );
    }

    /**
     * @param array $parameters
     * @return GetBanksRequest
     */
    public function getBanks(array $parameters = array())
    {
        return $this->createRequest(
            Message\GetBanksRequest::class,
            $parameters
        );
    }

    /**
     * @param array $parameters
     * @return GetCompanyTypesRequest
     */
    public function getCompanyTypes(array $parameters = array())
    {
        return $this->createRequest(
            Message\GetCompanyTypesRequest::class,
            $parameters
        );
    }

    /**
     * @param array $parameters
     * @return GetBusinessAreasRequest
     */
    public function getBusinessAreas(array $parameters = array())
    {
        return $this->createRequest(
            Message\GetBusinessAreasRequest::class,
            $parameters
        );
    }
}
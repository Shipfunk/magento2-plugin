<?php

namespace Nord\Shipfunk\Model\Api;

/**
 * @codeCoverageIgnoreStart
 */
class ShipfunkResponse extends \Magento\Framework\DataObject implements
    \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->getData(self::RESPONSE);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponse($response)
    {
        return $this->setData(self::RESPONSE, $response);
    }

}

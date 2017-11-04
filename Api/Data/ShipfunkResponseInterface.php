<?php

namespace Nord\Shipfunk\Api\Data;

/**
 * Interface ShipfunkResponseInterface
 * @api
 */
interface ShipfunkResponseInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const RESPONSE = 'response';

    /**
     * @return string
     */
    public function getResponse();

    /**
     * @param string $response
     * @return $this
     */
    public function setResponse($response);

}

<?php

namespace Nord\Shipfunk\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Index
 * @package Nord\Shipfunk\Controller\Index
 */
class Index extends Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var mixed
     */
    protected $connection;

    /**
     * Index constructor.
     *
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);

        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->connection = $resource->getConnection();
    }

    /**
     * Execute
     */
    public function execute()
    {
        $request = $this->getRequest()->getPostValue();
        $post = $request['data'];
        if (isset($post[0])) {
            $mode = $post[0];
        } else {
            $mode = 'update';
        }

        switch ($mode) {
            case 'insert':
                if (count($post) !== 4) {
                    return false;
                }
                foreach ($post[3] as &$pickup) {

                    $pickup['carriercode'] = $post[1];
                    $pickup['productcode'] = $post[2];
                    $this->insertPickup($pickup);
                }
                break;
            case 'select':
                $this->getPickupFromQuote($post);
                break;
            case 'update':
                $this->addPickupToQuote($post);
                break;
            case 'delete':
                $this->deletePickupFromQuote($post);
                break;
        }
    }

    /**
     * @param $pickup
     */
    protected function insertPickup($pickup)
    {
        $pickup_id = $pickup['pickup_id']."_".$pickup['carriercode']."_".$pickup['productcode'];
        $pickup_address = $pickup['pickup_name']."<br>".$pickup['pickup_addr']."<br>".$pickup['pickup_postal']." ".$pickup['pickup_city'];

        if (!$this->getPickup($pickup_id)) {

            $sql = "INSERT INTO nord_shipfunk_pickups (id, pickup_id, pickup) VALUES ('', '$pickup_id', '$pickup_address')";
            $this->connection->query($sql);
        }
    }

    /**
     * @param $pickup_id
     *
     * @return mixed
     */
    public function getPickup($pickup_id)
    {
        $sql = "SELECT pickup FROM nord_shipfunk_pickups WHERE pickup_id = '$pickup_id'";

        return $this->connection->fetchOne($sql);
    }

    /**
     * @param $select
     *
     * @return mixed
     */
    public function getPickupFromQuote($select)
    {
        $quote_id = $select[2];

        if (isset($select[3]) && !is_numeric($quote_id)) {
            $sql = "SELECT nsp.pickup FROM nord_shipfunk_selected_pickup nssp JOIN nord_shipfunk_pickups nsp ON (nsp.pickup_id = nssp.pickup_id) JOIN quote_id_mask qim ON (qim.quote_id = nssp.quote_id) WHERE qim.masked_id = '$quote_id'";
        } else {
            $sql = "SELECT nsp.pickup FROM nord_shipfunk_selected_pickup nssp JOIN nord_shipfunk_pickups nsp ON (nsp.pickup_id = nssp.pickup_id) WHERE nssp.quote_id = '$quote_id'";
        }

        $result = $this->connection->fetchOne($sql);


        if ($select[1] === false) {
            return $result;
        } else {
            echo json_encode($result);
        }
    }

    /**
     * @param $pickup
     */
    protected function addPickupToQuote($pickup)
    {
        $pickup = $pickup['query']['selected_option'];

        $pickup_id = $pickup['pickupid']."_".$pickup['carriercode']."_".$pickup['productcode'];
        $quote_id = $pickup['orderid'];

        $sql = "DELETE FROM nord_shipfunk_selected_pickup WHERE quote_id = '$quote_id'";
        $this->connection->query($sql);

        $sql = "INSERT INTO nord_shipfunk_selected_pickup (id, quote_id, pickup_id) VALUES ('', '$quote_id', '$pickup_id')";
        $this->connection->query($sql);
    }

    protected function deletePickupFromQuote($post)
    {
        $quote_id = $post[1];
        $sql = "DELETE FROM nord_shipfunk_selected_pickup WHERE quote_id = '$quote_id'";
        $this->connection->query($sql);
    }

}
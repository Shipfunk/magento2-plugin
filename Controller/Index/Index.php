<?php

namespace Nord\Shipfunk\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Nord\Shipfunk\Model\PickupsFactory;
use Nord\Shipfunk\Model\SelectedPickupFactory;
use Nord\Shipfunk\Model\ResourceModel\SelectedPickup\CollectionFactory;

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
  
    protected $_pickupsFactory;
  
    protected $_selectedPickupFactory;
  
    protected $_selectedPickupCollectionFactory;
  
    /**
     * Index constructor.
     *
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PickupsFactory $pickupsFactory,
        SelectedPickupFactory $selectedPickupFactory,
        CollectionFactory $selectedPickupCollectionFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_pickupsFactory = $pickupsFactory;
        $this->_selectedPickupFactory = $selectedPickupFactory;
        $this->_selectedPickupCollectionFactory = $selectedPickupCollectionFactory;

        parent::__construct($context);
    }

    /**
     * Execute
     */
    public function execute()
    {
        $request = $this->getRequest()->getPostValue();
        $post    = $request['data'];
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
        $pickup_id      = $pickup['pickup_id']."_".$pickup['carriercode']."_".$pickup['productcode'];
        $pickup_address = $pickup['pickup_name']."<br>".$pickup['pickup_addr']."<br>".$pickup['pickup_postal']." ".$pickup['pickup_city'];
        $pickup_address = preg_replace("/<script.*?\/script>/s", "", $pickup_address) ? : $pickup_address;
        if (!$this->getPickup($pickup_id)) {
            $model = $this->_pickupsFactory->create();
            $model->setData(['pickup_id' => $pickup_id, 'pickup' => $pickup_address]);
            $model->save();
        }
    }

    /**
     * @param $pickup_id
     *
     * @return mixed
     */
    public function getPickup($pickup_id)
    {
        $model = $this->_pickupsFactory->create()->load($pickup_id, 'pickup_id');
        return $model->getId();
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
            $collection = $this->_selectedPickupCollectionFactory->create()->joinPickups()->joinQuoteMask()->addFieldToFilter('masked_id', $quote_id );
        } else {
            $collection = $this->_selectedPickupCollectionFactory->create()->joinPickups()->addFieldToFilter('quote_id', $quote_id );
        }

        $result = $collection->getFirstItem()->getPickup();


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
        $quote_id  = $pickup['orderid'];

        $this->_selectedPickupCollectionFactory->create()->addFieldToFilter('quote_id', $quote_id )->walk('delete');
      
        $modelNew = $this->_selectedPickupFactory->create();
        $modelNew->setData(['pickup_id' => $pickup_id, 'quote_id' => $quote_id]);
        $modelNew->save();
    }

    protected function deletePickupFromQuote($post)
    {
        $quote_id = $post[1];
        $this->_selectedPickupCollectionFactory->create()->addFieldToFilter('quote_id', $quote_id )->walk('delete');
    }

}

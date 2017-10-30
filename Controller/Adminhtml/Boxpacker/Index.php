<?php

namespace Nord\Shipfunk\Controller\Adminhtml\Boxpacker;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipment\RequestFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Nord\Shipfunk\Helper\UnitConverter;
use Nord\Shipfunk\Model\Api\Shipfunk\CreateNewPackageCards;
use Nord\Shipfunk\Helper\ParcelHelper;
use Nord\Shipfunk\Model\BoxPacker\Box;
use Nord\Shipfunk\Model\BoxPacker\Item;
use Nord\Shipfunk\Model\BoxPacker\ShipfunkPacker;
use Psr\Log\LoggerInterface;

/**
 * Class Index
 *
 * @package Nord\Shipfunk\Controller\Adminhtml\Boxpacker
 */
class Index extends Action
{
    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ParcelHelper
     */
    protected $parcelHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RequestFactory
     */
    protected $shipmentRequestFactory;

    /**
     * @var CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var Shipment
     */
    protected $orderShipment;

    /**
     * @var UnitConverter
     */
    protected $unitConverter;

    /**
     * @var ShipfunkPacker
     */
    protected $packer;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var mixed
     */
    protected $product;

    /**
     * @var CreateNewPackageCards
     */
    protected $CreateNewPackageCards;

    /**
     * @var Order
     */
    protected $orderModel;

    /**
     * @var LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var CollectionFactory
     */
    protected $trackCollectionFactory;

    /**
     * @var ShipfunkDataHelper
     */
    protected $helper;

    /**
     * Index constructor.
     *
     * @param Context                $context
     * @param PageFactory            $resultPageFactory
     * @param UnitConverter          $unitConverter
     * @param ShipfunkPacker         $packer
     * @param ParcelHelper           $parcelHelper
     * @param Shipment               $orderShipment
     * @param CarrierFactory         $carrierFactory
     * @param RequestFactory         $shipmentRequestFactory
     * @param ShipmentLoader         $shipmentLoader
     * @param CreateNewPackageCards  $CreateNewPackageCards
     * @param Order                  $orderModel
     * @param LabelGenerator         $labelGenerator
     * @param CollectionFactory      $trackCollectionFactory
     * @param ShipfunkDataHelper     $shipfunkDataHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        UnitConverter $unitConverter,
        ShipfunkPacker $packer,
        ParcelHelper $parcelHelper,
        Shipment $orderShipment,
        CarrierFactory $carrierFactory,
        RequestFactory $shipmentRequestFactory,
        ShipmentLoader $shipmentLoader,
        CreateNewPackageCards $CreateNewPackageCards,
        Order $orderModel,
        LabelGenerator $labelGenerator,
        CollectionFactory $trackCollectionFactory,
        ShipfunkDataHelper $shipfunkDataHelper
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->carrierFactory = $carrierFactory;
        $this->shipmentRequestFactory = $shipmentRequestFactory;
        $this->unitConverter = $unitConverter;
        $this->orderShipment = $orderShipment;
        $this->params = $context->getRequest()->getParams();
        $this->packer = $packer;
        $this->parcelHelper = $parcelHelper;
        $this->shipmentLoader = $shipmentLoader;
        $this->CreateNewPackageCards = $CreateNewPackageCards;
        $this->orderModel = $orderModel;
        $this->labelGenerator = $labelGenerator;
        $this->trackCollectionFactory = $trackCollectionFactory;
        $this->helper = $shipfunkDataHelper;
    }

    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        if (!$request->get('id')) {
            return;
        }

        $this->getBoxDimensions();

        $orderId = $request->get('id');
        /** @noinspection PhpDeprecationInspection */
        $order = $this->orderModel->load($orderId);
        $orderItems = $order->getAllItems();
        $products = [];

        if ($orderItems) {
            /** @var mixed $orderItem */
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getParentItem() || $orderItem->getHasChildren() && $orderItem->isShipSeparately(
                    ) || $orderItem->getProduct()->isVirtual()
                ) {
                    continue;
                }

                $this->product = $orderItem->getProduct();
                $qty = (int)$orderItem->getQtyOrdered();

                $products[] = $this->product;

                $this->parcelHelper->setProduct($this->product);

                $item = new Item(
                    $this->product->getData('name').'/'.$this->product->getData('sku'),
                    $this->parcelHelper->getProductWidth(),
                    $this->parcelHelper->getProductLength(),
                    $this->parcelHelper->getProductDepth(),
                    $this->parcelHelper->getProductWeight(),
                    true,
                    $this->product->getData('price'),
                    $orderItem->getId(),
                    $this->product->getId(),
                    1
                );

                $this->packer->addItem($item, $qty);
            }
        }

        $packedBoxes = $this->packer->pack();
        $packages = $this->parcelHelper->convertBoxPackerToMagento($packedBoxes, false);

        try {
            $this->shipmentLoader->setOrderId($orderId);
            $this->shipmentLoader->setShipment($packages);
            $shipment = $this->shipmentLoader->load();

            if (!$shipment) {
                $this->_forward('noroute');

                return;
            }

            $shipment->register();
            $shipment->setPackages($packages);
            $this->_request->setParams(['packages' => $packages]);
            $this->_saveShipment($shipment);

            foreach ($orderItems as $orderItem) {
                $orderItem->setShipment($shipment);
                $orderItem->setQtyShipped($orderItem->getQtyOrdered());
                $orderItem->setParentId($shipment->getId());
                $orderItem->save();
            }

            $this->createLabel($shipment);

            $shipmentCreatedMessage = __('The shipment has been created.');
            /** @noinspection PhpDeprecationInspection */
            $this->messageManager->addSuccess(
                $shipmentCreatedMessage
            );
            $this->_objectManager->get(Session::class)->getCommentText(true);
        } catch (LocalizedException $e) {
            $this->_saveShipment($shipment, false);
            /** @noinspection PhpDeprecationInspection */
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/new', ['order_id' => $orderId]);
        } catch (\Exception $e) {
            $this->_saveShipment($shipment, false);
            $this->_objectManager->get(LoggerInterface::class)->critical($e);

            /** @noinspection PhpDeprecationInspection */
            $this->messageManager->addError(__('Cannot save shipment.'));
            $this->_redirect('*/*/new', ['order_id' => $orderId]);
        }

        $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * @return void
     */
    public function getBoxDimensions()
    {
        $parcels = $this->helper->getConfigData('parcels');        
        foreach ($parcels as $item) {
            $this->packer->addBox(
                new Box(
                    $item['parcel_name'],
                    $item['outer_width'],
                    $item['outer_length'],
                    $item['outer_depth'],
                    $item['empty_weight'],
                    $item['inner_width'],
                    $item['inner_length'],
                    $item['inner_depth'],
                    $item['max_weight']
                )
            );
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param bool                                $process
     *
     * @return $this
     */
    protected function _saveShipment($shipment, $process = true)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $shipment->getOrder()->setIsInProcess($process);

        $transaction = $this->_objectManager->create(
            Transaction::class
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }

    /**
     * @param $shipment
     */
    protected function createLabel($shipment)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $shipment->unsetData('tracks');
        $this->labelGenerator->create($shipment, $this->_request);
        /** @noinspection PhpUndefinedMethodInspection */
        $shipment->save();
    }
}

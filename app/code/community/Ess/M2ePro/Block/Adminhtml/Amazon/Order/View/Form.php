<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_View_Form extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    public $shippingAddress = array();

    public $realMagentoOrderId = NULL;

    /** @var $order Ess_M2ePro_Model_Order */
    public $order = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderViewForm');
        $this->setTemplate('M2ePro/amazon/order.phtml');
        // ---------------------------------------

        $this->order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
    }

    protected function _beforeToHtml()
    {
        // Magento order data
        // ---------------------------------------
        $this->realMagentoOrderId = NULL;

        $magentoOrder = $this->order->getMagentoOrder();
        if (!is_null($magentoOrder)) {
            $this->realMagentoOrderId = $magentoOrder->getRealOrderId();
        }
        // ---------------------------------------

        // ---------------------------------------
        if (!is_null($magentoOrder) && $magentoOrder->hasShipments() && !$this->order->getChildObject()->isPrime()) {
            $url = $this->getUrl('*/adminhtml_order/resubmitShippingInfo', array('id' => $this->order->getId()));
            $data = array(
                'class'   => '',
                'label'   => Mage::helper('M2ePro')->__('Resend Shipping Information'),
                'onclick' => 'setLocation(\''.$url.'\');',
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('resubmit_shipping_info', $buttonBlock);
        }
        // ---------------------------------------

        // Shipping data
        // ---------------------------------------
        /** @var $shippingAddress Ess_M2ePro_Model_Amazon_Order_ShippingAddress */
        $shippingAddress = $this->order->getShippingAddress();

        $this->shippingAddress = $shippingAddress->getData();
        $this->shippingAddress['country_name'] = $shippingAddress->getCountryName();
        // ---------------------------------------

        // Merchant Fulfillment
        // ---------------------------------------
        if (!$this->order->getChildObject()->isCanceled()
            && !$this->order->getChildObject()->isPending()
            && !$this->order->getChildObject()->isFulfilledByAmazon()
            && $this->order->getMarketplace()->getChildObject()->isMerchantFulfillmentAvailable()) {
            $orderId = $this->order->getId();
            $data = array(
                'class'   => '',
                'label'   => Mage::helper('M2ePro')->__('Use Amazon\'s Shipping Services'),
                'onclick' => "OrderMerchantFulfillmentHandlerObj.getPopupAction({$orderId});",
                'style'   => 'margin-top: 3px; margin-left: 6px;'
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('use_amazons_shipping_services', $buttonBlock);
        }
        // ---------------------------------------
        $buttonAddNoteBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Add Note'),
                'onclick' => "OrderNoteHandlerObj.openAddNotePopup({$this->order->getId()})",
                'class'   => 'order_note_btn',
            ));

        $this->setChild('item', $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_order_view_item'));
        $this->setChild('item_edit', $this->getLayout()->createBlock('M2ePro/adminhtml_order_item_edit'));
        $this->setChild('log', $this->getLayout()->createBlock('M2ePro/adminhtml_order_view_log_grid'));
        $this->setChild('order_note_grid', $this->getLayout()->createBlock('M2ePro/adminhtml_order_note_grid'));
        $this->setChild('add_note_button', $buttonAddNoteBlock);

        return parent::_beforeToHtml();
    }

    private function getStore()
    {
        if (is_null($this->order->getData('store_id'))) {
            return null;
        }

        try {
            $store = Mage::app()->getStore($this->order->getData('store_id'));
        } catch (Exception $e) {
            return null;
        }

        return $store;
    }

    public function isCurrencyAllowed()
    {
        $store = $this->getStore();

        if (is_null($store)) {
            return true;
        }

        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');

        return $currencyHelper->isAllowed($this->order->getChildObject()->getCurrency(), $store);
    }

    public function hasCurrencyConversionRate()
    {
        $store = $this->getStore();

        if (is_null($store)) {
            return true;
        }

        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');

        return $currencyHelper->getConvertRateFromBase($this->order->getChildObject()->getCurrency(), $store) != 0;
    }

    //########################################
}
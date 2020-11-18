<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Account as Account;

class Ess_M2ePro_Model_Ebay_Account_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{

    protected function prepareData()
    {
        $data = array();

        // tab: general
        // ---------------------------------------
        $keys = array(
            'title',
            'mode',
            'user_id',
            'info',
            'server_hash',
            'token_session',
            'token_expired_date',
            'sell_api_token_session',
            'sell_api_token_expired_date'
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        // tab: 3rd party
        // ---------------------------------------
        $keys = array(
            'other_listings_synchronization',
            'other_listings_mapping_mode'
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        $marketplacesIds = Mage::getModel('M2ePro/Marketplace')->getCollection()
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->getColumnValues('id');

        $marketplacesData = array();
        if ($this->getModel()->getId()) {
            $marketplacesData = $this->getModel()->getChildObject()->getSettings('marketplaces_data');
        }

        foreach ($marketplacesIds as $marketplaceId) {
            $marketplacesData[$marketplaceId]['related_store_id'] =
                isset($this->_rawData['related_store_id_' . $marketplaceId])
                ? (int)$this->_rawData['related_store_id_' . $marketplaceId]
                : Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $data['marketplaces_data'] = Mage::helper('M2ePro')->jsonEncode($marketplacesData);

        // Mapping
        // ---------------------------------------
        $tempData = array();
        $keys = array(
            'mapping_sku_mode',
            'mapping_sku_priority',
            'mapping_sku_attribute',

            'mapping_title_mode',
            'mapping_title_priority',
            'mapping_title_attribute',

            'mapping_item_id_mode',
            'mapping_item_id_priority',
            'mapping_item_id_attribute'
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $tempData[$key] = $this->_rawData[$key];
            }
        }

        $mappingSettings = array();
        if ($this->getModel()->getId()) {
            $mappingSettings = $this->getModel()->getChildObject()->getSettings('other_listings_mapping_settings');
        }

        if (isset($tempData['mapping_sku_mode'])) {
            $mappingSettings['sku']['mode'] = (int)$tempData['mapping_sku_mode'];

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT ||
                $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE ||
                $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID
            ) {
                $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];
            }

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        if (isset($tempData['mapping_title_mode'])) {
            $mappingSettings['title']['mode'] = (int)$tempData['mapping_title_mode'];

            if ($tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT ||
                $tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE
            ) {
                $mappingSettings['title']['priority'] = (int)$tempData['mapping_title_priority'];
            }

            if ($tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['title']['attribute'] = (string)$tempData['mapping_title_attribute'];
            }
        }

        if (isset($tempData['mapping_item_id_mode'])) {
            $mappingSettings['item_id']['mode'] = (int)$tempData['mapping_item_id_mode'];

            if ($tempData['mapping_item_id_mode'] == Account::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['item_id']['priority'] = (int)$tempData['mapping_item_id_priority'];
                $mappingSettings['item_id']['attribute'] = (string)$tempData['mapping_item_id_attribute'];
            }
        }

        $data['other_listings_mapping_settings'] = Mage::helper('M2ePro')->jsonEncode($mappingSettings);

        // tab: orders
        // ---------------------------------------
        $data['magento_orders_settings'] = array();
        if ($this->getModel()->getId()) {
            $data['magento_orders_settings'] = $this->getModel()->getChildObject()->getSettings(
                'magento_orders_settings'
            );
        }

        // m2e orders settings
        // ---------------------------------------
        $tempKey = 'listing';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'store_mode',
            'store_id'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // 3rd party orders settings
        // ---------------------------------------
        $tempKey = 'listing_other';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'product_mode',
            'product_tax_class_id',
            'store_id'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // order number settings
        // ---------------------------------------
        $tempKey = 'number';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        if (!empty($tempSettings['source'])) {
            $data['magento_orders_settings'][$tempKey]['source'] = $tempSettings['source'];
        }

        $prefixKeys = array(
            'prefix',
            'use_marketplace_prefix'
        );
        $tempSettings = !empty($tempSettings['prefix']) ? $tempSettings['prefix'] : array();
        foreach ($prefixKeys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey]['prefix'][$key] = $tempSettings[$key];
            }
        }

        // creation settings
        // ---------------------------------------
        $tempKey = 'creation';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // tax settings
        // ---------------------------------------
        $tempKey = 'tax';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // customer settings
        // ---------------------------------------
        $tempKey = 'customer';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'id',
            'website_id',
            'group_id',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        $notificationsKeys = array(
            'order_created',
            'invoice_created'
        );
        $tempSettings = !empty($tempSettings['notifications']) ? $tempSettings['notifications'] : array();
        foreach ($notificationsKeys as $key) {
            $data['magento_orders_settings'][$tempKey]['notifications'][$key] = in_array($key, $tempSettings);
        }

        // status mapping settings
        // ---------------------------------------
        $tempKey = 'status_mapping';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'new',
            'paid',
            'shipped'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // qty reservation
        // ---------------------------------------
        $tempKey = 'qty_reservation';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'days',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // In Store Pickup statuses
        // ---------------------------------------
        $tempKey = 'in_store_pickup_statuses';
        $tempSettings = !empty($this->_rawData['magento_orders_settings'][$tempKey])
            ? $this->_rawData['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'ready_for_pickup',
            'picked_up',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // invoice/shipment settings
        // ---------------------------------------
        $temp = Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM;
        if (isset($this->_rawData['magento_orders_settings']['status_mapping']['mode']) &&
            $this->_rawData['magento_orders_settings']['status_mapping']['mode'] == $temp
        ) {
            $data['magento_orders_settings']['invoice_mode']  = 1;
            $data['magento_orders_settings']['shipment_mode'] = 1;

            if (!isset($this->_rawData['magento_orders_settings']['invoice_mode'])) {
                $data['magento_orders_settings']['invoice_mode'] = 0;
            }

            if (!isset($this->_rawData['magento_orders_settings']['shipment_mode'])) {
                $data['magento_orders_settings']['shipment_mode'] = 0;
            }
        }

        $data['magento_orders_settings'] = Mage::helper('M2ePro')->jsonEncode($data['magento_orders_settings']);

        // tab: feedbacks
        // ---------------------------------------
        $keys = array(
            'feedbacks_receive',
            'feedbacks_auto_response',
            'feedbacks_auto_response_only_positive'
        );
        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        return $data;
    }

    public function getDefaultData()
    {
        return array(
            'title' => '',
            'user_id' => '',
            'mode' => Account::MODE_PRODUCTION,
            'server_hash' => '',
            'token_session' => '',
            'token_expired_date' => '',
            'sell_api_token_session' => '',
            'sell_api_token_expired_date' => '',

            'other_listings_synchronization' => 1,
            'other_listings_mapping_mode' => 0,
            'other_listings_mapping_settings' => array(),

            'magento_orders_settings' => array(
                'listing' => array(
                    'mode' => 1,
                    'store_mode' => Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT,
                    'store_id' => null
                ),
                'listing_other' => array(
                    'mode' => 1,
                    'product_mode' => Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT,
                    'product_tax_class_id' => Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE,
                    'store_id' => null,
                ),
                'number' => array(
                    'source' => Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO,
                    'prefix' => array(
                        'prefix' => '',
                        'use_marketplace_prefix' => 0,
                    ),
                ),
                'customer' => array(
                    'mode' => Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST,
                    'id' => null,
                    'website_id' => null,
                    'group_id' => null,
                    'notifications' => array(
                        'invoice_created' => false,
                        'order_created' => false
                    )
                ),
                'creation' => array(
                    'mode' => Account::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID,
                ),
                'tax' => array(
                    'mode' => Account::MAGENTO_ORDERS_TAX_MODE_MIXED
                ),
                'in_store_pickup_statuses' => array(
                    'mode' => 0,
                    'ready_for_pickup' => '',
                    'picked_up' => '',
                ),
                'status_mapping' => array(
                    'mode' => Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT,
                    'new' => Account::MAGENTO_ORDERS_STATUS_MAPPING_NEW,
                    'paid' => Account::MAGENTO_ORDERS_STATUS_MAPPING_PAID,
                    'shipped' => Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED
                ),
                'qty_reservation' => array(
                    'days' => 1
                ),
                'invoice_mode'  => 1,
                'shipment_mode' => 1
            ),

            'ebay_store_title' => '',
            'ebay_store_url' => '',
            'ebay_store_subscription_level' => '',
            'ebay_store_description' => '',

            'feedbacks_receive' => 0,
            'feedbacks_auto_response' => Account::FEEDBACKS_AUTO_RESPONSE_NONE,
            'feedbacks_auto_response_only_positive' => 0
        );
    }

    //########################################
}

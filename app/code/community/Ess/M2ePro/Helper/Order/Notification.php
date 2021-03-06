<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Order_Notification extends Mage_Core_Helper_Abstract
{
    const NOTIFICATIONS_PATH = '/logs/notification/order/';

    const NOTIFICATIONS_DISABLED        = 0;
    const NOTIFICATIONS_EXTENSION_PAGES = 1;
    const NOTIFICATIONS_MAGENTO_PAGES   = 2;

    /**
     * @var Ess_M2ePro_Model_Resource_Order_Log_Collection
     */
    protected $_collectionOrderLogs;

    //########################################

    public function buildMessage()
    {
        Mage::getSingleton('core/layout')->getBlock('head')->addJs(
            'M2ePro/Order/LogNotification.js'
        );

        $orderNotCreatedDate= explode(' ', $this->_collectionOrderLogs->getFirstItem()->getData('create_date'));
        $message = <<<HTML
<script type="text/javascript">
    if (typeof LogNotificationObj == 'undefined') {
       LogNotificationObj = new LogNotification();
    }
</script>

  Since {$orderNotCreatedDate[0]}, some Magento orders have not been created:
 {$this->_collectionOrderLogs->getSize()}, check your 
HTML;
        $hasAmazonLog = false;
        $hasEbayLog = false;
        $hasWalmartLog = false;

        foreach ($this->_collectionOrderLogs->getItems() as $item) {
            if ($item->getData('component_mode') === Ess_M2ePro_Helper_View_Amazon::NICK) {
                $hasAmazonLog = true;
            } elseif ($item->getData('component_mode') === Ess_M2ePro_Helper_View_Ebay::NICK) {
                $hasEbayLog = true;
            } elseif ($item->getData('component_mode') === Ess_M2ePro_Helper_View_Walmart::NICK) {
                $hasWalmartLog = true;
            }
        }

        $filter = base64_encode(
            'order_create_date[from]=' . $this->getFormatDate($orderNotCreatedDate[0]) . '&' .
            'description=Magento Order was not created&' .
            'initiator=' . Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION
        );

        if ($hasEbayLog) {
            $url = Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_ebay_log/order', array('filter' => $filter));
            $message .= '<a href="' . $url . '" target="_blank">eBay orders logs</a>';
        }

        if ($hasAmazonLog) {
            $url = Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_amazon_log/order', array('filter' => $filter));
            $message .= ' / <a href="' . $url . '" target="_blank"> Amazon orders logs</a>';
        }

        if ($hasWalmartLog) {
            $url = Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_walmart_log/order', array('filter' => $filter));
            $message .= ' / <a href="' . $url . '" target="_blank">Walmart orders logs</a>';
        }

        $url = Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_order/skipLogNotificationToCurrentDate');

        $message .= <<<HTML
. <a href="javascript:void(0);" onclick="LogNotificationObj.skipLogToCurrentDate('{$url}')">Skip this message</a>.
HTML;

        return $message;
    }

    protected function getFormatDate($orderNotCreatedDate)
    {
        list($yyyy, $m, $d)  = explode('-', $orderNotCreatedDate);

        $format = Zend_Locale_Data::getContent(
            Mage::app()->getLocale()->getLocaleCode(),
            'date',
            array('gregorian', 'short')
        );

        $datePosition = array();
        foreach (array('m', 'd', 'y') as $value) {
            $datePosition[stripos($format, $value)] = $value;
        }

        ksort($datePosition);

        $newDateFormat = '';
        foreach ($datePosition as $value) {
            if ($value == 'd') {
                $newDateFormat .= $d . '/';
            } elseif ($value == 'm') {
                $newDateFormat .= $m . '/';
            } else {
                $newDateFormat .= $yyyy . '/';
            }
        };

        return rtrim($newDateFormat, '/');
    }

    //########################################

    public function showNotification()
    {
        $this->_collectionOrderLogs = Mage::getModel('M2ePro/Order_Log')->getCollection()
            ->getLogsByDescription(
                'Magento Order was not created',
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                $this->getNotificationDate()
            );

        return ($this->_collectionOrderLogs->getSize()) ? true : false;
    }

    //########################################

    public function getNotificationMode()
    {
        /** @var Ess_M2ePro_Model_Config_Manager $config */
        $config = Mage::helper('M2ePro/Module')->getConfig();

        return (int)$config->getGroupValue(self::NOTIFICATIONS_PATH, 'mode');
    }

    public function setNotificationMode($value)
    {
        /** @var Ess_M2ePro_Model_Config_Manager $config */
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $config->setGroupValue(self::NOTIFICATIONS_PATH, 'mode', (int)$value);
    }

    public function getNotificationDate()
    {
        /** @var Ess_M2ePro_Model_Config_Manager $config */
        $config = Mage::helper('M2ePro/Module')->getConfig();

        return $config->getGroupValue(self::NOTIFICATIONS_PATH, 'last_date');
    }

    public function setNotificationDate($value)
    {
        /** @var Ess_M2ePro_Model_Config_Manager $config */
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $config->setGroupValue(self::NOTIFICATIONS_PATH, 'last_date', $value);
    }

    //----------------------------------------

    public function isNotificationDisabled()
    {
        return $this->getNotificationMode() == self::NOTIFICATIONS_DISABLED;
    }

    public function isNotificationExtensionPages()
    {
        return $this->getNotificationMode() == self::NOTIFICATIONS_EXTENSION_PAGES;
    }

    public function isNotificationMagentoPages()
    {
        return $this->getNotificationMode() == self::NOTIFICATIONS_MAGENTO_PAGES;
    }

    //########################################
}
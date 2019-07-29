<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Template_SellingFormat as WalmartSellingFormat;

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $sellingFormatTemplateModel Ess_M2ePro_Model_Template_Sellingformat
     */
    private $sellingFormatTemplateModel = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     * @return $this
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
        $this->sellingFormatTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->sellingFormatTemplateModel;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getWalmartSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //########################################

    public function getLagTime()
    {
        $result = 0;
        $src = $this->getWalmartSellingFormatTemplate()->getLagTimeSource();

        if ($src['mode'] == WalmartSellingFormat::LAG_TIME_MODE_RECOMMENDED) {
            $result = $src['value'];
        }

        if ($src['mode'] == WalmartSellingFormat::LAG_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $result = (int)$result;
        $result < 0 && $result = 0;

        return $result;
    }

    public function getItemWeight()
    {
        $result = 0;
        $src = $this->getWalmartSellingFormatTemplate()->getItemWeightSource();

        if ($src['mode'] == WalmartSellingFormat::WEIGHT_MODE_CUSTOM_VALUE) {
            $result = $src['custom_value'];
        }

        if ($src['mode'] == WalmartSellingFormat::WEIGHT_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);
        }

        $result < 0 && $result = 0;

        return $result;
    }

    public function getProductTaxCode()
    {
        $result = '';
        $src = $this->getWalmartSellingFormatTemplate()->getProductTaxCodeSource();

        if ($src['mode'] == WalmartSellingFormat::PRODUCT_TAX_CODE_MODE_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == WalmartSellingFormat::PRODUCT_TAX_CODE_MODE_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    public function getMustShipAlone()
    {
        $result = NULL;
        $src = $this->getWalmartSellingFormatTemplate()->getMustShipAloneSource();

        if ($src['mode'] == WalmartSellingFormat::MUST_SHIP_ALONE_MODE_YES) {
            $result = true;
        }

        if ($src['mode'] == WalmartSellingFormat::MUST_SHIP_ALONE_MODE_NO) {
            $result = false;
        }

        if ($src['mode'] == WalmartSellingFormat::MUST_SHIP_ALONE_MODE_CUSTOM_ATTRIBUTE) {
            $attributeValue = $this->getMagentoProduct()->getAttributeValue($src['attribute']);

            if ($attributeValue == Mage::helper('M2ePro')->__('Yes')) {
                $result = true;
            }

            if ($attributeValue == Mage::helper('M2ePro')->__('No')) {
                $result = false;
            }
        }

        return $result;
    }

    public function getShipsInOriginalPackaging()
    {
        $result = NULL;
        $src = $this->getWalmartSellingFormatTemplate()->getShipsInOriginalPackagingModeSource();

        if ($src['mode'] == WalmartSellingFormat::SHIPS_IN_ORIGINAL_PACKAGING_MODE_YES) {
            $result = true;
        }

        if ($src['mode'] == WalmartSellingFormat::SHIPS_IN_ORIGINAL_PACKAGING_MODE_NO) {
            $result = false;
        }

        if ($src['mode'] == WalmartSellingFormat::SHIPS_IN_ORIGINAL_PACKAGING_MODE_CUSTOM_ATTRIBUTE) {
            $attributeValue = $this->getMagentoProduct()->getAttributeValue($src['attribute']);

            if ($attributeValue == Mage::helper('M2ePro')->__('Yes')) {
                $result = true;
            }

            if ($attributeValue == Mage::helper('M2ePro')->__('No')) {
                $result = false;
            }
        }

        return $result;
    }

    public function getStartDate()
    {
        $result = NULL;
        $src = $this->getWalmartSellingFormatTemplate()->getSaleTimeStartDateSource();

        if ($src['mode'] == WalmartSellingFormat::DATE_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == WalmartSellingFormat::DATE_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    public function getEndDate()
    {
        $result = NULL;
        $src = $this->getWalmartSellingFormatTemplate()->getSaleTimeEndDateSource();

        if ($src['mode'] == WalmartSellingFormat::DATE_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == WalmartSellingFormat::DATE_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        if ($this->getWalmartSellingFormatTemplate()->isAttributesModeNone()) {
            return array();
        }

        $result = array();
        $src = $this->getWalmartSellingFormatTemplate()->getAttributesSource();

        foreach ($src['template'] as $value) {
            if (empty($value)) {
                continue;
            }

            $result[$value['name']] = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                $value['value'], $this->getMagentoProduct()
            );
        }

        return $result;
    }

    //########################################
}
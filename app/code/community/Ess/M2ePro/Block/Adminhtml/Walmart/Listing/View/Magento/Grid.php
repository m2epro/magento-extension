<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View_Magento_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('walmartListingViewGrid' . $this->_listing->getId());

        $this->hideMassactionColumn              = true;
        $this->_hideMassactionDropDown           = true;
        $this->_showAdvancedFilterProductsOption = false;
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_view_modeSwitcher'
        );
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );
        $collection->getSelect()->group('e.entity_id');
        $collection->setListing($this->_listing->getId());
        $collection->setStoreId($this->_listing->getStoreId());

        $collection->addAttributeToSelect('name')
                   ->joinStockItem(array('qty' => 'qty', 'is_in_stock' => 'is_in_stock'));

        // ---------------------------------------

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'walmart_status' => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$this->_listing->getId()
            )
        );
        $collection->joinTable(
            array('alp' => 'M2ePro/Walmart_Listing_Product'),
            'listing_product_id=id',
            array(
                'walmart_sku'             => 'sku',
                'online_qty'              => 'online_qty',
                'online_price'            => 'online_price',
                'is_online_price_invalid' => 'is_online_price_invalid',
            ),
            null,
            'left'
        );

        $store = Mage::app()->getStore($this->_listing->getStoreId());

        if ($store->getId()) {
            $collection->joinAttribute(
                'name', 'catalog_product/name', 'entity_id', null, 'left', $store->getId()
            );
            $collection->joinAttribute(
                'magento_price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId()
            );
            $collection->joinAttribute(
                'status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId()
            );
            $collection->joinAttribute(
                'visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId()
            );
            $collection->joinAttribute(
                'thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }

        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        parent::_prepareCollection();

        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'       => Mage::helper('M2ePro')->__('ID'),
                'align'        => 'right',
                'width'        => '100px',
                'type'         => 'number',
                'index'        => 'entity_id',
                'filter_index' => 'entity_id',
                'store_id'     => $this->_listing->getStoreId(),
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name', array(
                'header'         => Mage::helper('M2ePro')->__('Title'),
                'align'          => 'left',
                'type'           => 'text',
                'index'          => 'name',
                'filter_index'   => 'name',
                'frame_callback' => array($this, 'callbackColumnProductTitle')
            )
        );

        $this->addColumn(
            'type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'type_id',
            'filter_index' => 'type_id',
            'options' => Mage::helper('M2ePro/Magento_Product')->getTypesOptionArray()
            )
        );

        $this->addColumn(
            'is_in_stock', array(
                'header'    => Mage::helper('M2ePro')->__('Stock Availability'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'is_in_stock',
                'filter_index' => 'is_in_stock',
                'options' => array(
                    '1' => Mage::helper('M2ePro')->__('In Stock'),
                    '0' => Mage::helper('M2ePro')->__('Out of Stock')
                ),
                'frame_callback' => array($this, 'callbackColumnIsInStock')
            )
        );

        $this->addColumn(
            'sku', array(
                'header'       => Mage::helper('M2ePro')->__('SKU'),
                'align'        => 'left',
                'width'        => '90px',
                'type'         => 'text',
                'index'        => 'sku',
                'filter_index' => 'sku'
            )
        );

        $store = Mage::app()->getStore($this->_listing->getStoreId());

        $priceAttributeAlias = 'price';
        if ($store->getId()) {
            $priceAttributeAlias = 'magento_price';
        }

        $this->addColumn(
            $priceAttributeAlias, array(
                'header'         => Mage::helper('M2ePro')->__('Price'),
                'align'          => 'right',
                'width'          => '100px',
                'type'           => 'price',
                'currency_code'  => $store->getBaseCurrency()->getCode(),
                'index'          => $priceAttributeAlias,
                'filter_index'   => $priceAttributeAlias,
                'frame_callback' => array($this, 'callbackColumnPrice')
            )
        );

        $this->addColumn(
            'qty', array(
                'header'         => Mage::helper('M2ePro')->__('QTY'),
                'align'          => 'right',
                'width'          => '100px',
                'type'           => 'number',
                'index'          => 'qty',
                'filter_index'   => 'qty',
                'frame_callback' => array($this, 'callbackColumnQty')
            )
        );

        $this->addColumn(
            'visibility', array(
                'header'    => Mage::helper('M2ePro')->__('Visibility'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'visibility',
                'filter_index' => 'visibility',
                'options'   => Mage::getModel('catalog/product_visibility')->getOptionArray()
            )
        );

        $this->addColumn(
            'status', array(
                'header'         => Mage::helper('M2ePro')->__('Status'),
                'align'          => 'left',
                'width'          => '90px',
                'type'           => 'options',
                'sortable'       => false,
                'index'          => 'status',
                'filter_index'   => 'status',
                'options'        => Mage::getSingleton('catalog/product_status')->getOptionArray(),
                'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'websites', array(
                    'header'       => Mage::helper('M2ePro')->__('Websites'),
                    'align'        => 'left',
                    'width'        => '90px',
                    'type'         => 'options',
                    'sortable'     => false,
                    'index'        => 'websites',
                    'filter_index' => 'websites',
                    'options'      => Mage::getModel('core/website')->getCollection()->toOptionHash()
                )
            );
        }

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $rowVal = $row->getData();

        if ($column->getId() == 'magento_price' &&
            (!isset($rowVal['magento_price']) || (float)$rowVal['magento_price'] <= 0)
        ) {
            $value = '<span style="color: red;">0</span>';
        }

        if ($column->getId() == 'price' &&
            (!isset($rowVal['price']) || (float)$rowVal['price'] <= 0)
        ) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_walmart_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }

        return parent::_addColumnFilterToCollection($column);
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof ListingGridObj != 'undefined') {
        ListingGridObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            ListingGridObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    //########################################

    protected function isShowRuleBlock()
    {
        /** @var $ruleModel Ess_M2ePro_Model_Magento_Product_Rule */
        $ruleModel = Mage::helper('M2ePro/Data_Global')->getValue('rule_model');
        if ($ruleModel->isEmpty()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    //########################################
}

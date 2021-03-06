<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Support_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('supportGeneralForm');
        $this->setTemplate('M2ePro/support.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $cronInfoBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_controlPanel_inspection_cron',
            '',
            array('is_support_mode' => true)
        );
        $this->setChild('cron_info', $cronInfoBlock);

        $versionInfoBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_controlPanel_inspection_versionInfo',
            '',
            array('is_support_mode' => true)
        );
        $this->setChild('version_info', $versionInfoBlock);

        $systemRequirementsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_controlPanel_inspection_requirements',
            '',
            array('is_support_mode' => true)
        );
        $this->setChild('system_requirements', $systemRequirementsBlock);

        return parent::_beforeToHtml();
    }

    //########################################
}

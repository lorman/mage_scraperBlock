<?php

/**
 * RawData admin edit form
 *
 * @category    HubCo
 * @package     HubCo_Manual
 * @author      Ultimate Module Creator
 */
class HubCo_ScraperBlock_Block_Adminhtml_Block_Edit
extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function __construct()
    {

        parent::__construct();
        $this->_blockGroup = 'hubco_scraperblock';
        $this->_controller = 'adminhtml_block';
         $this->_updateButton(
             'save',
             'label',
             Mage::helper('hubco_scraperblock')->__('Save Block')
         );
        $this->_updateButton(
             'delete',
             'label',
             Mage::helper('hubco_scraperblock')->__('Delete Block')
         );
         $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('hubco_scraperblock')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getHeaderText()
    {
              if (Mage::registry('current_block') && Mage::registry('current_block')->getId()) {
            return Mage::helper('hubco_scraperblock')->__(
                "Edit Block '%s'",
                $this->escapeHtml(Mage::registry('current_block')->getBlockId())
            );
        } else {
            return Mage::helper('hubco_scraperblock')->__('Add Block');
        }
    }
}

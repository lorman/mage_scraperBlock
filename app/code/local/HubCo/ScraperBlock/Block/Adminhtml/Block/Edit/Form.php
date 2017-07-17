<?php

/**
 * Rawdata edit form
 *
 * @category    HubCo
 * @package     HubCo_Manual
 * @author      Ultimate Module Creator
 */
class HubCo_ScraperBlock_Block_Adminhtml_Block_Edit_Form
extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare form
     *
     * @access protected
     * @return HubCo_Manual_Block_Adminhtml_Rawdata_Edit_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {

      $form = new Varien_Data_Form(array(
          'id' => 'edit_form',
          'action' => $this->getUrl(
              'hubco_scraperblock_admin/block/edit',
              array(
                  '_current' => true,
                  'continue' => 0,
              )
          ),
          'method' => 'post',
          'enctype' => 'multipart/form-data'
      ));

      $form->setUseContainer(true);  // very importent line :)

      $form->setHtmlIdPrefix('block_');
      $form->setFieldNameSuffix('block');
      $this->setForm($form);
      $fieldset = $form->addFieldset(
          'block_form',
          array('legend' => Mage::helper('hubco_scraperblock')->__('Block'))
      );

      $fieldset->addField(
          'ipAddr',
          'text',
          array(
              'label' => Mage::helper('hubco_scraperblock')->__('IP Address'),
              'name'  => 'ipAddr',

         )
      );

      $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
        Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
      );

      $fieldset->addField('unblockTime', 'date', array(
          'name'      => 'unblockTime',
          'label'     => Mage::helper('hubco_scraperblock')->__('Unblock Date'),
          'format'    => $dateFormatIso,
          'class'     => 'validate-date validate-date-range date-range-custom_theme-from'
      ));

      $fieldset->addField(
          'userAgent',
          'text',
          array(
              'label' => Mage::helper('hubco_scraperblock')->__('User Agent'),
              'name'  => 'userAgent',

          )
      );

      $fieldset->addField(
          'whiteList',
          'text',
          array(
              'label' => Mage::helper('hubco_scraperblock')->__('White List'),
              'name'  => 'whiteList',

          )
      );
      //$formValues = Mage::registry('hubco_scraperblock')->getDefaultValues();
      if (!is_array($formValues)) {
         $formValues = array();
      }
      if (Mage::getSingleton('adminhtml/session')->getBlockData()) {
          $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getBlockData());
          Mage::getSingleton('adminhtml/session')->getBlockData(null);
      } elseif (Mage::registry('current_block')) {
          $formValues = array_merge($formValues, Mage::registry('current_block')->getData());
      }
      $form->setValues($formValues);

      return parent::_prepareForm();
    }
}

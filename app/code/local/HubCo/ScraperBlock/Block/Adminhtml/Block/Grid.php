<?php
class HubCo_ScraperBlock_Block_Adminhtml_Block_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareCollection()
    {
        /**
         * Tell Magento which collection to use to display in the grid.
         */
        $collection = Mage::getResourceModel(
            'hubco_scraperblock/block_collection'
        );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getRowUrl($row)
    {
        /**
         * When a grid row is clicked, this is where the user should
         * be redirected to - in our example, the method editAction of
         * BrandController.php in BrandDirectory module.
         */
      return $this->getUrl(
            'hubco_scraperblock_admin/block/edit',
            array(
                'id' => $row->getId()
            )
        );
    }

    protected function _prepareColumns()
    {
        /**
         * Here, we'll define which columns to display in the grid.
         */
        $this->addColumn('block_id', array(
            'header' => $this->_getHelper()->__('Block ID'),
            'type' => 'text',
            'index' => 'block_id',
        ));

        $this->addColumn('ipAddr', array(
            'header' => $this->_getHelper()->__('IP Address'),
            'type' => 'text',
            'index' => 'ipAddr',
        ));

        $this->addColumn('blockTime', array(
            'header' => $this->_getHelper()->__('Blocked'),
            'type' => 'datetime',
            'index' => 'blockTime',
        ));
        $this->addColumn('unblockTime', array(
            'header' => $this->_getHelper()->__('Un Block'),
            'type' => 'datetime',
            'index' => 'unblockTime',
        ));
        $this->addColumn('whiteList', array(
            'header' => $this->_getHelper()->__('WhiteListed'),
            'type' => 'text',
            'index' => 'whiteList',
        ));

        $this->addColumn('userAgent', array(
            'header' => $this->_getHelper()->__('User Agent'),
            'type' => 'text',
            'index' => 'userAgent',
        ));
        return parent::_prepareColumns();
    }

    protected function _getHelper()
    {
        return Mage::helper('hubco_scraperblock');
    }
}
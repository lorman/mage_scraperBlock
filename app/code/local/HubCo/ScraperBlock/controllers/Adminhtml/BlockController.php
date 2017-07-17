<?php
class HubCo_ScraperBlock_Adminhtml_BlockController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Instantiate our grid container block and add to the page content.
     * When accessing this admin index page, we will see a grid of all
     * brands currently available in our Magento instance, along with
     * a button to add a new one if we wish.
     */
    public function indexAction()
    {
        // instantiate the grid container
        $blockBlock = $this->getLayout()
            ->createBlock('hubco_scraperblock_adminhtml/block');
        // Add the grid container as the only item on this page
        $this->loadLayout();
        //var_dump(Mage::getSingleton('core/layout')->getUpdate()->getHandles());
         //die();
        $this->_addContent($blockBlock);
        $this->renderLayout();
    }
    public function editAction()
    {
       // var_dump ($postData = $this->getRequest()->getParam('id', false)); exit;
      /**
      * Retrieve existing brand data if an ID was specified.
      * If not, we will have an empty brand entity ready to be populated.
      */

      $data = Mage::getModel('hubco_scraperblock/block');
      if ($dataId = $this->getRequest()->getParam('id', false)) {
        $data->load($dataId);
        if ($data->getId() < 1) {
          $this->_getSession()->addError(
            $this->__('This data no longer exists.')
          );
          return $this->_redirect(
            'hubco_scraperblock_admin/block/index'
          );
        }
      }

      // process $_POST data if the form was submitted
      if ($postData = $this->getRequest()->getPost('block')) {
        try {
          foreach ($postData as $key => $value) {
            if (is_array($value) && !isset($_FILES['$key'])) {
              $postData[$key] = implode(',',$value);
            }
          }
          $data->addData($postData);
          $data->save();

          $this->_getSession()->addSuccess(
            $this->__("The Block has been saved.")
          );

          // redirect to remove $_POST data from the request
          return $this->_redirect(
            'hubco_scraperblock_admin/block/edit',
            array('id' => $data->getId())
          );
        } catch (Exception $e) {
          Mage::logException($e);
          $this->_getSession()->addError($e->getMessage());
        }

        /**
        * If we get to here, then something went wrong. Continue to
        * render the page as before, the difference this time being
        * that the submitted $_POST data is available.
        */
      }
      Mage::register('current_block', $data);

      // Instantiate the form container.
      $dataEditBlock = $this->getLayout()->createBlock(
        'hubco_scraperblock_adminhtml/block_edit'
      );

      // Add the form container as the only item on this page.
      $this->loadLayout()
      ->_addContent($dataEditBlock)
      ->renderLayout();
    }

    public function deleteAction()
    {
      $block = Mage::getModel('hubco_scraperblock/block');

      if ($blockId = $this->getRequest()->getParam('id', false)) {
        $block->load($blockId);
      }

      if ($block->getId() < 1) {
        $this->_getSession()->addError(
            $this->__('This block no longer exists.')
        );
        return $this->_redirect(
            'hubco_scraperblock_admin/block/index'
        );
      }

      try {
        $block->delete();

        $this->_getSession()->addSuccess(
            $this->__('The Block has been deleted.')
        );
      } catch (Exception $e) {
        Mage::logException($e);
        $this->_getSession()->addError($e->getMessage());
      }

      return $this->_redirect(
          'hubco_scraperblock_admin/block/index'
      );
    }
    /**
     * Thanks to Ben for pointing out this method was missing. Without
     * this method the ACL rules configured in adminhtml.xml are ignored.
     */
    protected function _isAllowed()
    {
        /**
         * we include this switch to demonstrate that you can add action
         * level restrictions in your ACL rules. The isAllowed() method will
         * use the ACL rule we have configured in our adminhtml.xml file:
         * - acl
         * - - resources
         * - - - admin
         * - - - - children
         * - - - - - smashingmagazine_branddirectory
         * - - - - - - children
         * - - - - - - - brand
         *
         * eg. you could add more rules inside brand for edit and delete.
         */
        $actionName = $this->getRequest()->getActionName();
        switch ($actionName) {
            case 'index':
            case 'edit':
            case 'delete':
                // intentionally no break
            default:
                $adminSession = Mage::getSingleton('admin/session');
                $isAllowed = $adminSession
                    ->isAllowed('hubco_scraperblock/block');
                break;
        }

        return $isAllowed;
    }
}
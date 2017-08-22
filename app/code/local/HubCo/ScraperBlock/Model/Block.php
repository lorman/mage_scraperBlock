<?php
class HubCo_ScraperBlock_Model_Block
    extends Mage_Core_Model_Abstract
{
  static protected $_hasRun = false;

  protected function _construct()
  {
      /**
       * This tells Magento where the related resource model can be found.
       *
       * For a resource model, Magento will use the standard model alias -
       * in this case 'hubco_brand' - and look in
       * config.xml for a child node <resourceModel/>. This will be the
       * location that Magento will look for a model when
       * Mage::getResourceModel() is called - in our case,
       * HubCo_Brand_Model_Resource.
       */
      $this->_init('hubco_scraperblock/block');

      $this->resource = Mage::getSingleton ( 'core/resource' );

      /**
       * Retrieve the read connection
      */
      $this->readCon = $this->resource->getConnection ( 'core_read' );

      /**
       * Retrieve the write connection
      */
      $this->writeCon = $this->resource->getConnection ( 'core_write' );
  }

  public function scraperCheck($observer) {
    //return;
    if(HubCo_ScraperBlock_Model_Block::$_hasRun)
    {
      // only run once per session
      return;
    }
    HubCo_ScraperBlock_Model_Block::$_hasRun = true;

    // detect CAPTCHA and resolve if it's good or not...
    $captcha = false;
    if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'capcha') {
      $requestStr = "https://www.google.com/recaptcha/api/siteverify?secret=6LeujwwTAAAAACtG11pS7dr_vEdxjTqFLREQbRb_&response=".$_REQUEST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR'];
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $requestStr);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_TIMEOUT, 20);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $temp = curl_exec($ch);
      curl_close($ch);
      $temp = json_decode($temp);
      if ($temp->success) {
        $captcha = true;
      }
    }

    $blockTable = $this->resource->getTableName('hubco_scraperblock/block');
    $accessTable = $this->resource->getTableName('hubco_scraperblock/access');
    $block = false;
    $ipAddr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $userCleaned = addslashes($userAgent);
    if (empty($ipAddr)) {
      return;
    }

    // Unblock if Captcha passed
    if ($captcha) {
      $query = "UPDATE $blockTable SET unblockTime = NOW() WHERE ipAddr = '$ipAddr'";
      $this->writeCon->query($query);
      // delete all entries from access table (restart counter for the IP)
      $query = "DELETE FROM $accessTable WHERE ipAddr = '$ipAddr'";
      $this->writeCon->query($query);
    }

    // check if the IP should be blocked
    // don't process any google/msn/bingbot access
    if (strstr(strtolower($userAgent), 'msnbot') === false && strstr(strtolower($userAgent), 'bingbot') === false && strstr(strtolower($userAgent), 'google') === false && strstr(strtolower($userAgent), 'shipstation') === false) {
      $query = "SELECT A.ipAddr, count(*) as cnt, MAX(A.accessTime), A.userAgent
      FROM $accessTable A
      LEFT JOIN $blockTable B ON A.ipAddr = B.ipAddr
      where A.accessTime between now() - interval 24 hour and now()
      AND A.ipAddr = '$ipAddr'
      AND (B.whiteList = 0 OR B.whiteList IS NULL)
      GROUP BY ipAddr
      HAVING cnt > 100";
      $row = $this->readCon->fetchRow($query);
      if (!empty($row)) {
        $query = "INSERT INTO $blockTable (IPAddr, blockTime, unblockTime, userAgent)
        values ('$ipAddr', NOW(), NOW() + INTERVAL 1 DAY, '$userCleaned')
          ON DUPLICATE KEY UPDATE blockTime = NOW(), unblockTime = NOW() + INTERVAL 1 DAY";
        $this->writeCon->query($query);
      }
    }

    // check if the IP is blocked
    $query = "SELECT * FROM $blockTable WHERE ipAddr = '$ipAddr' AND unblockTime > NOW() AND (whiteList = 0 OR whiteList IS NULL)";
    $rows = $this->readCon->fetchAll($query);
    if (!empty($rows)) {
      // IP is blocked, display message to customer
      $message = "Our System flagged you for excessive searching or browsing. The information contained on these pages is protected by copywright laws. <br><br>
      If you were flagged in error, please email service@thehubcompanies.com or call us at 847-790-4HUB(4482). Your public IP Address is: $ipAddr
      <script src='https://www.google.com/recaptcha/api.js'></script>
      ".'
      <form action="" method="post">
      <input type="hidden" name="op" value="capcha">
      <div class="g-recaptcha" data-sitekey="6LeujwwTAAAAADwE3bZhKDBmtiwZEKJyukAlAQjm"></div>
      <input name="reset" type="submit" value="Keep Browsing"/>
      </form>';
      echo $message;

      // disconnect from MYSQL
      foreach (Mage::getSingleton('core/resource')->getConnections() as $name => $connection) {
        if ($connection instanceof Zend_Db_Adapter_Abstract) {
            $connection->closeConnection();
        }
      }
      exit;
    }

    // record the access of the IP
    $query = "INSERT INTO $accessTable (ipAddr, accessTime, userAgent) values ('$ipAddr', NOW(), '$userCleaned')";
    $this->writeCon->query($query);

    // delete all old access stores
    $query = "DELETE FROM $accessTable WHERE accessTime < now() - interval 96 hour";
    $this->writeCon->query($query);

  }

}

<?php
/**
 * Author: info@ebizmarts.com
 * Date: 3/16/15
 * Time: 1:42 PM
 * File: Data.php
 * Module: magento2
 */

namespace Ebizmarts\Mandrill\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ACTIVE           = 'mandrill/general/active';
    const XML_PATH_APIKEY           = 'mandrill/general/apikey';
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;


    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_logger = $logger;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getApiKey($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_APIKEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isActive($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param $msg
     */
    public function log($msg)
    {
        $this->_logger->info($msg);
    }

    /**
     * @return mixed
     */
    public function getTestSender()
    {
        return $this->scopeConfig->getValue(
            'checkout/payment_failed/identity',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function saveMail($mailType,$mail,$name,$couponCode,$storeId)
    {
        if ($couponCode != '') {
            $coupon = $this->_objectManager->create('Magento\SalesRule\Model\Coupon')->loadByCode($couponCode)
            $rule = $this->_objectManager->create('Magento\SalesRule\Model\Rule')->load($coupon->getRuleId());
            $couponAmount = $rule->getDiscountAmount();
            switch ($rule->getSimpleAction()) {
                case 'cart_fixed':
                    $couponType = 1;
                    break;
                case 'by_percent':
                    $couponType = 2;
                    break;
            }
        } else {
            $couponType = 0;
            $couponAmount = 0;
        }
        $sent = $this->_objectManager->create('Ebizmarts\Mandrill\Model\Mailsent');
        $date = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $sent->setMailType($mailType)
            ->setStoreId($storeId)
            ->setCustomerEmail($mail)
            ->setCustomerName($name)
            ->setCouponNumber($couponCode)
            ->setCouponType($couponType)
            ->setCouponAmount($couponAmount)
            ->setSentAt($date->gmtDate())
            ->save();
    }
}
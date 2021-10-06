<?php

namespace Eniture\WweSmallPackageQuotes\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    private $request;
    private $resourceConfig;
    private $dataHelper;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig,
        \Eniture\WweSmallPackageQuotes\Helper\Data $dataHelper
    ) {
        $this->request = $context->getRequest();
        $this->resourceConfig = $resourceConfig;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $params = $this->request->getParams();
        
        if (!empty($params)) {
            $plan       = !empty($params['pakg_group']) ? $params['pakg_group'] : 0;
            $expireDay  = isset($params['pakg_duration']) ? $params['pakg_duration'] : '';
            $expiryDate = isset($params['expiry_date']) ? $params['expiry_date'] : '';
            $planType   = isset($params['plan_type']) ? $params['plan_type'] : '';
            $pakgPrice  = isset($params['pakg_price']) ? $params['pakg_price'] : '0';
            if ($pakgPrice == '0') {
                $plan = '0';
            }
            $today =  date('F d, Y');
            if (!empty($expiryDate) && strtotime($today) > strtotime($expiryDate)) {
                $plan ='-1';
            }
            $this->saveConfigurations('plan', $plan);
            $this->saveConfigurations('expireday', $expireDay);
            $this->saveConfigurations('expiredate', $expiryDate);
            $this->saveConfigurations('storetype', $planType);
            $this->saveConfigurations('pakgprice', $pakgPrice);
            $this->dataHelper->clearCache();
        }
    }
    
        /**
         * @param type $path
         * @param type $value
         */
    function saveConfigurations($path, $value)
    {
        $this->resourceConfig->saveConfig(
            'eniture/ENWweSmpkg/'.$path,
            $value,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
    }
}

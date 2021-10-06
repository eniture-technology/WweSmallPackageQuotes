<?php
namespace Eniture\WweSmallPackageQuotes\Model\Carrier;
/**
 * class that generated request data
 */
class WweSmpkgGenerateRequestData
{
    public $registry;
    public $scopeConfig;
    public $moduleManager;
    public $dataHelper;
    public $request;
    /**
     * This variable stores service type e.g domestic, international, both
     * @var string
     */
    private $serviceType;


    public function _init(
        $scopeConfig,
        $registry,
        $moduleManager,
        $dataHelper,
        $request
    ) {
        $this->registry        = $registry;
        $this->scopeConfig     = $scopeConfig;
        $this->moduleManager   = $moduleManager;
        $this->dataHelper      = $dataHelper;
        $this->request         = $request;
    }

    /**
     * function that generates Wwe array
     * @return array
     */
    public function generateWweSmpkgArray()
    {
        $getDistance = 0;

        $wweSmpkgArr = [
            'licenseKey'    => $this->getConfigData('licenseKey'),
            'serverName'    => $this->request->getServer('SERVER_NAME'),
            'carrierMode'   => 'pro', // use test / pro
            'quotestType'   => 'small',
            'version'       => '1.0.0',
            'api'           => $this->getApiInfoArr(),
            'getDistance'   => $getDistance,
        ];

        return  $wweSmpkgArr;
    }

    /**
     * @param $request
     * @param $wweSmpkgArr
     * @param $itemsArr
     * @param $dataHelper
     * @return array
     */
    public function generateRequestArray($request, $wweSmpkgArr, $itemsArr, $cart)
    {
        if(count($wweSmpkgArr['originAddress']) > 1){
            foreach($wweSmpkgArr['originAddress'] as $wh){
                $whIDs[] = $wh['locationId'];
            }
            if(count(array_unique($whIDs)) > 1){
                foreach($wweSmpkgArr['originAddress'] as $id => $wh){
                    if(isset($wh['InstorPickupLocalDelivery'])){
                        $wweSmpkgArr['originAddress'][$id]['InstorPickupLocalDelivery'] = [];
                    }
                }
            }
        }
        $carriers = $this->registry->registry('enitureCarriers');

        $carriers['wweSmall'] = $wweSmpkgArr;
        $receiverAddress = $this->getReceiverData($request);
        $smartPost = $this->getFedExSmartPost('FedExSmartPost');
        if ($this->registry->registry('fedexSmartPost') === null) {
            $this->registry->register('fedexSmartPost', $smartPost);
        }

        $requestArr = [
            'apiVersion'                    => '2.0',
            'platform'                      => 'magento2',
            'binPackagingMultiCarrier'      => $this->binPackSuspend(),
            'autoResidentials'              => $this->autoResidentialDelivery(),
            'liftGateWithAutoResidentials'  => $this->registry->registry('radForLiftgate'),
            'FedexOneRatePricing'           => ($smartPost) ? '0' : $this->checkFedExOnerate(),
            'FedexSmartPostPricing'         => $smartPost,

            'requestKey'        => $cart->getQuote()->getId(),
            'carriers'          => $carriers,
            'receiverAddress'   => $receiverAddress,
            'commdityDetails'   => $itemsArr
        ];

        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $binsData = $this->getSavedBins();
            $requestArr = array_merge($requestArr, isset($binsData) ? $binsData : []);
        }
        
        return  $requestArr;
    }

    /**
     * @return int
     */
    public function checkFedExOnerate()
    {
        $onerate = 0;
        if ($this->registry->registry('FedexOneRatePricing')) {
            $onerate = $this->registry->registry('FedexOneRatePricing');
        }
        return $onerate;
    }

    /**
     * @param $postId
     * @return string
     */
    public function getFedExSmartPost($postId)
    {
        $return = "0";
        if ($this->moduleManager->isEnabled('Eniture_FedExSmallPackageQuotes')) {
            $isActive = $this->scopeConfig->getValue(
                "carriers/ENFedExSmpkg/active",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($isActive == 1) {
                $return = $this->scopeConfig->getValue(
                    "fedexQuoteSetting/third/$postId",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) == "no" ? "1" : "0";
            }
        }
        return $return;
    }

    /**
     * @return string
     */
    public function binPackSuspend()
    {
        $return = "0";
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $return = $this->scopeConfig->getValue("binPackaging/suspend/value", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == "no" ? "1" : "0";
        }
        return $return;
    }
    /**
     * @return int
     */
    public function autoResidentialDelivery()
    {
        $autoDetectResidential = 0;
        if ($this->moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $suspndPath = "resaddressdetection/suspend/value";
            $autoResidential = $this->scopeConfig->getValue($suspndPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($autoResidential != null && $autoResidential == 'no') {
                $autoDetectResidential = 1;
            }
        }
        if ($this->registry->registry('autoDetectResidential') === null) {
            $this->registry->register('autoDetectResidential', $autoDetectResidential);
        }

        return $autoDetectResidential ;
    }


    public function getSavedBins()
    {
        $savedBins = [];
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $boxSizeHelper = $this->dataHelper->getBoxHelper('helper');
            $savedBins = $boxSizeHelper->fillBoxingData();
        }
        return $savedBins;
    }

    /**
     * This function returns carriers array if have not empty origin address
     * @return array
     */
    public function getCarriersArray()
    {
        $carriersArr = $this->registry->registry('enitureCarriers');
        $newCarriersArr = [];
        foreach ($carriersArr as $carrkey => $carrArr) {
            $notHaveEmptyOrigin = true;
            foreach ($carrArr['originAddress'] as $key => $value) {
                if(empty($value['senderZip'])){
                    $notHaveEmptyOrigin = false;
                }
            }
            if($notHaveEmptyOrigin){
                $newCarriersArr[$carrkey] = $carrArr;
            }
        }
        
        return $newCarriersArr;
    }

    /**
     * function that returns API array
     * @return array
     */
    public function getApiInfoArr()
    {
        if ($this->autoResidentialDelivery()) {
            $resDelevery = 'no';
        } else {
            $resDelevery = ($this->getConfigData('residentialDlvry'))?'yes':'no';
        }

        $apiArray = [
            'speed_ship_username'               => $this->getConfigData('username'), //'directsolar', //
            'speed_ship_password'               => $this->getConfigData('password'), //'Supplynoho12116',//
            'authentication_key'                => $this->getConfigData('authenticationKey'), //'ED16B9D917E28E98',//
            'world_wide_express_account_number' => $this->getConfigData('accountNumber'), //'x037f5'
            'prefferedCurrency'                 => $this->registry->registry('baseCurrency'),
            'includeDeclaredValue'              => $this->registry->registry('en_insurance'),
            'residentials_delivery'             => $resDelevery,
            'deliverOnSat'                      => 'Y', // Y / N
        ];
        
        return  $apiArray;
       
    }

    /**
     * function return service data
     * @param $fieldId
     * @return string
     */
    public function getConfigData($fieldId)
    {
        $secThreeIds = ['residentialDlvry', 'weightExeeds'];
        if (in_array($fieldId, $secThreeIds)){
            $sectionId = 'WweSmQuoteSetting';
            $groupId = 'third';
        }else{
            $sectionId = 'WweSmConnSetting';
            $groupId = 'first';
        }
        
        return $this->scopeConfig->getValue("$sectionId/$groupId/$fieldId", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function returns Reveiver Data Array
     * @param $request
     * @return array
     */
    public function getReceiverData($request)
    {
        $addressType = $this->scopeConfig->getValue("resaddressdetection/addressType/value", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $receiverDataArr = [
            'addressLine'           => $request->getDestStreet(),
            'receiverCity'          => $request->getDestCity(),
            'receiverState'         => $request->getDestRegionCode(),
            'receiverZip'           => preg_replace('/\s+/', '', $request->getDestPostcode()),
            'receiverCountryCode'   => $request->getDestCountryId(),
            'defaultRADAddressType' => $addressType ?? 'residential', //get value from RAD
        ];
        
        return  $receiverDataArr;
    }
}


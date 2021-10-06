<?php
 
namespace Eniture\WweSmallPackageQuotes\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Eniture\WweSmallPackageQuotes\Helper\WweSmConstants;

/**
 * @category   Shipping
 * @package    Eniture_WweSmallPackageQuotes
 * @author     eniture.com
 * @website    https://eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WweSmpkgShipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'ENWweSmpkg';

    public $isFixed = true;

    public $rateResultFactory;

    public $rateMethodFactory;

    public $scopeConfig;

    public $dataHelper;

    public $registry;

    public $moduleManager;

    public $qty;

    public $session;

    public $productloader;

    public $mageVersion;

    public $objectManager;

    /**
     * WweSmpkgShipping constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Eniture\WweSmallPackageQuotes\Helper\Data $dataHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param WweSmpkgAdminConfiguration $wweAdminConfig
     * @param WweSmpkgShipmentPackage $wweShipPkg
     * @param WweSmpkgGenerateRequestData $wweReqData
     * @param WweSmallSetCarriersGlobaly $wweSetGlobalCarrier
     * @param WweSmpkgManageAllQuotes $wweMangQuotes
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Eniture\WweSmallPackageQuotes\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Eniture\WweSmallPackageQuotes\Model\Carrier\WweSmpkgAdminConfiguration $wweAdminConfig,
        \Eniture\WweSmallPackageQuotes\Model\Carrier\WweSmpkgShipmentPackage $wweShipPkg,
        \Eniture\WweSmallPackageQuotes\Model\Carrier\WweSmpkgGenerateRequestData $wweReqData,
        \Eniture\WweSmallPackageQuotes\Model\Carrier\WweSmallSetCarriersGlobaly $wweSetGlobalCarrier,
        \Eniture\WweSmallPackageQuotes\Model\Carrier\WweSmpkgManageAllQuotes $wweMangQuotes,
        \Magento\Framework\App\RequestInterface $httpRequest,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->scopeConfig       = $scopeConfig;
        $this->cart              = $cart;
        $this->dataHelper        = $dataHelper;
        $this->registry          = $registry;
        $this->moduleManager     = $moduleManager;
        $this->urlInterface      = $urlInterface;
        $this->session            = $session;
        $this->productloader     = $productloader;
        $this->mageVersion        = $productMetadata->getVersion();
        $this->objectManager       = $objectmanager;
        $this->wweAdminConfig       = $wweAdminConfig;
        $this->wweShipPkg       = $wweShipPkg;
        $this->wweReqData       = $wweReqData;
        $this->wweSetGlobalCarrier       = $wweSetGlobalCarrier;
        $this->wweMangQuotes       = $wweMangQuotes;
        $this->httpRequest = $httpRequest;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->scopeConfig->getValue(
            'carriers/ENWweSmpkg/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return false;
        }

        if (empty($request->getDestPostcode()) || empty($request->getDestCountryId()) ||
            empty($request->getDestCity()) || empty($request->getDestRegionId())) {
            return false;
        }
        // set shipment origin globally for instore pickup and local delivery
        if ($this->registry->registry('baseCurrency') === null) {
            $this->registry->register('baseCurrency', $this->dataHelper->getBaseCurrencyCode());
        }
        
        // Admin Configuration Class call
        $this->wweAdminConfig->_init($this->scopeConfig, $this->registry);
        
        $ItemsList          = $request->getAllItems();
        $receiverZipCode    = $request->getDestPostcode();

        $package            = $this->GetWweSmpkgShipmentPackage($ItemsList,$receiverZipCode,$request);


        $this->wweReqData->_init($this->scopeConfig, $this->registry, $this->moduleManager, $this->dataHelper, $this->httpRequest);

        $wweSmpkgArr        = $this->wweReqData->generateWweSmpkgArray();

        $wweSmpkgArr['originAddress'] = $package['origin'];

        $this->wweSetGlobalCarrier->_init($this->dataHelper);
        $resp = $this->wweSetGlobalCarrier->manageCarriersGlobaly($wweSmpkgArr, $this->registry);

        $getQuotesFromSession = $this->quotesFromSession();
        if(null !== $getQuotesFromSession){
            return $getQuotesFromSession;
        }
        
        if(!$resp){
            return FALSE;
        }
        
        $requestArr = $this->wweReqData->generateRequestArray(
            $request,
            $wweSmpkgArr,
            $package['items'],
            $this->cart
        );

        if(empty($requestArr)){
            return FALSE;
        }

        $quotes = $this->dataHelper->wweSmSendCurlRequest(WweSmConstants::QUOTES_URL, $requestArr);
        $this->wweMangQuotes->_init($quotes, $this->dataHelper, $this->scopeConfig, $this->registry, $this->moduleManager, $this->objectManager);
        $quotesResult = $this->wweMangQuotes->getQuotesResultArr($request);

        $this->session->setEnShippingQuotes($quotesResult);

        $wweSmpkgQuotes = (!empty($quotesResult))?$this->setCarrierRates($quotesResult):'';
        return $wweSmpkgQuotes;
    }
    
    /**
     * 
     * @return type
     */
    public function quotesFromSession()
    {
        $currentAction = $this->urlInterface->getCurrentUrl();
        $currentAction = strtolower($currentAction);
        if(strpos($currentAction, 'shipping-information') !== false || strpos($currentAction, 'payment-information') !== false){
            $availableSessionQuotes = $this->session->getEnShippingQuotes(); // FROM SESSSION
            $availableQuotes = (!empty($availableSessionQuotes))?$this->setCarrierRates($availableSessionQuotes):null;
        }else{
            $availableQuotes = NULL;
        }
        return $availableQuotes;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }

        return $arr;
    }
    /**
     * Get configuration data of carrier
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => [
                'GND' => __('UPS Ground'),
                '3DS' => __('UPS 3 Day Select'),
                '2DA' => __('UPS 2nd Day Air'),
                '2DM' => __('UPS 2nd Day Air A.M.'),
                '1DP' => __('UPS Next Day Air Saver'),
                '1DA' => __('UPS Next Day Air'),
                '1DM' => __('UPS Next Day Air Early'),
            ],
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }
    /**
     * This function returns package array
     * @param $items
     * @param $receiverZipCode
     * @param $request
     * @return array
     */
    public function GetWweSmpkgShipmentPackage($items, $receiverZipCode,$request)
    {
        $this->wweShipPkg->_init($request, $this->scopeConfig, $this->dataHelper, $this->productloader, $this->httpRequest);
        
        $weightConfigExeedOpt = $this->scopeConfig->getValue('WweSmQuoteSetting/third/weightExeeds', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        foreach($items as $key => $item) {
            if($item->getRealProductType() == 'configurable'){
                $this->qty= $item->getQty();
            }
            if($item->getRealProductType() == 'simple'){
                
                $productQty = ( $this->qty> 0 ) ? $this->qty: $item->getQty();
                $this->qty = 0;

                $_product       = $this->productloader->create()->load($item->getProductId());
               
                $isEnableLtl    = $_product->getData('en_ltl_check');

                $lineItemClass  = $_product->getData('en_freight_class');
                
                if ( ($isEnableLtl) || ( $_product->getWeight() > 150 && $weightConfigExeedOpt) ) {
                    $freightClass = 'ltl';
                }else{
                    $freightClass = '';
                }

                //Checking if plan is at least Standard
                $plan = $this->dataHelper->wweSmallPlanInfo("ENWweSmpkg");
                if ($plan['planNumber'] < 2) {
                    $insurance =  0;
                    $hazmat = 'N';
                } else {
                    $hazmat = ($_product->getData('en_hazmat'))?'Y':'N';
                    $insurance = $_product->getData('en_insurance');
                    if ($insurance && $this->registry->registry('en_insurance') === null) {
                        $this->registry->register('en_insurance', $insurance);
                    }
                }

                switch ($lineItemClass) {
                    case 77:
                        $lineItemClass = 77.5;
                        break;
                    case 92:
                        $lineItemClass = 92.5;
                        break;
                    default:
                        break;
                }
                
                $originAddress  = $this->wweShipPkg->wweSmpkgOriginAddress($_product, $receiverZipCode);

                $hazordousData[][$originAddress['senderZip']] = $this->setHazmatArray($_product, $hazmat);
                $package['origin'][$_product->getId()] = $originAddress;
                $orderWidget[$originAddress['senderZip']]['origin'] = $originAddress;

                $length = ( $_product->getData('en_length') != null ) ? $_product->getData('en_length') : $_product->getData('ts_dimensions_length');
                $width = ( $_product->getData('en_width') != null ) ? $_product->getData('en_width') : $_product->getData('ts_dimensions_width');
                $height = ( $_product->getData('en_height') != null ) ? $_product->getData('en_height') : $_product->getData('ts_dimensions_height');

                $lineItems = [
                    'lineItemClass'          => ($lineItemClass == 'No Freight Class'
                        || $lineItemClass == 'No') ?
                        0 : $lineItemClass,
                    'freightClass'              => $freightClass,
                    'lineItemId'                => $_product->getId(),
                    'lineItemName'              => $_product->getName(),
                    'piecesOfLineItem'          => $productQty,
                    'lineItemPrice'             => $_product->getPrice(),
                    'lineItemWeight'            => number_format($_product->getWeight(), 2, '.', ''),
                    'lineItemLength'            => number_format($length, 2, '.', ''),
                    'lineItemWidth'             => number_format($width, 2, '.', ''),
                    'lineItemHeight'            => number_format($height, 2, '.', ''),
                    'isHazmatLineItem'          => $hazmat,
                    'product_insurance_active'  => $insurance,
                    'shipBinAlone'              => $_product->getData('en_own_package'),
                    'vertical_rotation'         => $_product->getData('en_vertical_rotation'),
                ];

                $package['items'][$_product->getId()] = array_merge($lineItems);
                $orderWidget[$originAddress['senderZip']]['item'][] = $package['items'][$_product->getId()];
            }
        }

        $this->setDataInRegistry($package['origin'], $hazordousData, $orderWidget);

        return $package;
    }

    /**
     * @param type $_product
     * @return type
     */
    public function setHazmatArray($_product, $hazmat)
    {
        return [
            'lineItemId'    => $_product->getId(),
            'isHazordous'   => $hazmat== 'Y' ? '1' : '0' ,
        ];
    }

    /**
     * @param type $origin
     * @param type $hazordousData
     * @param type $setPackageDataForOrderDetail
     */
    public function setDataInRegistry($origin, $hazordousData, $orderWidget)
    {
        // set order detail widget data
        if ($this->registry->registry('setPackageDataForOrderDetail') === null) {
            $this->registry->register('setPackageDataForOrderDetail', $orderWidget);
        }

        // set hazardous data globally
        if ($this->registry->registry('hazardousShipment') === null) {
            $this->registry->register('hazardousShipment', $hazordousData);
        }
        // set shipment origin globally for instore pickup and local delivery
        if ($this->registry->registry('shipmentOrigin') === null) {
            $this->registry->register('shipmentOrigin', $origin);
        }
    }

    public function setCarrierRates($quotes) {
        $carrersArray   = $this->registry->registry('enitureCarrierCodes');
        $carrersTitle   = $this->registry->registry('enitureCarrierTitle');
        
        $result = $this->rateResultFactory->create();

        foreach ($quotes as $carrierkey => $quote) {
            foreach ($quote as $key => $carreir) {
                    if(isset($carreir['code']) && isset($carreir['title']) && isset($carreir['rate'])){
                        $method = $this->rateMethodFactory->create();
                        $carrierCode    = (isset($carrersTitle[$carrierkey]))? $carrersTitle[$carrierkey] : $this->_code;
                        $carrierTitle   = (isset($carrersArray[$carrierkey]))? $carrersArray[$carrierkey] : $this->getConfigData('title');
                        $method->setCarrierTitle($carrierCode);
                        $method->setCarrier($carrierTitle);
                        $method->setMethod($carreir['code']);
                        $method->setMethodTitle($carreir['title']);
                        $method->setPrice($carreir['rate']);

                        $result->append($method);
                    }

                }
            }
        
        return $result;
    }
}

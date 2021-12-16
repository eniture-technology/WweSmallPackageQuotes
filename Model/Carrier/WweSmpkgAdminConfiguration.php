<?php
namespace Eniture\WweSmallPackageQuotes\Model\Carrier;

/**
 * class for admin configuration that runs first
 */
class WweSmpkgAdminConfiguration
{
    /**
     * @var \Magento\Framework\Registry
     */

    public $registry;
    public $scopeConfig;

    /**
     * @param type $scopeConfig
     * @param type $registry
     */
    public function _init($scopeConfig, $registry)
    {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->setCarriersAndHelpersCodesGlobaly();
        $this->myUniqueLineItemAttribute();
    }

    /**
     * This functuon set unique Line Item Attributes of carriers
     */
    public function myUniqueLineItemAttribute()
    {
        $lineItemAttArr =  [];
        if (is_null($this->registry->registry('UniqueLineItemAttributes'))) {
            $this->registry->register('UniqueLineItemAttributes', $lineItemAttArr);
        }
    }

    /**
     * This function is for set carriers codes and helpers code globaly
     */
    public function setCarriersAndHelpersCodesGlobaly()
    {
        $this->setCodesGlobaly('enitureCarrierCodes', 'ENWweSmpkg');
        $this->setCodesGlobaly('enitureCarrierTitle', 'Worldwide Small Package Quotes');
        $this->setCodesGlobaly('enitureHelpersCodes', '\Eniture\WweSmallPackageQuotes');
        $this->setCodesGlobaly('enitureActiveModules', $this->checkModuleIsEnabled());
        $this->setCodesGlobaly('enatureModuleTypes', 'small');
    }

    /**
     * return if this module is enable or not
     * @return boolean
     */
    public function checkModuleIsEnabled()
    {
        $grpSecPath = "carriers/ENWweSmpkg/active";
        return $this->scopeConfig->getValue($grpSecPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * This function sets Codes Globaly e.g carrier code or helper code
     * @param $globArrayName
     * @param $arrValue
     */
    public function setCodesGlobaly($globArrayName, $arrValue)
    {
        if (is_null($this->registry->registry($globArrayName))) {
            $codesArray = [];
            $codesArray['wweSmall'] = $arrValue;
            $this->registry->register($globArrayName, $codesArray);
        } else {
            $codesArray = $this->registry->registry($globArrayName);
            $codesArray['wweSmall'] = $arrValue;
            $this->registry->unregister($globArrayName);
            $this->registry->register($globArrayName, $codesArray);
        }
    }
}

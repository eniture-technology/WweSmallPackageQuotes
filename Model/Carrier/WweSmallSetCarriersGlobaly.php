<?php
/**
 * WWE Small Package
 * @package     WWE Small Package
 * @author      Eniture-Technology
 */
namespace Eniture\WweSmallPackageQuotes\Model\Carrier;

/**
 * Class for set carriers globally
 */
class WweSmallSetCarriersGlobaly
{
    public $dataHelper;
    /**
     * constructor of class
     */
    public function _init($dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * function for magange carriers globally
     * @param $wweArr
     * @return boolean
     */
    public function manageCarriersGlobaly($wweArr, $registry)
    {
        $this->_registry = $registry;
        if ($this->_registry->registry('enitureCarriers') === null) {
            $enitureCarriersArray = [];
            $enitureCarriersArray['wweSmall'] = $wweArr;
            $this->_registry->register('enitureCarriers', $enitureCarriersArray);
        } else {
            $carriersArr = $this->_registry->registry('enitureCarriers');
            $carriersArr['wweSmall'] = $wweArr;
            $this->_registry->unregister('enitureCarriers');
            $this->_registry->register('enitureCarriers', $carriersArr);
        }

        $activeEnitureModulesCount = $this->getActiveEnitureModulesCount();

        if (count($this->_registry->registry('enitureCarriers')) < $activeEnitureModulesCount) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * function that return count of active eniture modules
     * @return int
     */
    public function getActiveEnitureModulesCount()
    {
        $activeModules = array_keys($this->dataHelper->getActiveCarriersForENCount());
        $activeEnitureModulesArr = array_filter($activeModules, function ($moduleName) {
            if (substr($moduleName, 0, 2) == 'EN') {
                return true;
            }
            return false;
        });

        return count($activeEnitureModulesArr);
    }

    /**
     * This function accepts all quotes data and sends to its respective module functions to
     * process and return final result array.
     * @param $quotes
     * @return array
     */
    public function manageQuotes($quotes)
    {
        $helpersArr = $this->_registry->registry('enitureHelpersCodes');
        $resultArr = [];
        foreach ($quotes as $key => $quote) {
            $helperId = $helpersArr[$key];
            $wweResultData = $this->_registry->helper($helperId)->getQuotesResults($quote);
            if ($wweResultData != false) {
                $resultArr[$key] = $wweResultData;
            }
        }

        return $resultArr;
    }
}

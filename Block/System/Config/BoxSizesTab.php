<?php

namespace Eniture\WweSmallPackageQuotes\Block\System\Config;

use Eniture\WweSmallPackageQuotes\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

class BoxSizesTab extends Field
{
    /**
     *
     */
    const BOXSIZESTAB_TEMPLATE = 'system/config/boxsizestab.phtml';

    /**
     * @var Manager
     */
    private $moduleManager;
    /**
     * @var string
     */
    public $enable = 'no';
    /**
     * @var
     */
    public $boxSizeData;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var
     */
    public $licenseKey;
    /**
     * @var bool
     */
    public $isFedExModule = false;
    /**
     * @var Context
     */
    public $context;
    /**
     * @var
     */
    public $smallTrialMsg;
    /**
     * @var
     */
    public $boxUseSuspended;
    /**
     * @var
     */
    public $getBoxSizes;

    /**
     * @param Context $context
     * @param Manager $moduleManager
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager,
        Data $dataHelper,
        $data = []
    ) {
        $this->moduleManager    = $moduleManager;
        $this->objectManager    = $objectManager;
        $this->context          = $context;
        $this->dataHelper       = $dataHelper;
        $this->checkBinPackagingModule();
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BOXSIZESTAB_TEMPLATE);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return html
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * This function returns the HTML, used in the Block
     * @return html
     */

    public function getHtml()
    {
        return $this->_toHtml();
    }

    /**
     * checkBinPackagingModule
     */
    public function checkBinPackagingModule()
    {
        if ($this->moduleManager->isEnabled('Eniture_StandardBoxSizes')) {
            $scopeConfig            = $this->context->getScopeConfig();
            $configPath             = "WweSmConnSetting/first/licenseKey";
            $this->licenseKey       = $scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
            $this->enable           = 'yes';
            $dataHelper             = $this->objectManager->get("Eniture\StandardBoxSizes\Helper\Data");
            $this->boxSizeData      = $dataHelper->boxSizesDataHandling($this->licenseKey);
            $this->smallTrialMsg    = $dataHelper->checkSmallModuleTrial();
            $this->boxUseSuspended  = $dataHelper->boxUseSuspended();
            $this->getBoxSizes      = $dataHelper->getBoxSizes();
        }
    }

    /**
     * @return string
     */
    public function saveBoxUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/SaveBoxsize/';
    }

    /**
     * @return string
     */
    public function deleteBoxUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/DeleteBoxsize/';
    }

    /**
     * @return string
     */
    public function editBoxUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/EditBoxsize/';
    }

    /**
     * @return string
     */
    public function boxAvailableUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/BoxAvailability/';
    }

    /**
     * @return string
     */
    public function suspendBoxUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/SuspendedBoxSizes/';
    }

    /**
     * @return string
     */
    public function autoRenewBoxPlanUrl()
    {
        return $this->getbaseUrl().'/StandardBoxSizes/Box/AutoRenewPlan/';
    }
    /**
     * @return string
     */
    public function loadOneRateBoxesUrl()
    {
        return '';
    }

    /**
     * @return string
     */
    public function saveFedExOneRateUrl()
    {
        return '';
    }
}

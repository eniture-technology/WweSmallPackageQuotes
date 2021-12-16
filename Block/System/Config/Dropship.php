<?php

namespace Eniture\WweSmallPackageQuotes\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Eniture\WweSmallPackageQuotes\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Dropship extends Field
{
    /**
     * Dropship Template path
     */
    const DROPSHIP_TEMPLATE = 'system/config/dropship.phtml';
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var int
     */
    public $currentPlan;
    /**
     * @var array
     */
    public $dropships;


    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->fetchDropships();
        $this->getCurrentPlan();
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::DROPSHIP_TEMPLATE);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return html
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
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
     * @return string
     */
    public function getDsAjaxUrl()
    {
        return $this->getbaseUrl().'wwesmallpackagequotes/Dropship/';
    }

    /**
     * Fetching all the dropships from Database
     */
    public function fetchDropships()
    {
        $this->dropships = $this->dataHelper->fetchWarehouseSecData('dropship');
    }

    /**
     * this function return the current plan active
     * @return void
     */
    public function getCurrentPlan()
    {
        $this->currentPlan =  $this->dataHelper->wweSmallPlanInfo()['planNumber'];
    }
}

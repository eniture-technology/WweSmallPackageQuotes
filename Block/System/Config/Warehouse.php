<?php

namespace Eniture\WweSmallPackageQuotes\Block\System\Config;

use Eniture\WweSmallPackageQuotes\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Warehouse extends Field
{

    const WAREHOUSE_TEMPLATE = 'system/config/warehouse.phtml';
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
    public $warehouses = [];

    /**
     * @var int
     */
    public $canAddWarehouse = 1;

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
        $this->fetchWarehouses();
        $this->checkCurrentPlan();
        $this->addWhRestriction();
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::WAREHOUSE_TEMPLATE);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return element
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @return html
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getbaseUrl().'wwesmallpackagequotes/Warehouse/';
    }

    /**
     * Fetching all the warehouses from Database
     */
    public function fetchWarehouses ()
    {
        $this->warehouses = $this->dataHelper->fetchWarehouseSecData('warehouse');
    }

    /**
     * this function return the current plan active
     */
    public function checkCurrentPlan()
    {
        $this->currentPlan =  $this->dataHelper->wweSmallPlanInfo()['planNumber'];
    }

    /**
     * Show Wwe Small Plan Notice
     * @return string
     */
    public function wweSmallSetPlanNotice()
    {
        return $this->dataHelper->wweSmallSetPlanNotice();
    }

    /**
     * This function checks if user is on Basic Plan and has more than 1 warehouse then restrict the user to add more
     */
    public function addWhRestriction()
    {
        $this->canAddWarehouse = ($this->currentPlan < 2 && count($this->warehouses) > 0) ? 0 : 1;
    }
}

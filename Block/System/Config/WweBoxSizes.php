<?php

namespace Eniture\WweSmallPackageQuotes\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WweBoxSizes extends \Magento\Config\Block\System\Config\Form\Field
{
    const BOXSIZE_TEMPLATE = 'system/config/boxsizes.phtml';
    
    public $dataHelper;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Eniture\WweSmallPackageQuotes\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BOXSIZE_TEMPLATE);
        }
        return $this;
    }
    
    /**
     * @param AbstractElement $element
     * @return element
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    /**
     * @return url
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}

<?php
namespace Eniture\WweSmallPackageQuotes\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;

class StandrdBoxSize extends Field
{
    
    const SBS_TEMPLATE = 'system/config/standrdboxsize.phtml';
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Manager $moduleManager,
        $data = []
    ) {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $data);
    }
    
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->moduleManager->isOutputEnabled('Eniture_StandardBoxSizes')) {
            if (!$this->getTemplate()) {
                $this->setTemplate(static::SBS_TEMPLATE);
            }
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
     * @param AbstractElement $element
     * @return html
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}

<?php
namespace Eniture\WweSmallPackageQuotes\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Eniture\WweSmallPackageQuotes\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnection extends Field
{
    const BUTTON_TEMPLATE = 'system/config/testconnection.phtml';

    /**
     * @var Context
     */
    private $context;
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(Context $context, Data $dataHelper, $data = [])
    {
        $this->context = $context;
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
            $this->setTemplate(static::BUTTON_TEMPLATE);
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
     * @return url
     */
    public function getAjaxCheckUrl()
    {
        return $this->getbaseUrl().'wwesmallpackagequotes/Test/TestConnection/';
    }

    /**
     * @param AbstractElement $element
     * @return array
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $this->addData(
            [
                'id'            => 'wwesm-test-conn',
                'button_label'  => 'Test Connection',
            ]
        );
        return $this->_toHtml();
    }

    /**
     * Show Wwe Small Plan Notice
     * @return string
     */
    public function wweSmPlanNotice()
    {
        return $this->dataHelper->wweSmallSetPlanNotice();
    }

    public function wweSmConnMsg()
    {
        return '<div class="message message-notice notice wwesm-conn-setting-note">You must have a Worldwide Express account to use this application. If you do not have one contact Worldwide Express at 800-734-5351 or <a target="_blank" href="https://eniture.com/request-worldwide-express-account-number/">register online</a>.</div>';
    }
}

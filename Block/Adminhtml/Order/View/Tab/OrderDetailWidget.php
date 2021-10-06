<?php
namespace Eniture\WweSmallPackageQuotes\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class OrderDetailWidget extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/orderdetailwidget.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Additional Order Details');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Additional Order Details');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if ($this->coreRegistry->registry('orderWidgetFlag') === null) {
            $this->coreRegistry->register('orderWidgetFlag', 'yes');
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('orderdetailwidget_wwesmall/*/OrderDetailWidget', ['_current' => true]);
    }
}

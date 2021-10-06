<?php
namespace Eniture\WweSmallPackageQuotes\Model;

class Warehouse extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\WweSmallPackageQuotes\Model\ResourceModel\Warehouse');
    }
}

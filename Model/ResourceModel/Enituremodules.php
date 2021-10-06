<?php
namespace Eniture\WweSmallPackageQuotes\Model\ResourceModel;

class Enituremodules extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enituremodules', 'module_id');
    }
}

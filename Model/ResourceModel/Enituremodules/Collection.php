<?php

namespace Eniture\WweSmallPackageQuotes\Model\ResourceModel\Enituremodules;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eniture\WweSmallPackageQuotes\Model\Enituremodules', 'Eniture\WweSmallPackageQuotes\Model\ResourceModel\Enituremodules');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}

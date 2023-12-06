<?php

namespace Eniture\WweSmallPackageQuotes\Controller\Warehouse;

use Eniture\WweSmallPackageQuotes\Helper\Data;
use \Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class EditWarehouse extends Action
{
    /**
     * @var Data Object
     */
    private $dataHelper;
    /**
     * @param Context $context
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * Fetch Warehouse From Database
     */
    public function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $editWhData[$key] = htmlspecialchars($post, ENT_QUOTES);
        }

        $warehouseId   = $editWhData['edit_id'];
        $warehouseList  = $this->dataHelper->fetchWarehouseWithID('warehouse', $warehouseId);

        //Get plan
//        $plan = $this->dataHelper->wweSmallPlanInfo('ENWweSmpkg')['planNumber'];
//        if ($plan != 3) {
//            $warehouseList[0]['in_store'] = null;
//            $warehouseList[0]['local_delivery'] = null;
//        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($warehouseList));
    }
}

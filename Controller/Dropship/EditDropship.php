<?php

namespace Eniture\WweSmallPackageQuotes\Controller\Dropship;

use Eniture\WweSmallPackageQuotes\Helper\Data;
use \Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class EditDropship
 * @package Eniture\WweSmallPackageQuotes\Controller\Dropship
 */
class EditDropship extends Action
{
    /**
     * @var Data
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
     * @return string
     */
    public function execute()
    {
        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $editDsData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $getDropshipId  = $editDsData['edit_id'];
        $dropshipList   = $this->dataHelper->fetchWarehouseWithID('dropship', $getDropshipId);

        //Get Location Data
        $plan = $this->dataHelper->wweSmallPlanInfo('ENWweSmpkg')['planNumber'];
//        if ($plan != 3) {
//            $dropshipList[0]['in_store'] = null;
//            $dropshipList[0]['local_delivery'] = null;
//        }

        //Change html entities code
        $nick = $dropshipList[0]['nickname'];
        $dropshipList[0]['nickname'] = html_entity_decode($nick, ENT_QUOTES);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($dropshipList));
    }
}

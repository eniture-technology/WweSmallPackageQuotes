<?php

namespace Eniture\WweSmallPackageQuotes\Controller\Warehouse;

use Eniture\WweSmallPackageQuotes\Helper\Data;
use Eniture\WweSmallPackageQuotes\Model\WarehouseFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class SaveWarehouse extends Action
{
    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param WarehouseFactory $warehouseFactory
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        WarehouseFactory $warehouseFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->warehouseFactory = $warehouseFactory->create();
        parent::__construct($context);
    }
    /**
     * Save and Update Warehouse Data
     */
    public function execute()
    {
        $insertQry = ['insertId' => 0, 'lastId' => 0];
        $updateQry = 0;
        $msg = 'Warehouse already exists.';
        $updateInspld = false;

        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $saveWhData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $inputDataArr = $this->dataHelper->wweSmOriginArray($saveWhData);
        $validateData = $this->dataHelper->wweSmValidatedPostData($inputDataArr);
        $city  = $validateData['city'];
        $state = $validateData['state'];
        $zip   = $validateData['zip'];

        if ($city != 'Error') {

            $warehouseId  = isset($saveWhData['originId']) ? intval($saveWhData['originId']) : "";
            $getWarehouse = $this->checkWarehouseList($city, $state, $zip);

            if (!empty($getWarehouse)) {
                $whId = reset($getWarehouse)['warehouse_id'];
                if ($warehouseId == $whId) {
                    // check any change in InspLd data
                    $updateInspld = $this->dataHelper->checkUpdatePickupDelivery($getWarehouse, $validateData);
                }
            }

            if ($warehouseId && (empty($getWarehouse) || $updateInspld)) {
                $updateQry = $this->dataHelper->updateWarehousData($validateData, "warehouse_id='".$warehouseId."'");
                $msg = 'Warehouse updated successfully.';
            } elseif (empty($getWarehouse)) {
                $insertQry = $this->dataHelper->insertWarehouseData($validateData, $warehouseId);
                $msg = 'New warehouse added successfully.';
            }
            $lastId = ($updateQry) ? $warehouseId : $insertQry['lastId'];
        } else {
            $lastId = '';
            $msg = 'City name is invalid';
        }
        $canAddWh = $this->dataHelper->whPlanRestriction();
        $warehouseList = $this->warehouseListData($validateData, $insertQry, $updateQry, $lastId, $canAddWh, $msg);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($warehouseList));
    }

    /**
     * @param $validateData
     * @param $insertQry
     * @param $updateQry
     * @param $lastId
     * @param $canAddWh
     * @param $msg
     * @return array
     */
    public function warehouseListData($validateData, $insertQry, $updateQry, $lastId, $canAddWh, $msg)
    {
        return [
            'id'             => $lastId,
            'origin_city'    => $validateData['city'],
            'origin_state'   => $validateData['state'],
            'origin_zip'     => $validateData['zip'],
            'origin_country' => $validateData['country'],
            'insert_qry'     => $insertQry['insertId'],
            'update_qry'     => $updateQry,
            'canAddWh'       => $canAddWh,
            'msg'            => $msg
        ];
    }

    /**
     * @param string $city
     * @param string $state
     * @param string $zip
     * @return array
     */
    public function checkWarehouseList($city, $state, $zip)
    {
        $whCollection = $this->warehouseFactory->getCollection()
            ->addFilter('location', ['eq' => 'warehouse'])
            ->addFilter('city', ['eq' => $city])
            ->addFilter('state', ['eq' => $state])
            ->addFilter('zip', ['eq' => $zip]);

        return $this->dataHelper->purifyCollectionData($whCollection);
    }
}

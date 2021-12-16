<?php
namespace Eniture\WweSmallPackageQuotes\Controller\Dropship;

use Eniture\WweSmallPackageQuotes\Helper\Data;
use Eniture\WweSmallPackageQuotes\Model\WarehouseFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class SaveDropship extends Action
{
    /**
     * @var Data Object
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
        $this->warehouseFactory    = $warehouseFactory->create();
        parent::__construct($context);
    }

    /**
     * Save and Update Drop Ship from Database
     */
    public function execute()
    {
        $insertQry = ['insertId' => 0, 'lastId' => 0];
        $updateQry = 0;
        $updateInspLd = false;
        $msg = 'Drop ship already exists.';

        foreach ($this->getRequest()->getPostValue() as $key => $post) {
            $saveDsData[$key] = filter_var($post, FILTER_SANITIZE_STRING);
        }

        $inputDataArr = $this->dataHelper->wweSmOriginArray($saveDsData);
        $validateData = $this->dataHelper->wweSmValidatedPostData($inputDataArr);
        $city  = $validateData['city'];
        $state = $validateData['state'];
        $zip   = $validateData['zip'];
        $nickname = $validateData['nickname'] = $this->nicknameValid(trim($validateData['nickname']), $zip, $city);

        if ($city != 'Error') {
            $dropshipId   = isset($saveDsData['dropshipId']) ? intval($saveDsData['dropshipId']) : "";
            $getDropship  = $this->checkDropshipList($city, $state, $zip, $nickname);

            if (!empty($getDropship)) {
                $dsId = reset($getDropship)['warehouse_id'];
                if ($dropshipId == $dsId) {
                    // check any change in InspLd data
                    $updateInspLd = $this->dataHelper->checkUpdatePickupDelivery($getDropship, $validateData);
                }
            }

            if ($dropshipId && (empty($getDropship) || $updateInspLd)) {
                $updateQry = $this->dataHelper->updateWarehousData($validateData, "warehouse_id='".$dropshipId."'");
                $msg = 'Drop ship updated successfully.';
            } elseif (empty($getDropship) && ($this->countNickname($nickname) == 0)) {
                    $insertQry = $this->dataHelper->insertWarehouseData($validateData, $dropshipId);
                    $msg = 'Drop ship added successfully.';
            }
            $lastId = ($updateQry) ? $dropshipId : $insertQry['lastId'];
        } else {
            $lastId = '';
            $msg = 'City name is invalid';
        }


        $dropshipList = $this->dropshipListData($validateData, $insertQry, $updateQry, $lastId, $msg);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($dropshipList));
    }

    /**
     * @param array $validateData
     * @param int $insertQry
     * @param int $updateQry
     * @param int|string $lastId
     * @return array
     */
    public function dropshipListData($validateData, $insertQry, $updateQry, $lastId, $msg)
    {
        return [
            'origin_city'   => $validateData['city'],
            'origin_state'  => $validateData['state'],
            'origin_zip'    => $validateData['zip'],
            'origin_country'=> $validateData['country'],
            'nickname'      =>$validateData['nickname'],
            'insert_qry'    => $insertQry['insertId'],
            'update_qry'    => $updateQry,
            'id'            => $lastId,
            'msg'           => $msg
        ];
    }

    /**
     * @param $city
     * @param $state
     * @param $zip
     * @param $nickname
     * @return array
     */
    public function checkDropshipList($city, $state, $zip, $nickname)
    {
        $dsCollection  = $this->warehouseFactory->getCollection()
                                    ->addFilter('location', ['eq' => 'dropship'])
                                    ->addFilter('city', ['eq' => $city])
                                    ->addFilter('state', ['eq' => $state])
                                    ->addFilter('zip', ['eq' => $zip])
                                    ->addFilter('nickname', ['eq' => $nickname]);

        return $this->dataHelper->purifyCollectionData($dsCollection);
    }

    /**
     * @param string $nickname
     * @return int
     */
    public function countNickname($nickname)
    {
            $dsCollection = $this->warehouseFactory->getCollection()
                                        ->addFilter('location', ['eq' => 'dropship'])
                                        ->addFilter('nickname', ['eq' => $nickname]);
            return count($this->dataHelper->purifyCollectionData($dsCollection));
    }

    /**
     * @param string $nickname
     * @param string $zip
     * @param string $city
     * @return string
     */
    public function nicknameValid($nickname, $zip, $city)
    {
        $defaultRegex = "/DS_[0-9 a-z A-Z]+_[a-z A-Z]*/";
        if (preg_match($defaultRegex, $nickname) || empty($nickname)) {
            $nickname = 'DS_'.$zip.'_'.$city;
        }
        return $nickname;
    }
}

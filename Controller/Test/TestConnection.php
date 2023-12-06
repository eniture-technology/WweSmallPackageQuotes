<?php

namespace Eniture\WweSmallPackageQuotes\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Eniture\WweSmallPackageQuotes\Helper\Data;
use Eniture\WweSmallPackageQuotes\Helper\WweSmConstants;

class TestConnection extends Action
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * TestConnection constructor.
     * @param Context $context
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Data $dataHelper
    ) {
        $this->dataHelper   = $dataHelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $credentials = [];
        foreach ($this->getRequest()->getPostValue() as $key => $data) {
            $credentials[$key] = $data;
        }

        $postData = [
            'platform'                          => 'magento2',
            'speed_freight_username'            => $credentials['username'] ?? '',
            'speed_freight_password'            => $credentials['password'] ?? '',
            'plugin_licence_key'                => $credentials['pluginLicenceKey'] ?? '',
            'plugin_domain_name'                => $this->getStoreUrl(),
        ];

        if(isset($credentials['apiEndpoint']) && $credentials['apiEndpoint'] == 'new'){
            $postData['ApiVersion'] = '2.0';
            $postData['clientId'] = $credentials['clientId'] ?? '';
            $postData['clientSecret'] = $credentials['clientSecret'] ?? '';
        }else{
            $postData['authentication_key'] = $credentials['authenticationKey'] ?? '';
            $postData['world_wide_express_account_number'] = $credentials['accountNumber'] ?? '';
        }

        $response = $this->dataHelper->wweSmSendCurlRequest(WweSmConstants::TEST_CONN_URL, $postData);

        $result = $this->wweSmTestConnResponse($response);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }

    /**
     * @param object $response
     * @return object
     */
    public function wweSmTestConnResponse($response)
    {

        $return = [];
        $successMsg = 'The test resulted in a successful connection.';
        $erMsg = 'Empty response from API';

        if(empty($response)){
            $return['Error'] =  $erMsg;
        } elseif(isset($response->severity)){
            if($response->severity == 'SUCCESS'){
                $return['Success'] =  $successMsg;
            }else{
                $return['Error'] =  $response->Message ?? $erMsg;
            }
        }elseif (isset($response->error) && ($response->error == 1 || $response->error == 0)) {
            $return['Error'] =  $response->error_desc ?? $erMsg;
        }elseif (isset($response->success) && $response->success == 1) {
            $return['Success'] =  $successMsg;
        } else {
            $return['Error'] =  'An empty or unknown response format, therefore we are unable to determine whether it was successful or an error';
        }

        return json_encode($return);
    }

    /**
     * This function returns the Current Store Url
     * @return string
     */
    public function getStoreUrl()
    {
        // It will be written to return Current Store Url in multi-store view
        return $this->getRequest()->getServer('SERVER_NAME');
    }
}

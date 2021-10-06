<?php

namespace Eniture\WweSmallPackageQuotes\Block\Adminhtml\Product;

use \Magento\Backend\Block\Template\Context;

class ProductPlanRestriction extends \Magento\Config\Block\System\Config\Form\Field
{
    const PRODUCT_TEMPLATE = 'product/productplanrestriction.phtml';
    
    /**
     * @var string
     */
    public $enable = 'no';
    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shipconfig;
    /**
     * @var \Eniture\WweSmallPackageQuotes\Helper\Data
     */
    public $dataHelper;
    /**
     * @var Context
     */
    private $context;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Shipping\Model\Config $shipconfig,
        \Eniture\WweSmallPackageQuotes\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->shipconfig = $shipconfig;
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
            $this->setTemplate(static::PRODUCT_TEMPLATE);
        }
        return $this;
    }
  
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return html
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function planMsg($planInfo)
    {
        $data = ['hazmat' => ['count' => 'hazCount',
                              'enabled' => 'hazEnCount',
                              'return' => 'hazmatMsg'],
                'insurance' => ['count' => 'insCount',
                                'enabled' => 'insEnCount',
                                'return' => 'insuranceMsg']
                ];
        $return = [];
        foreach ($data as $key => $value) {
            if ($planInfo[$value['count']] == $planInfo[$value['enabled']]) {
                $return[$value['return']] = null;
            } elseif ($planInfo[$value['enabled']] == 0) {
                $return[$value['return']] = '';
            } else {
                $return[$value['return']] = $this->setPlanMsg($planInfo['data'], $key);
            }
        }
        return $return;
    }

    public function setPlanMsg($msgInfo, $index)
    {
        $msg = "";
        foreach ($msgInfo as  $res) {
            if (isset($res[$index])){
                if ($res[$index] == 'Enabled') {
                    $planMsg = ' '. $res['label'] . ' : <b>' . $res[$index] . '</b>.<br>';
                }
                if ($res[$index] == 'Disabled') {
                    $planMsg = ' '. $res['label'] . ' : Upgrade to <b>Standard Plan</b> to enable.<br>';
                }

                $msg .=  $planMsg ;
            }
        }

        return $msg;
    }

    public function getPlanInfo()
    {
        $numLTL = $numSmpkg = $hazEn = $insEn = 0;

        $activeCarriers = array_keys($this->shipconfig->getActiveCarriers());
        foreach ($activeCarriers as $carrierCode) {
            $hazmat = $insurance = 'Disabled';
            $enCarrier = substr($carrierCode, 0, 2);
            if ($enCarrier == 'EN') {
                $carrierLabel = $this->getConfiguration($carrierCode, 'label');
                $carrierPlan = $this->getConfiguration($carrierCode, 'plan');

                $restriction['data'][$carrierCode] = [
                    'label' => $carrierLabel,
                    'plan' => $carrierPlan
                ];
                if(strpos($carrierCode,'LTL')){
                    $numLTL++;
                }
                if(strpos($carrierCode,'Smpkg')){
                    $numSmpkg++;
                }
                if ($carrierPlan > 1) {
                    $hazmat = $insurance = 'Enabled';
                    $hazEn++;
                }
                if($numLTL) {
                    $restriction['data'][$carrierCode]['hazmat'] = $hazmat;
                } elseif ($numSmpkg) {
                    if ($carrierPlan > 1) {
                        $insEn++;
                    }
                    $restriction['data'][$carrierCode]['hazmat'] = $hazmat;
                    $restriction['data'][$carrierCode]['insurance'] = $insurance;
                }
            }
        }
        $restriction['hazCount'] = $numSmpkg+$numLTL;
        $restriction['insCount'] = $numSmpkg;
        $restriction['hazEnCount'] = $hazEn;
        $restriction['insEnCount'] = $insEn;
        return $restriction;
    }

    public function getConfiguration($carrierCode, $reqFor) {
        return $this->context->getScopeConfig()->getValue(
            'eniture/'.$carrierCode.'/'.$reqFor.''
        );
    }
}

<?php 
namespace Eniture\WweSmallPackageQuotes\Model\Source;
class HandlingFee
{
	public function toOptionArray()
	{
        return [
            'handlingFeeVal' =>
                ['value' => 'flat',  'label'  => 'Flat Rate'],
                ['value' => '%',     'label'  => 'Percentage ( % )'],
        ];
    }

}
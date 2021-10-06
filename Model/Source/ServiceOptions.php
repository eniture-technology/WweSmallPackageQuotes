<?php 
namespace Eniture\WweSmallPackageQuotes\Model\Source;
class ServiceOptions
{
	public function toOptionArray()
	{
        return [
            'serviceOptions' =>
                ['value' => 'GND',  'label'  => 'UPS Ground'],

                ['value' => '3DS',  'label'  => 'UPS 3 Day Select'],

                ['value' => '2DA',  'label'  => 'UPS 2nd Day Air'],

                ['value' => '2DM',  'label'  => 'UPS 2nd Day Air A.M.'],

                ['value' => '1DP',  'label'  => 'UPS Next Day Air Saver'],

                ['value' => '1DA',  'label'  => 'UPS Next Day Air'],

                ['value' => '1DM',  'label'  => 'UPS Next Day Air Early'],
            ];
    }
}

<?php
namespace Eniture\WweSmallPackageQuotes\Model\Source;

class InternationalServiceOptions
{
    public function toOptionArray()
    {
        return [
            'serviceOptions' =>
                ['value' => '01',  'label'  => 'UPS Worldwide Express'],

                ['value' => '28',  'label'  => 'UPS Worldwide Saver'],

                ['value' => '21',  'label'  => 'UPS Worldwide Express Plus'],

                ['value' => '05',  'label'  => 'UPS Worldwide Expedited'],

                ['value' => '03',  'label'  => 'UPS Standard']
            ];
    }
}

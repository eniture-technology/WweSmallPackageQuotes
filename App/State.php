<?php

namespace Eniture\WweSmallPackageQuotes\App;

use Magento\Framework\App\State as ParentState;
use Magento\Framework\App\Area;

class State extends ParentState
{
    public function validateAreaCode()
    {
        if (!isset($this->_areaCode)) {
            $this->setAreaCode(Area::AREA_GLOBAL);
        }
    }
}

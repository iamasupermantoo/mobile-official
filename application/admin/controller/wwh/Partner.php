<?php

namespace app\admin\controller\wwh;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Partner extends Backend
{
    
    /**
     * Partner模型对象
     * @var \app\admin\model\wwh\Partner
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\wwh\Partner;

    }

    public function import()
    {
        parent::import();
    }

}

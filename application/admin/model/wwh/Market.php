<?php

namespace app\admin\model\wwh;

use think\Model;


class Market extends Model
{

    

    

    // 表名
    protected $name = 'wwh_market';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}

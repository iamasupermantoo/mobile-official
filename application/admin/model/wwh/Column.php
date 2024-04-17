<?php

namespace app\admin\model\wwh;

use think\Exception;
use think\Model;


class Column extends Model
{

    // 表名
    protected $name = 'wwh_column';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'status_text',
        'classify_text',
        'url'
    ];

    public function getUrlAttr($value, $data)
    {
        $diyname = $data['diyname'] ? $data['diyname'] : $data['id'];
        return isset($data['type']) && isset($data['outlink']) && $data['type'] == 'link' ? $data['outlink'] : addon_url('wwh/index/column', [':id' => $data['id'], ':diyname' => $diyname],'html');
    }

    public static function getTypeList()
    {
        return ['list' => __('List'), 'link' => __('Link')];
    }

    public static function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getClassifyList()
    {
        $config = get_addon_config('wwh');
        return $config['classify'];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getClassifyTextAttr($value, $data)
    {
        $value = $value ? $value : $data['classify'];
        $valueArr = $value ? explode(',', $value) : [];
        $list = $this->getClassifyList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    public static function getChildrenIds($id, $withself = false)
    {
        static $tree;
        if (!$tree) {
            $tree = \fast\Tree::instance();
            $tree->init(collection(column::order('weigh desc,id desc')->field('id,parent_id,name,type,classify,diyname,status')->select())->toArray(), 'parent_id');
        }
        $childIds = $tree->getChildrenIds($id, $withself);
        return $childIds;
    }

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
        self::beforeInsert(function ($row) {
            $parent_ids = 0;
            if ($row->parent_id != 0) {
                $parent_ids = self::getParentIds($row->parent_id);
                $parent_ids .= "," . $row->parent_id;
            }
            $row->parent_ids = $parent_ids;
        });
        self::beforeUpdate(function ($row) {
            if ($row['parent_id']) {
                $childrenIds = self::getChildrenIds($row['id'], true);
                if (in_array($row['parent_id'], $childrenIds)) {
                    throw new Exception("上级栏目不能是其自身或子栏目");
                }
            }
            $changeData = $row->getChangedData();
            if (isset($changeData['parent_id'])) {
                $row->parent_ids = self::getParentIds($row->parent_id) . ',' . $row->parent_id;
            }
        });
        self::beforeWrite(function ($row) {
            //在更新之前对数组进行处理
            foreach ($row->getData() as $k => $value) {
                if (is_array($value) && is_array(reset($value))) {
                    $value = json_encode(self::getArrayData($value), JSON_UNESCAPED_UNICODE);
                } else {
                    $value = is_array($value) ? implode(',', $value) : $value;
                }
                $row->setAttr($k, $value);
            }
        });
    }

    public static function getParentIds($parent_id) {
        $row = self::get($parent_id);
        if ( !$row ) {
            return 0;
        }
        return $row->parent_ids;
    }

    public function template()
    {
        return $this->belongsTo('app\admin\model\wwh\Template', 'template_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}

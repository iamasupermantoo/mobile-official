<?php

namespace app\admin\model\wwh;

use think\Db;
use think\Model;

class Archives extends Model
{

    // 表名
    protected $name = 'wwh_archives';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'rec_data_text',
        'status_text',
        'url',
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $column = Column::get($row['column_id']);
            $nav = Db::name('wwh_column')->where('id', $row['column_id'])->find();
            $tpl = $nav['showtpl'];
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk], 'classify' => $column['classify'], 'tpl'=>$tpl]);
        });
        self::beforeInsert(function ($row) {
            $column_ids = 0;
            if ($row->column_id != 0) {
                $column_ids = self::getParentIds($row->column_id);
                $column_ids .= "," . $row->column_id;
            }
            $row->column_ids = $column_ids;
        });
        self::beforeUpdate(function ($row) {
            $pk = $row->getPk();
            $changeData = $row->getChangedData();
            if (isset($changeData['column_id'])) {
                $row->column_ids = self::getParentIds($row->column_id) . ',' . $row->column_id;
            }
            $column = Column::get($row['column_id']);
            $nav = Db::name('wwh_column')->where('id', $row['column_id'])->find();
            $tpl = $nav['showtpl'];
            $row->getQuery()->where($pk, $row[$pk])->update(['classify' => $column['classify'], 'tpl'=>$tpl]);
        });
    }

    public static function getParentIds($parent_id) {
        $row = Column::get($parent_id);
        if ( !$row ) {
            return 0;
        }
        return $row->parent_ids;
    }

    public static function getTypeList()
    {
        return ['product' => __('Product'), 'cases' => __('Cases'), 'download' => __('Download'), 'news' => __('News')];
    }

    public function getUrlAttr($value, $data)
    {
        return $this->buildUrl($value, $data);
    }

    private function buildUrl($value, $data)
    {
        $column = Db::name('wwh_column')->where('id', $data['column_id'])->find();
        if ($data['classify'] == 'download'){
            return addon_url('wwh/index/column', [':diyname'=>'download'],'html');
        }else{
            return addon_url('wwh/index/archives', [':diyname' => $column['diyname'], ':id' => $data['id']],'html');
        }

    }

    public function getRecDataList()
    {
        return ['0' => __('Rec_data 0'), '1' => __('Rec_data 1'), '10' => __('Rec_data 10')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getRecDataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['rec_data']) ? $data['rec_data'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getRecDataList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setRecDataAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function column()
    {
        return $this->belongsTo('app\admin\model\wwh\Column', 'column_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}

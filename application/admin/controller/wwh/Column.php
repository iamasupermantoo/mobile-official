<?php

namespace app\admin\controller\wwh;

use app\common\controller\Backend;
use fast\Tree;


/**
 * 栏目管理
 */
class Column extends Backend
{
    protected $columnList = [];
    protected $templateList = [];
    protected $multiFields = ['weigh', 'isnav'];
    protected $searchFields = 'name';

    /**
     * Column模型对象
     * @var \app\admin\model\wwh\column
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\wwh\Column;
        $this->tree = Tree::instance();
        $this->tree->init(collection($this->model->order('weigh desc,id desc')->select())->toArray(), 'parent_id');
        $this->columnList = $this->tree->getTreeList($this->tree->getTreeArray(0), 'name');
        $this->templateList = \app\admin\model\wwh\Template::order('id asc')->select();

        $this->view->assign("templateList", $this->templateList);
        $this->view->assign("columnList", $this->columnList);
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("classifyList", $this->model->getClassifyList());
        $this->assignconfig("classifyList", $this->model->getClassifyList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $search = $this->request->request("search");
            $template_id = $this->request->request("template_id");
            //构造父类select列表选项数据
            $list = [];
            if ($search) {
                foreach ($this->columnList as $k => $v) {
                    if (stripos($v['name'], $search) !== false) {
                        $list[] = $v;
                    }
                }
            } else {
                $list = $this->columnList;
            }
            foreach ($list as $index => $item) {
                if ($template_id && $template_id != $item['template_id']) {
                    unset($list[$index]);
                }
            }
            $list = array_values($list);
            $modelNameArr = [];
            foreach ($this->templateList as $k => $v) {
                $modelNameArr[$v['id']] = $v['name'];
            }
            foreach ($list as $k => &$v) {
                $v['template_name'] = $v['template_id'] && isset($modelNameArr[$v['template_id']]) ? $modelNameArr[$v['template_id']] : __('None');
            }
            $total = count($list);
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $column = \app\admin\model\wwh\Column::get($ids);
        if (!$column) {
            $this->error(__('No Results were found'));
        }
        $column = $column->toArray();

        $hasArchives = model('\app\admin\model\wwh\Archives')->where('column_id', $column['id'])->whereOr('FIND_IN_SET(:id, `column_ids`)', ['id' => $column['id']])->count();
        $this->view->assign('hasArchives', $hasArchives);
        $this->view->assign('values', $column);

        return parent::edit($ids);
    }
	
	/**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $pk = $this->model->getPk();
            $wwh = model('\app\admin\model\wwh\Column')->where('parent_id', 'in', $ids)->select();
            if ($wwh) {
                $this->error('存在下级栏目，无法删除!');
            }
            $res = model('\app\admin\model\wwh\Column')->alias('a')->join('wwh_archives w', 'a.id = w.column_id')->where('a.id', 'in', $ids)->select();
            if ($res) {
                $this->error('栏目存在内容，无法删除!');
            }
            $count = model('\app\admin\model\wwh\Column')->where($pk, 'in', $ids)->delete();
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
    }

}

<?php

namespace app\admin\controller\wwh;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;

class Archives extends Backend
{

    /**
     * Archives模型对象
     * @var \app\admin\model\wwh\Archives
     */
    protected $model = null;
    protected $searchFields = 'title';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\wwh\Archives;
        $columnList = [];
        $disabledIds = [];
        $all = collection(model('\app\admin\model\wwh\Column')->order('weigh desc,id desc')->select())->toArray();
        foreach ($all as $k => $v) {
            $state = ['opened' => true];
            if (($v['classify'] == 'none') or ($v['classify'] == 'service') or ($v['classify'] == 'partner') or ($v['classify'] == 'about')) {
                $disabledIds[] = $v['id'];
            }
            if (($v['classify'] == 'none') or ($v['classify'] == 'service') or ($v['classify'] == 'partner') or ($v['classify'] == 'about')) {
                $state['disabled'] = true;
            }
            $columnList[] = [
                'id'     => $v['id'],
                'parent' => $v['parent_id'] ? $v['parent_id'] : '#',
                'text'   => __($v['name']),
                'type'   => $v['type'],
                'state'  => $state
            ];
        }
        $tree = Tree::instance()->init($all, 'parent_id');
        $channelOptions = $tree->getTree(0, "<option model='@model_id' value=@id @selected @disabled>@spacer@name</option>", '', $disabledIds);
        $this->view->assign('channelOptions', $channelOptions);
        $this->assignconfig('columnList', $columnList);
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("recDataList", $this->model->getRecDataList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->with(['column'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {

                $row->getRelation('column')->visible(['name']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            return parent::edit($ids);
        }
        $column = \app\admin\model\wwh\Column::get($row['column_id']);
        if (!$column) {
            $this->error(__('No specified column found'));
        }
        $disabledIds = [];
        $all = collection(model('\app\admin\model\wwh\Column')->order('weigh desc,id desc')->select())->toArray();
        foreach ($all as $k => $v) {
            if ($v['type'] == 'link' || $v['classify'] != $column['classify']) {
                $disabledIds[] = $v['id'];
            }
        }
        $disabledIds = array_diff($disabledIds, [$row['column_id']]);
        $tree = Tree::instance()->init($all, 'parent_id');
        $channelOptions = $tree->getTree(0, "<option model='@model_id' value=@id @selected @disabled>@spacer@name</option>", $row['column_id'], $disabledIds);
        $this->view->assign('channelOptions', $channelOptions);
        $this->view->assign("row", $row);
        return $this->view->fetch();
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
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $liststatus = $this->model->where(['status'=>'1','id'=>$ids])->limit(1)->select();
                    if ($liststatus) {
                        $this->error('该文章已审核，不能删除！');
                    } else {
                        $count += $v->delete();
                    }
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 审核
     */
    public function audit($ids)
    {
        foreach ($ids as $k => $v){
            $res = $this->model->where(['id' => ['in', $ids]])->update(['status' => 1]);
            if ($res == true) {
                $this->success('审核成功');
            } else {
                $this->error('未更新任何行');
            }
        }
    }

    /**
     * 反审核
     */
    public function faudit($ids)
    {
        $res = $this->model->where(['status'=>'1','id'=>$ids])->update(['status' => 0]);
        if ($res) {
            $this->success('反审核成功');
        } else {
            $this->error('未更新任何行');
        }
    }

}

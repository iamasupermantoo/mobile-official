<?php

namespace app\admin\controller\wwh;

use app\common\controller\Backend;
use think\Db;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Config extends Backend
{
    /**
     * 查看
     */
    public function index()
    {
        $data = Db::name('wwh_config')->where('id', 1)->find();
        $this->assign('data', $data);
        return $this->view->fetch();
    }
    
    
    /**
     * 站点设置修改
     */
    public function configedit()
    {
        $s=[
            'site_name'=>input('site_name'),
            'keywords'=>input('keywords'),
            'description'=>input('description'),
			'logo'=>input('logo'),
            'logo1'=>input('logo1'),
            'footer_logo'=>input('footer_logo'),
            'email'=>input('email'),
            'tel'=>input('tel'),
            'gongwang'=>input('gongwang'),
            'link1'=>input('link1'),
            'beian'=>input('beian'),
            'link2'=>input('link2'),
            'copyright'=>input('copyright'),
            'weibo'=>input('weibo'),
            'wechat'=>input('wechat'),
            'douyin'=>input('douyin'),
        ];
        $test = Db::name('wwh_config')->where('id', 1)->find();
        if (empty($test)) {
            $data = Db::name('wwh_config')->insert($s);
        } else {
            $data = Db::name('wwh_config')->where('id', 1)->setField($s);
        }
        if ($data) {
            $this->success('保存成功');
        } else {
            $this->error('未检测到数据变动');
        }
    }


    /**
     * 栏目Banner修改
     */
    public function banneredit()
    {
        $s=[
            'banner1'=>input('banner1'),
            'banner2'=>input('banner2'),
            'banner3'=>input('banner3'),
            'banner4'=>input('banner4'),
            'banner5'=>input('banner5'),
            'banner6'=>input('banner6'),
            'ban1_t1'=>input('ban1_t1'),
            'ban1_t2'=>input('ban1_t2'),
            'ban2_t1'=>input('ban2_t1'),
            'ban2_t2'=>input('ban2_t2'),
            'ban3_t1'=>input('ban3_t1'),
            'ban3_t2'=>input('ban3_t2'),
            'ban4_t1'=>input('ban4_t1'),
            'ban4_t2'=>input('ban4_t2'),
            'ban5_t1'=>input('ban5_t1'),
            'ban5_t2'=>input('ban5_t2'),
            'ban6_t1'=>input('ban6_t1'),
            'ban6_t2'=>input('ban6_t2'),
        ];
        $test = Db::name('wwh_config')->where('id', 1)->find();
        if (empty($test)) {
            $data = Db::name('wwh_config')->insert($s);
        } else {
            $data = Db::name('wwh_config')->where('id', 1)->setField($s);
        }
        if ($data) {
            $this->success('保存成功');
        } else {
            $this->error('未检测到数据变动');
        }
    }


    /**
     * 底部链接修改
     */
    public function footeredit()
    {
        $s=[
            'content'=>input('content'),
        ];
        $test = Db::name('wwh_config')->where('id', 1)->find();
        if (empty($test)) {
            $data = Db::name('wwh_config')->insert($s);
        } else {
            $data = Db::name('wwh_config')->where('id', 1)->setField($s);
        }
        if ($data) {
            $this->success('保存成功');
        } else {
            $this->error('未检测到数据变动');
        }
    }

    /**
     * 地图配置修改
     */
    public function mapedit()
    {
        $s=[
            'map_zb'=>input('map_zb'),
            'map_dzq'=>input('map_dzq'),
            'map_mc'=>input('map_mc'),
            'map_dz'=>input('map_dz'),
        ];
        $test = Db::name('wwh_config')->where('id', 1)->find();
        if (empty($test)) {
            $data = Db::name('wwh_config')->insert($s);
        } else {
            $data = Db::name('wwh_config')->where('id', 1)->setField($s);
        }
        if ($data) {
            $this->success('保存成功');
        } else {
            $this->error('未检测到数据变动');
        }
    }

}

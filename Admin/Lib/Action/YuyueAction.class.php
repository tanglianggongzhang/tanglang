<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of YuyueAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class YuyueAction extends CommonAction {

    /**
     * 预约列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $Mod = D("YuyueView");
        import("ORG.Util.Page");
        $where = "Yuyue.ytype=1";
        $citymod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            $where.=" and Yuyue.p_id=" . $_SESSION['my_info']['proid'];
            $where.=" and Yuyue.c_id=" . $_SESSION['my_info']['cityid'];
        } else {
            $p_list = $citymod->getprovince(1);
            $this->assign("p_list", $p_list);
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            if (!empty($p_id))
                $where.=" and Yuyue.p_id=" . $p_id;
            if (!empty($c_id))
                $where.=" and Yuyue.c_id=" . $c_id;
            $this->assign("c_id", $c_id);
            $this->assign("c_name", $citymod->getname($c_id));
            $this->assign("p_id", $p_id);
            
        }
        $keys=$_GET['keys'];
        $keys=$keys=="请输入关键字"?"":$keys;
        if(!empty($keys))
            $where.=" and Yuyue.name like '%".$keys."%'";
        $this->assign("keys",$keys);
        
        $totalRows = $Mod->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $Mod->where($where)->order("Yuyue.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        $arr = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['status_f'] = $arr[$v['status']];
        }
        $this->assign("list", $list);

        $this->assign("page", $p->show());

        $this->display();
    }

    /**
     * 设计师 预约列表
     */
    public function gzyy() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $Mod = D("YuyueView");
        import("ORG.Util.Page");
        $where = "Yuyue.ytype=2";
        $citymod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            $where.=" and Yuyue.p_id=" . $_SESSION['my_info']['proid'];
            $where.=" and Yuyue.c_id=" . $_SESSION['my_info']['cityid'];
        } else {
            $p_list = $citymod->getprovince(1);
            $this->assign("p_list", $p_list);
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            if (!empty($p_id))
                $where.=" and Yuyue.p_id=" . $p_id;
            if (!empty($c_id))
                $where.=" and Yuyue.c_id=" . $c_id;
            $this->assign("c_id", $c_id);
            $this->assign("c_name", $citymod->getname($c_id));
            $this->assign("p_id", $p_id);
            
        }
        $keys=$_GET['keys'];
        $keys=$keys=="请输入关键字"?"":$keys;
        if(!empty($keys))
            $where.=" and Yuyue.name like '%".$keys."%'";
        $this->assign("keys",$keys);
        
        $totalRows = $Mod->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $Mod->where($where)->order("Yuyue.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        
        $arr = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['status_f'] = $arr[$v['status']];
        }
        $this->assign("list", $list);

        $this->assign("page", $p->show());

        $this->display();
    }

    /**
     * 删除预约
     */
    public function del_yuyue() {

        $id = $_GET['id'];
        $M = M("Yuyue");
        $rs = $M->where("id=" . $id)->delete();
        if ($_GET['is_gz'] == 1)
            $link = U("Yuyue/index");
        else
            $link = U("Yuyue/gzyy");

        if ($rs)
            $this->success("操作成功！", $link);
        else
            $this->error("操作失败！");
    }

    /**
     * 快速修改状态
     */
    public function edit_status() {
        $status = $_GET['status'];
        if ($status == 1) {
            $status_u = 0;
        } else {
            $status_u = 1;
        }
        $id = $_GET['id'];
        $M = M("Yuyue");
        $rs = $M->where("id=" . $id)->save(array("status" => $status_u));
        if ($_GET['is_gz'] == 1)
            $link = U("Yuyue/index");
        else
            $link = U("Yuyue/gzyy");

        if ($rs)
            $this->success("操作成功！", $link);
        else
            $this->error("操作失败！");
    }

    /**
     * 预约类型
     */
    public function ytype() {
        if (IS_POST) {
            $name = trim($_POST['name']);
            $ymod = M("Yuyuetype");
            if (empty($name)) {
                $this->error("类型名称不能为空!");
                exit;
            }
            $cou = $ymod->where("name = '" . $name . "'")->count();
            if ($cou > 0) {
                $this->error("预约类型名称已经存在！");
                exit;
            }
            $data['name'] = $name;
            $rs = $ymod->add($data);
            if ($rs)
                $this->success("操作成功!");
            else
                $this->error("操作失败!");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $ymod = M("Yuyuetype");
        $ylist = $ymod->where(1)->select();
        $this->assign("ylist", $ylist);

        $this->display();
    }

    /**
     * 删除预约类型
     */
    public function del_ytype() {
        $id = $_GET['id'];
        $ymod = M("Yuyuetype");
        $rs = $ymod->where("yid=" . $id)->delete();
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败！");
    }

    //---------------------ajax
    /**
     * 修改预约类型
     */
    public function ajax_updatename() {
        header('Content-Type:application/json; charset=utf-8');
        $id = $_POST['id'];
        $sort = $_POST['sort'];
        $M = M("Yuyuetype");
        $rs = $M->where("yid=" . $id)->save(array("name" => $sort));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

}

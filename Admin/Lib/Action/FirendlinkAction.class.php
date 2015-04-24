<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FirendlinkAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class FirendlinkAction extends CommonAction {

    /**
     * 友情链接列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $hxmod = D("FirendlinkView");
        import("ORG.Util.Page");
        $keys = trim($_GET['keys']);
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $where = "1";
        if (!empty($keys))
            $where .= " and Firendlink.name like '%" . $keys . "%'";
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $this->assign("is_qx", $is_qx);

        if ($is_qx == 0) {
            //非地区管理员
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            //省
            $mcity = new CityModel();
            $plist = $mcity->getprovince(1);
            $this->assign("plist", $plist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
            $this->assign("c_name", $mcity->getname($c_id));
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
        }
        if (!empty($p_id))
            $where .= " and Firendlink.p_id = '" . $p_id . "'";

        if (!empty($c_id))
            $where .= " and Firendlink.c_id = '" . $c_id . "'";

        $totalRows = $hxmod->where($where)->order("Firendlink.orders desc")->count();
        $page = new Page($totalRows, 10);
        $list = $hxmod->where($where)->order("Firendlink.orders desc")->limit($page->firstRow . "," . $page->listRows)->select();

        $showpage = $page->show();
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i:s", $v['addtime']);
        }

        $this->assign("list", $list);


        $this->assign("page", $showpage);
        $this->assign("keys", $keys);
        $this->assign("keys", $keys);


        $this->display();
    }

    /**
     * 添加友情链接
     */
    public function addfriendlink() {
        $a_id = ($_SESSION['my_info']['a_id']);
        if (IS_POST) {
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            if ($this->getqx($_SESSION['my_info']['role']) == 0) {
                //非地区管理员
                if (empty($p_id)) {
                    $this->error("请选择省");
                    exit;
                }
                if (empty($c_id)) {
                    $this->error("请选择市");
                    exit;
                }
            } else {
                $p_id = $_SESSION['my_info']['proid'];
                $c_id = $_SESSION['my_info']['cityid'];
                if (empty($p_id)) {
                    $this->error("请选择省");
                    exit;
                }
                if (empty($c_id)) {
                    $this->error("请选择市");
                    exit;
                }
            }
            $name = $_POST['name'];
            $name = trim($name);
            if (empty($name)) {
                $this->error("请填写名称");
                exit;
            }
            //上传图片

            $imginfo = $this->upload('./Uploads/fl/');
            if (!empty($imginfo[0]['savename']))
                $logo = "/Uploads/fl/" . $imginfo[0]['savename'];
            else
                $logo = "";

            $order = trim($_POST['orders']);
            $order = empty($order) ? 0 : $order;
            $is_tj = $_POST['is_tj'];
            $link = trim($_POST['link']);
            # var_dump($imginfo);exit;
            $m = M("Firendlink");


            $data = array(
                "name" => $name,
                "logo" => $logo,
                "link" => $link,
                "orders" => $order,
                "is_tj" => $is_tj,
                "p_id" => $p_id,
                "c_id" => $c_id,
                "adduid" => $a_id,
                "addtime" => time()
            );
            $res = $m->add($data);


            if ($res) {
                $this->success("添加成功", U("Firendlink/index"));
            } else {
                $this->error("添加失败");
            }
            exit;
        }
        #读取省
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            $mcity = new CityModel();
            $plist = $mcity->getprovince(1);
            $this->assign("plist", $plist);
        }
        $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->display();
    }

    /**
     * 编辑友情链接
     */
    public function editfirendlink() {
        $m = M("Firendlink");
        if (IS_POST) {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            if (empty($name)) {
                $this->error("名称不能为空！");
                exit;
            }

            if ($_FILES['logo']['name'] != '') {
                $imginfo = $this->upload('./Uploads/fl/');
                if (!empty($imginfo[0]['savename']))
                    $logo = "/Uploads/fl/" . $imginfo[0]['savename'];
                else
                    $logo = "";
            }
            $link = trim($_POST['link']);
            $orders = trim($_POST['orders']);
            $is_tj = $_POST['is_tj'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];

            $data = array();
            if (!empty($name))
                $data['name'] = $name;
            if (!empty($logo))
                $data['logo'] = $logo;
            if (!empty($link))
                $data['link'] = $link;
            if (!empty($orders))
                $data['orders'] = $orders;
            if (!empty($is_tj))
                $data['is_tj'] = $is_tj;
            if (!empty($p_id))
                $data['p_id'] = $p_id;
            if (!empty($c_id))
                $data['c_id'] = $c_id;

            $res = $m->where("id=" . $id)->save($data);
            if ($res)
                $this->success("操作成功！", U("Firendlink/index"));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];

        $info = $m->where("id=" . $id)->find();

        $this->assign("info", $info);
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            #读取省
            $mcity = new CityModel();
            $plist = $mcity->getprovince(1);
            $this->assign("plist", $plist);
            $this->assign("c_id", $info['c_id']);
            $this->assign("c_name", $mcity->getname($info['c_id']));
        }
        $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));
        
        $this->display("addfriendlink");
    }

    /**
     * 删除
     */
    public function del() {
        $id = $_GET['id'];
        $m = M("Firendlink");
        $info = $m->where("id=" . $id)->find();
        if (file_exists($info['logo']))
            unlink($info['logo']);
        $res = $m->where("id=" . $id)->delete();

        if ($res)
            $this->success("操作成功！", U("Firendlink/index"));
        else
            $this->error("操作失败！");
    }

}

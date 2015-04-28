<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShitiAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShitiAction extends CommonAction {

    /**
     * 实体店列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        @import("ORG.Util.Page");
        $smod = D("ShitiView");
        $where = "1";
        $cmod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            //地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $q_id = $_GET['q_id'];
            #区
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("is_qx", 1);
        } else {
            //#省
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
            $this->assign("is_qx", 0);
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            $q_id = $_GET['q_id'];
        }
        if (!empty($p_id))
            $where.=" and p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and c_id=" . $c_id;
        if (!empty($q_id))
            $where.=" and q_id=" . $q_id;
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("c_name", $cmod->getname($c_id));
        $this->assign("q_id", $q_id);
        $this->assign("q_name", $cmod->getname($q_id));
        $keys = trim($_GET['keys']);
        $keys = $keys == "请输入关键字" ? "" : $keys;
        if (!empty($keys))
            $where.="name like '%" . $keys . "%'";
        $this->assign("keys", $keys);
        $totalRows = $smod->where($where)->count();
        $page = new Page($totalRows, 10);
        $list = $smod->where($where)->order("addtime desc")->limit($page->firstRow . "," . $page->listRows)->select();
        $xs = array(0 => "隐藏", 1 => "显示");
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i:s", $v['addtime']);
            $list[$k]['isdis'] = $xs[$v['is_display']];
            $list[$k]['link'] = $this->systemConfig['SITE_INFO']['url'] . "index.php/Shiti/show/id/" . $v['id'];
        }
        $this->assign("list", $list);

        $this->assign("page", $page->show());
        $this->display();
    }

    /**
     * 添加实体店
     */
    public function addst() {
        if (IS_POST) {
            $name = trim($_POST['name']);
            $telphone = trim($_POST['telphone']);
            $address = trim($_POST['address']);
            $is_display = trim($_POST['is_display']);
            $p_id = trim($_POST['p_id']);

            $c_id = trim($_POST['c_id']);
            $q_id = trim($_POST['q_id']);
            $yingye = trim($_POST['yingye']);
            $fwzz = trim($_POST['fwzz']);
            $tcw = trim($_POST['tcw']);
            $mianji = trim($_POST['mianji']);
            if (empty($name)) {
                $this->error("请填写实体店名称！");
                exit;
            }
            if (empty($telphone)) {
                $this->error("请填写实体店电话！");
                exit;
            }
            if (empty($address)) {
                $this->error("请填写实体店地址！");
                exit;
            }
            if (empty($p_id)) {
                $this->error("请选择省！");
                exit;
            }
            if (empty($c_id)) {
                $this->error("请选择市！");
                exit;
            }
            if (empty($q_id)) {
                $this->error("请选择区！");
                exit;
            }
            if (empty($yingye)) {
                $this->error("请填写营业时间!");
                exit;
            }
            if (empty($fwzz)) {
                $this->error("请填写服务宗旨!");
                exit;
            }

            if (empty($tcw)) {
                $this->error("请填写停车位数目!");
                exit;
            }
            if (empty($mianji)) {
                $this->error("请填写展厅面积!");
                exit;
            }



            if (!empty($_FILES['mapimg']['name']) && !empty($_FILES['zwimg']['name'])) {
                //地图图片、周围图片 都有
                $imginfo = $this->upload("./Uploads/std/");
                if (empty($imginfo[0]['savename']))
                    $mapimg = "";
                else
                    $mapimg = "/Uploads/std/" . $imginfo[0]['savename'];
                if (empty($imginfo[1]['savename']))
                    $zwimg = "";
                else
                    $zwimg = "/Uploads/std/" . $imginfo[1]['savename'];
            }
            elseif (!empty($_FILES['mapimg']['name']) && empty($_FILES['zwimg']['name'])) {
                $imginfo = $this->upload("./Uploads/std/");
                if (empty($imginfo[0]['savename']))
                    $mapimg = "";
                else
                    $mapimg = "/Uploads/std/" . $imginfo[0]['savename'];
            }
            elseif (empty($_FILES['mapimg']['name']) && !empty($_FILES['zwimg']['name'])) {
                $imginfo = $this->upload("./Uploads/std/");
                if (empty($imginfo[0]['savename']))
                    $zwimg = "";
                else
                    $zwimg = "/Uploads/std/" . $imginfo[0]['savename'];
            }else {
                $mapimg = "";
                $zwimg = "";
            }
            $data = array();
            if (!empty($name))
                $data['name'] = $name;
            if (!empty($telphone))
                $data['telphone'] = $telphone;
            if (!empty($address))
                $data['address'] = $address;
            if (!empty($p_id))
                $data['p_id'] = $p_id;
            if (!empty($c_id))
                $data['c_id'] = $c_id;
            if (!empty($q_id))
                $data['q_id'] = $q_id;
            if (!empty($yingye))
                $data['yingye'] = $yingye;
            if (!empty($fwzz))
                $data['fwzz'] = $fwzz;
            if (!empty($tcw))
                $data['tcw'] = $tcw;
            if (!empty($mianji))
                $data['mianji'] = $mianji;
            if (!empty($mapimg))
                $data['mapimg'] = $mapimg;
            if (!empty($zwimg))
                $data['zwimg'] = $zwimg;
            $data['addtime'] = time();
            $m = M("Shiti");
            $res = $m->add($data);
            if ($res)
                $this->success("操作成功!", U("Shiti/index"));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        #省 市 
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            #地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            #获取区列表
            $cmod = new CityModel();
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("c_id", $c_id);
            $this->assign("p_id", $p_id);
            $this->assign("is_qx", 1);
        } else {
            #获取省份列表
            $cmod = new CityModel();
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
            $this->assign("is_qx", 0);
        }

        $this->display();
    }

    /**
     * 编辑实体店
     */
    public function editst() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = D("ShitiView");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        if (IS_POST) {
            $name = trim($_POST['name']);
            $telphone = trim($_POST['telphone']);
            $address = trim($_POST['address']);
            $is_display = trim($_POST['is_display']);
            $p_id = trim($_POST['p_id']);
            $c_id = trim($_POST['c_id']);
            $q_id = trim($_POST['q_id']);
            $yingye = trim($_POST['yingye']);
            $fwzz = trim($_POST['fwzz']);
            $tcw = trim($_POST['tcw']);
            $mianji = trim($_POST['mianji']);
            if (!empty($_FILES['mapimg']['name']) && !empty($_FILES['zwimg']['name'])) {
                //地图图片、周围图片 都有
                $imginfo = $this->upload("./Uploads/std/");
                if (empty($imginfo[0]['savename'])) {
                    $mapimg = "";
                } else {
                    $mapimg = "/Uploads/std/" . $imginfo[0]['savename'];
                    unlink(".".$info["mapimg"]);
                }
                if (empty($imginfo[1]['savename'])) {
                    $zwimg = "";
                } else {
                    $zwimg = "/Uploads/std/" . $imginfo[1]['savename'];
                    unlink(".".$info['zwimg']);
                }
            } elseif (!empty($_FILES['mapimg']['name']) && empty($_FILES['zwimg']['name'])) {
                $imginfo = $this->upload("./Uploads/std/");
                if (empty($imginfo[0]['savename'])) {
                    $mapimg = "";
                } else {
                    $mapimg = "/Uploads/std/" . $imginfo[0]['savename'];
                    unlink(".".$info["mapimg"]);
                }
            } elseif (empty($_FILES['mapimg']['name']) && !empty($_FILES['zwimg']['name'])) {
                $imginfo = $this->upload("./Uploads/std/");
                if (empty($imginfo[0]['savename'])) {
                    $zwimg = "";
                } else {
                    $zwimg = "/Uploads/std/" . $imginfo[0]['savename'];
                    unlink(".".$info['zwimg']);
                }
            } else {
                $mapimg = "";
                $zwimg = "";
            }
            $id = $_POST['id'];
            $data = array();
            if (!empty($name) && $name != $info['name']) {
                $data['name'] = $name;
            }
            if (!empty($telphone) && $telphone != $info['telphone']) {
                $data['telphone'] = $telphone;
            }
            if (!empty($address) && $address != $info['address']) {
                $data['address'] = $address;
            }
            if (!empty($is_display) && $is_display != $info['is_display']) {
                $data['is_display'] = $is_display;
            }
            if (!empty($p_id) && $p_id != $info['p_id']) {
                $data['p_id'] = $p_id;
            }
            if (!empty($c_id) && $c_id != $info['c_id']) {
                $data['c_id'] = $c_id;
            }
            if (!empty($q_id) && $q_id != $info['q_id']) {
                $data['q_id'] = $q_id;
            }
            if (!empty($yingye) && $yingye != $info['yingye']) {
                $data['yingye'] = $yingye;
            }
            if (!empty($fwzz) && $fwzz != $info['fwzz']) {
                $data['fwzz'] = $fwzz;
            }
            if (!empty($tcw) && $tcw != $info['tcw']) {
                $data['tcw'] = $tcw;
            }
            if (!empty($mianji) && $mianji != $info['mianji']) {
                $data['mianji'] = $mianji;
            }
            if (!empty($mapimg))
                $data['mapimg'] = $mapimg;
            if (!empty($zwimg))
                $data['zwimg'] = $zwimg;
            $Mo = M("Shiti");
            $data['addtime'] = time();
            $res = $Mo->where("id=" . $id)->save($data);
            if ($res)
                $this->success("操作成功!", U("Shiti/index"));
            else
                $this->error("操作失败!");
            exit;
        }
        $cmod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            #地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
            $this->assign("is_qx", 1);
        } else {
            #省
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
            $this->assign("is_qx", 0);
        }
        $this->assign("c_name", $cmod->getname($info['c_id']));
        $this->assign("q_name", $cmod->getname($info['q_id']));

        $this->display("addst");
    }

    /**
     * 删除实体店
     */
    public function del() {
        $id = $_GET['id'];
        $m = M("Shiti");
        $info = $m->where("id=" . $id)->find();
        if (!empty($info['mapimg']))
            unlink(".".$info['mapimg']);
        if (!empty($info['zwimg']))
            unlink(".".$info['zwimg']);

        $res = $m->where("id=" . $id)->delete();
        if ($res)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 修改状态
     * 实体店
     */
    public function upstate() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 0)
            $up = 1;
        if ($status == 1)
            $up = 0;
        $m = M("Shiti");
        $res = $m->where("id=" . $id)->save(array("is_display" => $up));
        if ($res) {
            $this->success("修改成功！");
        } else {
            $this->error("操作失败!");
        }
    }

    /**
     * 实体店申请记录
     */
    public function shenqing() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $stV = D("ShitijlView");
        $where = "1";

        $cmod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            //地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("is_qx", 1);
        } else {
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            #省
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
            $this->assign("is_qx", 0);
        }
        $q_id = $_GET['q_id'];
        $keys = $_GET['keys'] == "请输入关键字" ? "" : $keys;
        $stid = $_GET['stid'];
        #实体店
        $stmod = M("Shiti");
        $stlist = $stmod->field("id,name")->where("1")->order("addtime desc")->select();
        $this->assign("stlist", $stlist);
        #实体列表-----------------start
        if (!empty($p_id))
            $where.=" and Shiti.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and Shiti.c_id=" . $c_id;
        if (!empty($q_id))
            $where.=" and Shiti.q_id=" . $q_id;
        if (!empty($keys))
            $where.=" and Shitijl.name like '%" . $keys . "%'";
        if (!empty($stid))
            $where.=" and Shitijl.stid=" . $stid;

        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("q_id", $q_id);
        $this->assign("keys", $keys);
        $this->assign("stid", $stid);
        $totle = $stV->where($where)->count();
        @import("ORG.Util.Page");
        $page = new Page($totle, 10);

        $list = $stV->where($where)->order("Shitijl.addtime desc")->limit($page->firstRow . "," . $page->listRows)->select();

        $this->assign("list", $list);
        $this->assign("page", $page->show());
        #实体列表-----------------end
        $this->display();
    }

    /**
     * 删除申请记录
     */
    public function del_sq() {
        $id = $_GET['id'];
        $m = M("Shitijl");
        $res = $m->where("id=" . $id)->delete();
        if ($res)
            $this->success("操作成功!");
        else
            $this->error("操作失败!");
    }

    /**
     * 添加
     * 实体店幻灯片
     */
    public function addsthdp() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $stmod = M("Shiti");

        if (IS_POST) {
            $stid = $_POST['stid'];
            $link = trim($_POST['link']);
            $is_display = $_POST['is_display'];
            if (empty($stid)) {
                $this->error("请选择实体店！");
                exit;
            }
            $imginfo = $this->upload("./Uploads/std/");
            if (!empty($imginfo[0]['savename']))
                $img = "/Uploads/std/" . $imginfo[0]['savename'];
            else
                $img = "";
            $data = array(
                "stid" => $stid,
                "img" => $img,
                "link" => $link,
                "addtime" => time(),
                "is_display" => $is_display
            );
            $mod = M("Shitihdp");
            $res = $mod->add($data);
            if ($res) {
                $this->success("操作成功!", U("Shiti/liststhdp"));
            } else {
                $this->error("操作失败!");
            }
            exit;
        }

        $stlist = $stmod->field("id,name")->where("1")->order("addtime desc")->select();
        $this->assign("stlist", $stlist);

        $this->display();
    }

    /**
     * 列表
     * 实体店幻灯片
     */
    public function liststhdp() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $hdM = D("ShitihdpView");
        @import("ORG.Util.Page");

        $where = "1";
        $cmod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            //地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $cmod->getcity($c_id); //区
            $this->assign("qlist", $qlist);
            $this->assign("is_qx", 1);
        } else {
            //非地区管理员
            $plist = $cmod->getcity(1); //省
            $this->assign("plist", $plist);
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            $this->assign("is_qx", 0);
        }
        $q_id = $_GET['q_id'];
        $stid = $_GET['stid'];

        $stlist = M("Shiti")->field("id,name")->order("addtime desc")->select();
        $this->assign("stlist", $stlist);
        if (!empty($p_id))
            $where.=" and Shiti.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and Shiti.c_id=" . $c_id;
        if (!empty($q_id))
            $where.=" and Shiti.q_id=" . $q_id;
        if (!empty($stid))
            $where.=" and Shitihdp.stid=" . $stid;

        $cou = $hdM->where($where)->count();
        $page = new Page($cou, 10);
        $list = $hdM->where($where)->order("Shitihdp.addtime desc")->limit($page->firstRow . "," . $page->listRows)->select();
        $zt = array("0" => "隐藏", "1" => "显示");
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i:s", $v['addtime']);
            $list[$k]['isdis'] = $zt[$v['is_display']];
        }
        $this->assign("list", $list);
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("c_name", $cmod->getname($c_id));
        $this->assign("q_id", $q_id);
        $this->assign("q_name", $cmod->getname($q_id));
        $this->assign("stid", $stid);

        $this->assign("page", $page->show());
        $this->display();
    }

    /**
     * 删除
     * 实体店幻灯片
     */
    public function delsthdp() {
        $id = $_GET['id'];
        $m = M("Shitihdp");
        $info = $m->where("id=" . $id)->find();
        if(!empty($info['img']))
            unlink (".".$info['img']);
        
        $res = $m->where("id=" . $id)->delete();
        if ($res) {
            $this->success("操作成功！");
        } else {
            $this->error("操作失败!");
        }
    }

    /**
     * 编辑
     * 实体店幻灯片
     */
    public function editsthdp() {

        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $hdpmod = D("ShitihdpView");
        $info = $hdpmod->where("Shitihdp.id=" . $id)->find();

        $this->assign("info", $info);
        if (IS_POST) {
            $m = M("Shitihdp");
            $id = $_POST['id'];
            $stid = $_POST['stid'];
            $link = trim($_POST['link']);
            $is_display = $_POST['is_display'];
            if (!empty($_FILES['img']['name'])) {
                $nameinfo = $this->upload("./Uploads/std/");
                if (empty($nameinfo[0]['savename'])) {
                    $img = "";
                } else {
                    $img = "/Uploads/std/" . $nameinfo[0]['savename'];
                    unlink(".".$info['img']);
                }
            }
            $data = array();
            if (!empty($stid) && $stid != $info['stid']) {
                $data['stid'] = $stid;
            }
            if (!empty($img)) {
                $data['img'] = $img;
            }
            if (!empty($link) && $link != $info['link']) {
                $data['link'] = $link;
            }
            $data['addtime'] = time();
            if (!empty($is_display) && $is_display != $info['is_display']) {
                $data['is_display'] = $is_display;
            }
            $rs = $m->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功", U("Shiti/liststhdp"));
            else
                $this->error("操作失败");
            exit;
        }
        $stmod = M("Shiti");
        $stlist = $stmod->where(1)->order("addtime desc")->field("id,name")->select();
        $this->assign("stlist", $stlist);

        $this->display("addsthdp");
    }

    /**
     * 修改状态
     * 实体店幻灯片
     */
    public function upstatuehdp() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 0) {
            $up = 1;
        } else {
            $up = 0;
        }
        $m = M("Shitihdp");
        $res = $m->where("id=" . $id)->save(array("is_display" => $up));
        if ($res) {
            $this->success("操作成功！");
        } else {
            $this->error("操作失败!");
        }
    }

    /**
     * ajax
     * 根据省id，市id,区id
     * 获取实体店
     */
    public function getst() {
        header("Content-Type:application/json; charset=utf-8");
        $p_id = $_GET['p_id'];
        $c_id = $_GET['c_id'];
        $q_id = $_GET['q_id'];
        $where = array();
        if (!empty($p_id))
            $where['p_id'] = $p_id;
        if (!empty($c_id))
            $where['c_id'] = $c_id;
        if (!empty($q_id))
            $where['q_id'] = $q_id;
        $M = M("Shiti");
        $list = $M->where($where)->select();

        echo json_encode($list);
    }

}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GroupAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GroupAction extends CommonAction {

    /**
     * 列表
     * 团购活动
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $citymod = new CityModel();
        if ($is_qx == 1) {
            //是地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            #区
            $qlist = $citymod->getcity($c_id);
            $this->assign("qlist", $qlist);
        } else {
            #省
            $plist = $citymod->getprovince(1);
            $this->assign("plist", $plist);
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
        }
        $q_id = $_GET['q_id'];
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("q_id", $q_id);
        $this->assign("c_name", $citymod->getname($c_id));
        $this->assign("q_name", $citymod->getname($q_id));

        $where = "1";
        if (!empty($p_id))
            $where.=" and Group1.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and  Group1.c_id=" . $c_id;
        if (!empty($q_id))
            $where.=" and  Group1.q_id=" . $q_id;
        if (!empty($keys))
            $where.=" and  Group1.title like '%" . $keys . "%'";
        $this->assign("keys", $keys);
        $M = D("GroupView");
        $cou = $M->where($where)->count();
        $p = new Page($cou, 10);
        $list = $M->where($where)->order(" Group1.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        $arr = array("未审核", "已审核");
        $arr1 = array("普通", "推荐");
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i:s", $v['addtime']);
            $list[$k]['statusf'] = $arr[$v['status']];
            $list[$k]['is_tjf'] = $arr1[$v['is_tj']];
        }
        $this->assign("list", $list);
        $this->assign("page", $p->show());

        $this->display();
    }

    /**
     * 添加
     * 团购活动
     */
    public function add_group() {
        if (IS_POST) {
            $uid = $_POST['gzid'];
            $title = trim($_POST['title']);
            if (empty($title)) {
                $this->error("标题不能为空！");
                exit;
            }
            if ($this->check_title($title)) {
                $this->error("标题已经存在！");
                exit;
            }
            $is_tj = $_POST['is_tj'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $status = $_POST['status'];
            $starthdtime = $_POST['starthdtime'];
            $endhdtime = $_POST['endhdtime'];
            $address = $_POST['address'];
            $shuoming = $_POST['shuoming'];
            $chengchelx = $_POST['chengchelx'];
            $chengchelxs = str_replace("\n", ",", $chengchelx);

            if (!empty($starthdtime))
                $starthdtime = strtotime($starthdtime);
            if (!empty($endhdtime))
                $endhdtime = strtotime($endhdtime);
            if ($starthdtime > $endhdtime) {
                $this->error("开始时间不能大于结束时间");
                exit;
            }

            $imgs = $this->upload();
            foreach ($imgs as $k => $v) {
                if ($v['key'] == "mapimg") {
                    $mapimg = "/Uploads/product/" . $v['savename'];
                } else {
                    $hdimg = "/Uploads/product/" . $v['savename'];
                }
            }
            $bmnum = $_POST['bmnum'];
            $data = array();
            $data['title'] = $title;
            $data['is_tj'] = $is_tj;
            $data['p_id'] = $p_id;
            $data['c_id'] = $c_id;
            $data['q_id'] = $q_id;
            $data['starthdtime'] = $starthdtime;
            $data['endhdtime'] = $endhdtime;
            $data['address'] = $address;
            $data['shuoming'] = $shuoming;
            $data['chengchelx'] = $chengchelxs;
            $data['mapimg'] = $mapimg;
            $data['hdimg'] = $hdimg;
            $data['bmnum'] = $bmnum;
            $data['addtime'] = time();
            $data['adduid'] = $_SESSION['my_info']['a_id'];
            $data['uid'] = $uid;
            $data['status'] = $status;
            $M = M("Group");
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！");
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("sjlist", $this->getgzh());
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $citymod = new CityModel();
        if ($is_qx == 1) {
            #区
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $citymod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
        } else {
            $plist = $citymod->getprovince(1);
            $this->assign("plist", $plist);
        }


        $this->display();
    }

    /**
     * 编辑
     * 团购活动
     */
    public function edit_group() {
        if (IS_POST) {
            $title = trim($_POST['title']);
            $is_tj = $_POST['is_tj'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $status = $_POST['status'];
            $starthdtime = $_POST['starthdtime'];
            $endhdtime = $_POST['endhdtime'];
            $address = trim($_POST['address']);
            $shuoming = trim($_POST['shuoming']);
            $chengchelx = str_replace("\n", ",", $chengchelx);
            $bmnum = trim($_POST['bmnum']);
            $id = $_POST['id'];
            if (!empty($starthdtime)) {
                $starthdtime1 = strtotime($starthdtime);
            }
            if (!empty($endhdtime)) {
                $endhdtime1 = strtotime($endhdtime);
            }
            if ($starthdtime1 > $endhdtime1) {
                $this->error("开始时间不能大于结束时间！");
                exit;
            }

            $M = D("GroupView");
            $info1 = $M->where("id=" . $id)->find();

            $img = $this->upload();
            if (!empty($img)) {
                foreach ($img as $k => $v) {
                    if ($v['key'] == "mapimg") {
                        unlink("." . $info1['mapimg']);
                        $mapimg = "/Uploads/product/" . $v['savename'];
                    } else {
                        unlink("." . $info1['hdimg']);
                        $hdimg = "/Uploads/product/" . $v['savename'];
                    }
                }
            }


            $data = array();
            if (!empty($title) && $title != $info1['title'])
                $data['title'] = $title;
            if ($is_tj != $info1['is_tj'])
                $data['is_tj'] = $is_tj;
            if ($p_id != $info1['p_id'])
                $data['p_id'] = $p_id;
            if ($c_id != $info1['c_id'])
                $data['c_id'] = $c_id;
            if ($q_id != $info1['q_id'])
                $data['q_id'] = $q_id;
            if ($starthdtime1 != $info1['starthdtime'])
                $data['starthdtime'] = $starthdtime1;
            if ($endhdtime1 != $info1['endhdtime'])
                $data['endhdtime'] = $endhdtime1;
            if ($address != $info1['address'])
                $data['address'] = $address;
            if ($shuoming != $info1['shuoming'])
                $data['shuoming'] = $shuoming;
            if ($chengchelx != $info1['chengchelx'])
                $data['chengchelx'] = $chengchelx;
            if (!empty($mapimg))
                $data['mapimg'] = $mapimg;
            if (!empty($hdimg))
                $data['hdimg'] = $hdimg;
            if (!empty($bmnum) && $bmnum != $info1['bmnum'])
                $data['bmnum'] = $bmnum;
            $data['addtime'] = time();
            if ($status != $info1['status'])
                $data['status'] = $status;
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U("Group/index"));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = D("GroupView");
        $info = $M->where("Group1.id=" . $id)->find();
        $info['chengchelx'] = str_replace(",", "\n", $info['chengchelx']);


        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $citymod = new CityModel();
        if ($is_qx == 1) {
            #区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $citymod->getcity($c_id);
            $this->assign("qlist", $qlist);
        } else {

            #省
            $plist = $citymod->getprovince(1);
            $this->assign("plist", $plist);
            $this->assign("c_name", $info['c_name']);
            $this->assign("q_name", $info['q_name']);
        }
        $this->assign("starthdtime", date("Y-m-d H:i", $info['starthdtime']));
        $this->assign("endhdtime", date("Y-m-d H:i", $info['endhdtime']));

        $this->assign("info", $info);
        $this->assign("is_edit", 1);
        $this->assign("comname", $this->getgzh_ins($info['uid']));


        $this->display("add_group");
    }

    /**
     * 快速修改
     * 推荐
     * 团购活动
     */
    public function edit_tj() {
        $id = $_GET['id'];
        $is_tj = $_GET['is_tj'];
        $is_tjf = $is_tj == 1 ? 0 : 1;
        $G = M("Group");
        $rs = $G->where("id=" . $id)->save(array("is_tj" => $is_tjf));
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 删除
     * 团购活动
     */
    public function del_group() {
        $id = $_GET['id'];
        $M = M("Group");
        $info = $M->where("id=" . $id)->find();
        if (!empty($info['mapimg'])) {
            unlink("." . $info['mapimg']);
        }
        if (!empty($info['hdimg'])) {
            unlink("." . $info['hdimg']);
        }
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 修改
     * 状态
     * 团购活动
     */
    public function edit_status() {
        $status = $_GET['status'];
        $statusf = $status == 1 ? "0" : "1";
        $id = $_GET['id'];
        $M = M("Group");
        $rs = $M->where("id=" . $id)->save(array("status" => $statusf));
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     *  修改 参加团购活动记录状态
     */
    public function edit_jl_status() {
        $status = $_GET['status'];
        $statusf = $status == 1 ? "0" : "1";
        $id = $_GET['id'];
        $M = M("Groupaddjl");
        $rs = $M->where("id=" . $id)->save(array("status" => $statusf));
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 参见活动记录
     */
    public function groupaddjl() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $gMod = D("GroupaddjlView");
        $where = "1";
        #团购活动列表
        $this->assign("glist",$this->getgroup());
        
        $gid=$_GET['gid'];
        if(!empty($gid)){
            $where.=" and Groupaddjl.g_id=".$gid;
        }
        $keys=$_GET['keys'];
        $keys=$keys=="请输入关键字"?"":$keys;
        if(!empty($keys)){
            $where.=" and Groupaddjl.name like '%".$keys."%'";
        }
        $this->assign("keys",$keys);
        $this->assign("gid",$gid);
        
        $cou = $gMod->where($where)->count();
        $p = new Page($cou, 10);
        $list = $gMod->where($where)->limit($p->firstRow . "," . $p->listRows)->order("Groupaddjl.addtime desc")->select();
        
        
        $arr = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i", $v['addtime']);
            $list[$k]['statusf'] = $arr[$v['status']];
        }
        $this->assign("list", $list);

        $this->assign("page", $p->show());

        $this->display();
    }

    /**
     * 删除记录
     */
    public function del_jl() {
        $id = $_GET['id'];
        $M = M("Groupaddjl");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    //----------------------private-------
    /**
     * 获取商城列表
     */
    private function getgzh() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $where = "1";
        if ($is_qx == 1) {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];

            $where.=" and p_id=" . $p_id;
            $where.=" and c_id=" . $c_id;
        }
        $M = M("Dianpumember");
        $list = $M->where($where)->field("a_id,a_name,lxrname,company")->select();

        return $list;
    }

    /**
     * 商城名称
     */
    private function getgzh_ins($uid) {
        $M = M("Dianpumember");
        $list = $M->where("a_id=" . $uid)->field("a_id,a_name,lxrname,company")->find();
        return $list['a_name'] . "[" . $list['company'] . "]";
    }

    /**
     * 检查 团购活动 标题是否存在
     */
    private function check_title($title) {
        $M = M("Group");
        $cou = $M->where("title ='" . $title . "'")->count();
        if ($cou > 0)
            return true;
        else
            return FALSE;
    }

    /**
     * 获取团购活动列表
     */
    private function getgroup() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        if($is_qx==1){
            $p_id=$_SESSION['my_info']['proid'];
            $c_id=$_SESSION['my_info']['cityid'];
        }
        $M=M("Group");
        $where="1";
        if(!empty($p_id))
            $where.=" and p_id=".$p_id;
        if(!empty($c_id))
            $where.=" and c_id=".$c_id;
        $rs=$M->where($where)->order("addtime desc")->select();
        return $rs;
    }

    //--------------------------------ajax------------
}

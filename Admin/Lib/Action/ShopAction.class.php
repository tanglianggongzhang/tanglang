<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShopAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShopAction extends CommonAction {

    /**
     * 商品列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->display();
    }

    /**
     * 商城分类
     */
    public function shopcategory() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $cmod = new ShopcategoryModel();
        $list = $cmod->getcategory();
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 添加分类
     */
    public function addshopcategory() {
        if (IS_POST) {
            $cname = trim($_POST['cname']);
            if (empty($cname)) {
                $this->success("分类名称不能为空！");
                exit;
            }
            if (!empty($_FILES['clogo']['name'])) {
                $path = "/Uploads/shangjia/";
                $imgsrc = $this->upload("." . $path);
                if (!empty($imgsrc[0]['savename']))
                    $clogo = $path . $imgsrc[0]['savename'];
            }
            $cmod = new ShopcategoryModel();
            $data = array();
            $pid = $_POST['pid'];
            $data['cname'] = $cname;
            $data['clogo'] = $clogo;
            $data['pid'] = $pid;
            if ($cmod->checkname($cname)) {
                $this->error("商品分类已经存在！");
                exit;
            }

            if ($pid == 0) {
                $data['level'] = 0;
            } else {
                $data['level'] = $cmod->getlevel($pid);
                $data['level']+=1;
            }
            $res = $cmod->add($data);
            if ($res) {
                $this->success("操作成功!", U("Shop/shopcategory"));
            } else {
                $this->error("操作失败!");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $scmod = new ShopcategoryModel();
        $sclist = $scmod->getcategory();
        $this->assign("sclist", $sclist);
        $this->display();
    }

    /**
     * 编辑分类
     */
    public function editshopcategory() {
        if (IS_POST) {
            $cid = $_POST['cid'];
            $scmod = new ShopcategoryModel();
            $info = $scmod->where("cid=" . $cid)->find();

            $cname = trim($_POST['cname']);
            if (!empty($_FILES['clogo']['name'])) {

                $path = "/Uploads/shangjia/";


                $imgsrc = $this->upload("." . $path);
                if (!empty($imgsrc[0]['savename'])) {
                    $clogo = $path . $imgsrc[0]['savename'];
                    unlink("." . $path . $info['clogo']);
                }
            }
            $pid = $_POST['pid'];
            if ($pid == 0) {
                $level = 0;
            } else {
                $level = $scmod->getlevel($pid);
                $level+=1;
            }

            $data = array(
                "cname" => $cname,
                "clogo" => $clogo,
                "pid" => $pid,
                "level" => $level
            );
            $where = "cid=" . $cid;
            $res = $scmod->where($where)->save($data);
            if ($res) {
                $this->success("操作成功!", U("Shop/shopcategory"));
            } else {
                $this->error("操作失败!");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $cid = $_GET['cid'];
        $scmod = new ShopcategoryModel();
        $info = $scmod->getinfo($cid);
        $this->assign("info", $info);
        #分类
        $sclist = $scmod->getcategory();
        $this->assign("sclist", $sclist);

        $this->display("addshopcategory");
    }

    /**
     * 删除分类
     */
    public function delshopcategory() {
        $cid = $_GET['cid'];
        $scmod = new ShopcategoryModel();
        $info = $scmod->getinfo($cid);
        if (!empty($info['clogo'])) {
            unlink("." . $info['clogo']);
        }
        $rs = $scmod->where("cid=" . $cid)->delete();
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败!");
    }

    /**
     * 添加幻灯片
     */
    public function add_hdp() {
        if (IS_POST) {
            $name = trim($_POST['name']);
            $iminf = $this->upload();
            if (!empty($iminf[0]['savename'])) {
                $img = "/Uploads/product/" . $iminf[0]['savename'];
            }
            $link = trim($_POST['link']);
            $uid = $_POST['gzid'];
            $addtime = time();
            $status = $_POST['status'];

            $M = M("Hdpmember");
            $data = array(
                "name" => $name,
                "img" => $img,
                "link" => $link,
                "uid" => $uid,
                "addtime" => $addtime,
                "status" => $status,
                "type" => 2
            );
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
        $this->display();
    }

    /**
     * 幻灯片
     * 列表
     */
    public function list_hdp() {

        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh()); #设计师

        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $uid = $_GET['uid'];

        $this->assign("keys", $keys);
        $this->assign("uid", $uid);

        import("ORG.Util.Page");
        $where = "Hdp.type=2 ";

        if (!empty($keys))
            $where.=" and Hdp.name like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and Hdp.uid=" . $uid;


        $M = D("HdpshopView");
        $totalRows = $M->where($where)->count();

        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("Hdp.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();


        $this->assign("page", $p->show());

        foreach ($list as $k => $v) {

            $list[$k]['status_f'] = $v['status'] == 1 ? "已审核" : "未审核";
        }
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 删除幻灯片
     */
    public function del_hpd() {
        $id = $_GET['id'];
        $M = M("Hdpmember");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 编辑
     * 幻灯片
     */
    public function edit_hpd() {

        if (IS_POST) {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            $gzid = $_POST['gzid'];
            $status = trim($_POST['status']);
            $link = $_POST['link'];
            $imgi = $this->upload();
            if (!empty($imgi[0]['savename'])) {
                $img = "/Uploads/product/" . $imgi[0]['savename'];
            }

            $data = array();

            $M = M("Hdpmember");
            $info1 = $M->where("id=" . $id)->find();
            if (!empty($img)) {
                $data['img'] = $img;
                unlink("." . $info1['img']);
            }
            if (!empty($name))
                $data['name'] = $name;
            if ($link != $info1['link'])
                $data['link'] = $link;
            if ($gzid != $info1['uid'])
                $data['uid'] = $gzid;
            if ($status != $info1['status'])
                $data['status'] = $status;
            $data['addtime'] = time();
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U('Shop/list_hdp'));
            else {

                $this->error("操作失败！");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        if (empty($id)) {
            $this->error("请选择要编辑的幻灯片");
            exit;
        }
        $M = M("Hdpmember");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $gzlist = $this->getgzh();
        $this->assign("sjlist", $gzlist); #工长

        $this->display("add_hdp");
    }

    /**
     * 快速修改状态
     * 幻灯片
     */
    public function status_hdp() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 0)
            $statusf = 1;
        else
            $statusf = 0;
        $M = M("Hdpmember");
        $rs = $M->where("id=" . $id)->save(array("status" => $statusf));
        if ($rs)
            $this->success("操作成功");
        else
            $this->error("操作失败！");
    }

    /**
     * 添加优惠券
     */
    public function add_yhq() {
        if (IS_POST) {
            $now = time();
            $name = trim($_POST['name']);
            $uid = $_POST['gzid'];
            if (empty($name)) {
                $this->error("优惠券名称不能为空！");
                exit;
            }
            $M = M("Yhq");
            $cou = $M->where("name='" . $name . "'")->count();
            if ($cou > 0) {
                $this->error("优惠券名称已经存在！");
                exit;
            }
            if (empty($uid)) {
                $this->error("请选择商城！");
                exit;
            }
            $imginf = $this->upload();
            if (!empty($imginf)) {
                if (!empty($imginf[0]['savename']))
                    $yhimg = "/Uploads/product/" . $imginf[0]['savename'];
            }
            $is_tj = $_POST['is_tj'];
            $status = $_POST['status'];
            $dyprice = trim($_POST['dyprice']);
            $xyprice = trim($_POST['xyprice']);
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $yhqnum = trim($_POST['yhqnum']);
            $czsm = trim($_POST['czsm']);
            $sytj = trim($_POST['sytj']);
            if (empty($startdate)) {
                $this->error("开始日期不能为空！");
                exit;
            }
            if (empty($enddate)) {
                $this->error("结束日期不能为空！");
                exit;
            }
            if (!empty($startdate)) {
                $starttime = strtotime($startdate);
            }
            if (!empty($enddate)) {
                $endtime = strtotime($enddate);
            }
            if ($starttime > $endtime) {
                $this->error("开始日期不能大于结束日期！");
                exit;
            }
            if (empty($yhqnum)) {
                $this->error("优惠券数目不能为空！");
                exit;
            }
            $pc = $this->getprocity($uid);
            $p_id = $pc['p_id'];
            $c_id = $pc['c_id'];
            $data = array(
                "name" => $name,
                "is_tj" => $is_tj,
                "yhimg" => $yhimg,
                "dyprice" => $dyprice,
                "xyprice" => $xyprice,
                "startdate" => $starttime,
                "enddate" => $endtime,
                "yhqnum" => $yhqnum,
                "ysy" => 0,
                "addtime" => time(),
                "p_id" => $p_id,
                "c_id" => $c_id,
                "adduid" => $_SESSION['my_info']['a_id'],
                "uid" => $uid,
                "czsm" => $czsm,
                "sytj" => $sytj,
                "status" => $status
            );
            
            $rs = $M->add($data);
            $M2=M("YhqXiangxi");
            if ($rs) {
                //生成优惠券密码
               
                $id=$M->getLastInsID();
                for ($y = 0; $y < $yhqnum; $y++) {
                    $pwd=$this->shengchengpwd();
                    $data1=array(
                        "id"=>$id,
                        "yhqpwd"=>$pwd,
                        "issy"=>0
                    );
                    $M2->add($data1);
                }
            }
            $this->success("操作成功！",U('Shop/list_yhq'));
            exit;
        }
       
        parent::_initalize();
        $this->assign('systemConfig', $this->systemConfig);
        $this->assign("sjlist", $this->getgzh());
        
        $this->display();
    }

    /**
     * 优惠券列表
     */
    public function list_yhq() {
        parent::_initalize();
        $this->assign('systemConfig', $this->systemConfig);
        $this->display();
    }

    /**
     * 删除优惠券
     */
    public function del_yhq() {
        
    }

    /**
     * 编辑优惠券
     */
    public function edit_yhq() {
        parent::_initalize();
        $this->assign('systemConfig', $this->systemConfig);
        $this->display("add_yhq");
    }

    /**
     * 优惠券详细表
     */
    public function list_xx_yhq() {
        parent::_initalize();
        $this->assign('systemConfig', $this->systemConfig);
        $this->display();
    }

    /**
     * 优惠券 删除详细表
     */
    public function del_xx_yhq() {
        
    }

    /**
     * 设置优惠券 附加表 状态
     */
    public function edit_status_xxyhq() {
        
    }

    /**
     * 设置优惠券 主表 状态
     */
    public function edit_status_yhq() {
        
    }

    //----------------------private-------
    /**
     * 获取设计师列表
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
     * 根据用户id
     * 获取省和市
     */
    private function getprocity($uid) {

        $where = "a_id=" . $uid;

        $M = M("Dianpu");
        $list = $M->where($where)->field("p_id,c_id")->find();
        return $list;
    }

    /**
     * 检查密码是否存在
     */
    private function ischeck($name) {
        $M = M("YhqXiangxi");
        $rs = $M->where("yhqpwd='" . $name . "'")->count();
        if ($rs > 0)
            return true;
        else
            return false;
    }

    private function shengchengpwd() {
        do {
            $sj1 = rand(65, 90);
            $or1 = chr($sj1);
            $sj2 = rand(65, 90);
            $or2 = chr($sj2);
            $sj3 = rand(65, 90);
            $or3 = chr($sj3);
            $sj4 = rand(65, 90);
            $or4 = chr($sj4);
            $sj5 = rand(0, 9);
            $sj6 = rand(0, 9);
            $sj7 = rand(0, 9);
            $sj8 = rand(0, 9);
            $yhqpwd = $or1 . $or2 . $or3 . $or4 . "_" . $sj8 . $sj5 . $sj6 . $sj7;
            $tf=$this->ischeck($yhqpwd);
            
        } while ($tf);
        return $yhqpwd;
    }

    //------------------ajax
    public function ajaxgetsj() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = $_POST['gname'];
        $m = M("Dianpumember");
        $where = "company like '%" . $gname . "%' or a_name like '%" . $gname . "%' or lxrname like '%" . $gname . "%'";
        $rs = $m->where($where)->select();
        if ($rs)
            $data = array("status" => 1, "data" => $rs);
        else
            $data = array("status" => 0, "data" => "");
        echo json_encode($data);
    }

}

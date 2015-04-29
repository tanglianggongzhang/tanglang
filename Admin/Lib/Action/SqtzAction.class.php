<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SqtzAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class SqtzAction extends CommonAction {

    /**
     * 列表
     * 社区团装
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        @import("ORG.Util.Page");
        $cmod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            //地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
        } else {
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
        }
        $q_id = $_GET['q_id'];
        $where = "1";
        if (!empty($p_id))
            $where.=" and p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and c_id=" . $c_id;
        if (!empty($q_id))
            $where.=" and q_id=" . $q_id;
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        if (!empty($keys))
            $where.=" and name like '%" . $keys . "%'";
        $this->assign("keys", $keys);
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("q_id", $q_id);

        $smod = M("Sqtzview");

        $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));
        $totalRows = $smod->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $smod->where($where)->limit($p->firstRow . "," . $p->listRows)->order("orders desc")->select();
        $arr = array("0" => "隐藏", "1" => "显示");
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i:s", $v['addtime']);
            $list[$k]['statusf'] = $arr[$v['is_display']];
        }
        $this->assign("list", $list);
        $this->assign("page", $p->show());
        $this->display();
    }

    /**
     * 添加
     * 社区团装
     */
    public function addsqtz() {
        if (IS_POST) {
            $name = trim($_POST['name']);
            if (empty($name)) {
                $this->error("社区团装名称不能为空！");
                exit;
            }
            $ftname = trim($_POST['ftname']);
            $tzimg = "";
            $imginf = $this->upload("./Uploads/sqtz/");
            if (!empty($imginf[0]['savename']))
                $tzimg = "/Uploads/sqtz/" . $imginf[0]['savename'];

            $junjia = trim($_POST['junjia']);
            $is_display = $_POST['is_display'];
            $huxing = $_POST['huxing'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $address = $_POST['address'];
            $xq_id = $_POST['xq_id'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $qyhnum = trim($_POST['qyhnum']);
            $yusuan = trim($_POST['yusuan']);
            $shengprice = trim($_POST['shengprice']);
            $tzjs = trim($_POST['tzjs']);
            $hdnr = trim($_POST['hdnr']);
            $szqy = trim($_POST['szqy']);
            $hddd = trim($_POST['hddd']);
            $bmdh = trim($_POST['bmdh']);
            $hdxq = trim($_POST['hdxq']);
            $cyrs = trim($_POST['cyrs']);
            $orders = trim($_POST['orders']);
            $jingdu = trim($_POST['jingdu']);
            $weidu = trim($_POST['weidu']);
            $addtime = time();
            $m = M("Sqtz");
            $data = array(
                "name" => $name,
                "ftname" => $ftname,
                "tzimg" => $tzimg,
                "junjia" => $junjia,
                "is_display" => $is_display,
                "huxing" => $huxing,
                "p_id" => $p_id,
                "c_id" => $c_id,
                "q_id" => $q_id,
                "address" => $address,
                "xq_id" => $xq_id,
                "starttime" => $starttime,
                "endtime" => $endtime,
                "qyhnum" => $qyhnum,
                "yusuan" => $yusuan,
                "shengprice" => $shengprice,
                "tzjs" => $tzjs,
                "hdnr" => $hdnr,
                "szqy" => $szqy,
                "hddd" => $hddd,
                "bmdh" => $bmdh,
                "hdxq" => $hdxq,
                "cyrs" => $cyrs,
                "orders" => $orders,
                "jingdu" => $jingdu,
                "weidu" => $weidu,
                "addtime" => $addtime
            );
            $res = $m->add($data);
            if ($res)
                $this->success("操作成功!", U("Sqtz/index"));
            else
                $this->error("操作失败!");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        #户型
        $hxmod = M("Hxcategory");
        $hxlist = $hxmod->where(1)->order("addtime desc")->select();
        $this->assign("hxlist", $hxlist);
        #小区
        $xqmod = M("Xq");
        $xqlist = $xqmod->where(1)->order("addtime desc")->select();
        $this->assign("xqlist", $xqlist);
        #省
        $cmod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            //非地区管理员
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
            $this->assign("is_qx", 0);
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("p_id",$p_id);
            $this->assign("c_id",$c_id);
            $this->assign("is_qx", 1);
        }
        $this->display();
    }

    /**
     * 编辑
     * 社区团装
     */
    public function editsqtz() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        if (IS_POST) {
            $mod = M("Sqtzview");
            $id = $_POST['id'];
            $info = $mod->where("id=" . $id)->find();
            $name = trim($_POST['name']);
            if (empty($name)) {
                $this->error("社区团装名称不能为空");
                exit;
            }
            $ftname = trim($_POST['ftname']);
            $tzimg = "";
            if (!empty($_FILES['tzimg']['name'])) {
                unlink("." . $info['tzimg']);
                $imginf = $this->upload("./Uploads/sqtz/");
                if (!empty($imginf[0]['savename']))
                    $tzimg = "/Uploads/sqtz/" . $imginf[0]['savename'];
            }
            $junjia=  trim($_POST['junjia']);
            $is_display=$_POST['is_display'];
            $huxing=$_POST['huxing'];
            $p_id=$_POST['p_id'];
            $c_id=$_POST['c_id'];
            $q_id=$_POST['q_id'];
            $address=  trim($_POST['address']);
            $xq_id=$_POST['xq_id'];
            $starttime=$_POST['starttime'];
            $endtime=$_POST['endtime'];
            $qyhnum=  trim($_POST['qyhnum']);
            $qyhnum=  intval($qyhnum);
            $yusuan=  trim($_POST['yusuan']);
            $shengprice=  trim($_POST['shengprice']);
            $tzjs=trim($_POST['tzjs']);
            $hdnr=trim($_POST['hdnr']);//活动内容
            $szqy=  trim($_POST['szqy']);
            $hddd=  trim($_POST['hddd']);
            $bmdh=  trim($_POST['bmdh']);
            $hdxq=  trim($_POST['hdxq']);
            $cyrs=  trim($_POST['cyrs']);
            $orders=  trim($_POST['orders']);
            $jingdu=  trim($_POST['jingdu']);
            $weidu=  trim($_POST['weidu']);
            
            $data = array();
            if (!empty($name) && $name != $info['name'])
                $data['name'] = $name;
            if (!empty($ftname) && $ftname != $info['ftname'])
                $data['ftname'] = $ftname;
            if(!empty($tzimg))
                $data['tzimg']=$tzimg;
            if(!empty($junjia)&&$junjia!=$info['junjia'])
                $data['junjia']=$junjia;
            if($is_display!=$info['is_display'])
                $data['is_display']=$is_display;
            if($huxing!=$info['huxing'])
                $data['huxing']=$huxing;
            if($p_id!=$info['p_id'])
                $data['p_id']=$p_id;
            if($c_id!=$info['c_id'])
                $data['c_id']=$c_id;
            if($q_id!=$info['q_id'])
                $data['q_id']=$q_id;
            if($address!=$info['address'])
                $data['address']=$address;
            if($xq_id!=$info['xq_id'])
                $data['xq_id']=$xq_id;
            if($starttime!=$info['starttime'])
                $data['starttime']=$starttime;
            if($endtime!=$info['endtime'])
                $data['endtime']=$endtime;
            if($qyhnum!=$info['qyhnum'])
                $data['qyhnum']=$qyhnum;
            if($yusuan!=$info['yusuan'])
                $data['yusuan']=$yusuan;
            if($shengprice!=$info['shengprice'])
                $data['shengprice']=$shengprice;
            if($tzjs!=$info['tzjs'])
                $data['tzjs']=$tzjs;
            if($hdnr!=$info['hdnr'])
                $data['hdnr']=$hdnr;
            if($szqy!=$info['szqy'])
                $data['szqy']=$szqy;
            if($hddd!=$info['hddd'])
                $data['hddd']=$hddd;
            if($bmdh!=$info['bmdh'])
                $data['bmdh']=$bmdh;
            if($hdxq!=$info['hdxq'])
                $data['hdxq']=$hdxq;
            if($cyrs!=$info['cyrs'])
                $data['cyrs']=$cyrs;
            if($orders!=$info['orders'])
                $data['orders']=$orders;
            if($jingdu!=$info['jingdu'])
                $data['jingdu']=$jingdu;
            if($weidu!=$info['weidu'])
                $data['weidu']=$weidu;
            $res=M("Sqtz")->where("id=".$id)->save($data);
            if($res)
                $this->success ("操作成功！",U("Sqtz/index"));
            else
                $this->error ('操作失败！');
            exit();
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $m = M("Sqtzview");
        $info = $m->where("id=" . $id)->find();

        #户型
        $hxmod = M("Hxcategory");
        $hxlist = $hxmod->where(1)->order("addtime desc")->select();
        $this->assign("hxlist", $hxlist);
        #小区
        $xqmod = M("Xq");
        $xqlist = $xqmod->where(1)->order("addtime desc")->select();
        $this->assign("xqlist", $xqlist);
        #省
        $cmod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            //非地区管理员
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
            $this->assign("is_qx", 0);
            $this->assign("c_name", $cmod->getname($info['c_id']));
            $this->assign("q_name", $cmod->getname($info['q_id']));
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("q_name", $cmod->getname($info['q_id']));
            $this->assign("p_id",$p_id);
            $this->assign("c_id",$c_id);
            $this->assign("is_qx", 1);
        }


        $this->assign("info", $info);

        $this->display("addsqtz");
    }

    /**
     * 删除
     * 社区团装
     */
    public function delsqtz() {
        parent::_initalize();
        $id = $_GET['id'];
        $m = M("Sqtz");
        $info=$m->where("id=".$id)->find();
        if(!empty($info['tzimg'])){
            unlink(".".$info['tzimg']);
        }
        $res = $m->where("id=" . $id)->delete();
        if ($res)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * ajax
     * 修改排序
     * 社区团装
     */
    public function update_orders_sqtz() {
        header('Content-Type:application/json; charset=utf-8');
        $id = $_POST['id'];
        $order = trim($_POST['order']);
        $order = intval($order);
        $data = array("orders" => $order);
        $m = M("Sqtz");
        $res = $m->where("id=" . $id)->save($data);
        if ($res)
            $info = array("status" => 1, "info" => "修改成功");
        else
            $info = array("status" => 0, "info" => "修改失败");
        echo json_encode($info);
    }

    /**
     * 更改
     * 状态
     * 社区团装
     */
    public function update_status_sqtz() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status)
            $up = 0;
        else
            $up = 1;
        $m = M("Sqtz");
        $res = $m->where("id=" . $id)->save(array("is_display" => $up));
        if ($res)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 列表
     * 参与社区团装的记录
     */
    public function list_tzjl() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $M=M("Sqtz");
        $sqtzlist=$M->where(1)->order("orders desc")->field("id,name")->select();
        $this->assign('sqtzlist',$sqtzlist);
        //----团装记录 start
        @import("ORG.Util.Page");
        $tzmod=D("TzjlView");
        $where=1;
        $sqtzid=$_GET['sqtzid'];
        if(!empty($sqtzid))
            $where.=" and Tzjl.tzid=".$sqtzid;
        
        $totalRows=$tzmod->where($where)->count();
        $p=new Page($totalRows,10);
        $list=$tzmod->where($where)->order("Tzjl.addtime desc")->limit($p->firstRow.",".$p->listRows)->select();
        $arr=array("0"=>"未联系","1"=>"已联系");
        foreach ($list as $k=>$v){
            $list[$k]['addtimef']=date("Y-m-d H:i:s",$v['addtime']);
            $list[$k]['islx']=$arr[$v['is_lx']];
        }
        $this->assign("list",$list);
        $this->assign("page",$p->show());
        //----团装记录 end
        
        $this->display();
    }
    /**
     * 搜索社区团装
     */
    public function search_sqtz(){
        header("Content-Type:application/json; charset=utf-8");
        $keys=  trim($_POST['keys']);
        $m=M("Sqtz");
        if(!empty($keys)){
            
            $list=$m->where("name like '%".$keys."%'")->field("id,name")->order("orders desc")->select();
            
        }else{
            $list=$m->where("1")->order("orders desc")->field("id,name")->select();
            
        }
        echo json_encode($list);
    }

    /**
     * 删除
     * 参与社区团装的记录
     */
    public function del_tzjl() {
        $id=$_GET['id'];
        $M=M("Tzjl");
        $res=$M->where("id=".$id)->delete();
        if($res)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }

    /**
     * 更改
     * 状态
     * 参与社区团装的记录
     */
    public function update_status_tzjl() {
        $id=$_GET['id'];
        $is_lx=$_GET['status'];
        if($is_lx==1)
            $is_lx=0;
        else
            $is_lx=1;
        $M=M("Tzjl");
        $res=$M->where("id=".$id)->save(array("is_lx"=>$is_lx));
       
        if($res)
            $this->success ("修改成功!",U("Sqtz/list_tzjl"));
        else
            $this->error ("修改失败!");
    }

    /**
     * 添加小区
     */
    public function addxq() {
        if (IS_POST) {
            $name = trim($_POST['name']);
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $aid = $_SESSION['my_info']['a_id'];

            if (empty($p_id)) {
                $this->error("请选择省！");
                exit;
            }
            if (empty($c_id)) {
                $this->error("请选择市!");
                exit;
            }
            if (empty($q_id)) {
                $this->error("请选择区！");
                exit;
            }
            if (empty($name)) {
                $this->error("请填写名称！");
                exit;
            }
            $m = M("Xq");
            $data = array(
                "p_id" => $p_id,
                "c_id" => $c_id,
                "q_id" => $q_id,
                "name" => $name,
                "adduid" => $aid,
                "addtime" => time()
            );
            $res = $m->add($data);
            if ($res) {
                $this->success("操作成功！", U('Sqtz/listxq'));
            } else {
                $this->success("操作失败！");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $cmod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            //地区管理员

            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("is_qx", 1);
        } else {
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
            $this->assign("is_qx", 0);
        }
        $this->display();
    }

    /**
     * 编辑小区
     */
    public function editxq() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $m = M("Xq");
        $info = $m->where("id=" . $id)->find();
        $this->assign("info", $info);
        $cmod = new CityModel();
        $c_name = $cmod->getname($info['c_id']);
        $this->assign("c_name", $c_name);
        $q_name = $cmod->getname($info['q_id']);
        $this->assign("q_name", $q_name);
        $plist = $cmod->getcity(1);
        $this->assign("plist", $plist);
        if (IS_POST) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];

            if (empty($name)) {
                $this->error("名称不能为空！");
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
            $data = array();
            if (!empty($p_id) && $p_id != $info['p_id']) {
                $data['p_id'] = $p_id;
            }
            if (!empty($c_id) && $c_id != $info['c_id']) {
                $data['c_id'] = $c_id;
            }
            if (!empty($q_id) && $q_id != $info['q_id']) {
                $data['q_id'] = $q_id;
            }
            if (!empty($name) && $q_id != $info['name']) {
                $data['name'] = $name;
            }

            $res = $m->where("id=" . $id)->save($data);
            if (res)
                $this->success("操作成功！", U("Sqtz/listxq"));
            else
                $this->error("操作失败！");
            exit();
        }
        $this->display("addxq");
    }

    /**
     * 删除小区
     */
    public function delxq() {
        $id = $_GET['id'];
        $m = M("Xq");
        $res = $m->where("id=" . $id)->delete();
        if ($res)
            $this->success("操作成功!");
        else
            $this->error("操作失败！");
    }

    /**
     * 列表小区
     */
    public function listxq() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $where = "1";
        $cmod = new CityModel();

        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            //地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
            $this->assign("is_qx", 1);
        } else {
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            $plist = $cmod->getcity(1);
            $this->assign("plist", $plist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
            $this->assign("c_name", $cmod->getname($c_id));
            $this->assign("is_qx", 0);
        }

        $q_id = $_GET['q_id'];
        $this->assign("q_id", $q_id);
        $this->assign("q_name", $cmod->getname($q_id));
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $this->assign("keys", $keys);

        @import("ORG.Util.Page");
        $mod = D("XqView");
        if (!empty($p_id))
            $where.=" and Xq.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and Xq.c_id=" . $c_id;
        if (!empty($q_id))
            $where.=" and Xq.q_id=" . $q_id;
        if (!empty($keys))
            $where.=" and Xq.name like '%" . $keys . "%'";
        $totalRows = $mod->where($where)->count();
        $page = new Page($totalRows, 10);
        $list = $mod->where($where)->limit($page->firstRow . "," . $page->listRows)->order("Xq.addtime desc ")->select();
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i:s", $v['addtime']);
        }
        $this->assign("list", $list);
        $this->assign("page", $page->show());

        $this->display();
    }

    /**
     * ajax
     * 获取小区
     */
    public function getxq() {
        header("Content-Type:application/json; charset=utf-8");
        $m = M("Xq");
        $pid = $_POST['pid'];
        $cid = $_POST['cid'];
        $qid = $_POST['qid'];

        $list = $m->where("p_id=" . $pid . " and c_id=" . $cid . " and q_id=" . $qid)->select();

        echo json_encode($list);
    }

}

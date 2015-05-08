<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShejiAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShejiAction extends CommonAction {

    /**
     * 设计师案例
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh()); #设计师
        $cmod = new CityModel();
        $pro_list = $cmod->getcity(1);
        $this->assign("pro_list", $pro_list);
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $this->assign("is_qx", $is_qx);
        if ($is_qx == 0) {
            $p_id = $_GET['province'];
            $c_id = $_GET['city'];
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
        }
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $uid = $_GET['uid'];

        $this->assign("keys", $keys);
        $this->assign("uid", $uid);
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("cityname", $cmod->getname($c_id));

        import("ORG.Util.Page");
        $where = "cases.type=2 ";
        if (!empty($p_id))
            $where.=" and cases.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and cases.c_id=" . $c_id;
        if (!empty($keys))
            $where.=" and cases.title like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and cases.uid=" . $uid;


        $M = D("SjcaseView");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("cases.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();


        $this->assign("page", $p->show());
        $inf = include_once './Common/config2.php';
        foreach ($list as $k => $v) {
            $list[$k]['is_jds'] = $v['is_jd'] == 1 ? "经典" : "普通";
            $list[$k]['status_f'] = $v['status'] == 1 ? "已审核" : "未审核";
        }
        $this->assign("list", $list);
        $this->assign("jd", $inf['zxjd']);


        $this->display();
    }

    /**
     * 添加
     * 设计师
     */
    public function add_sj_case() {
        if (IS_POST) {
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $gzid = $_POST['gzid'];
            $type = 2;
            $title = trim($_POST['title']);
            $is_jd = $_POST['is_jd'];
            $hid = $_POST['hid'];
            $fid = $_POST['fid'];
            $price = trim($_POST['price']);
            $mianji = trim($_POST['mianji']);
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $goods = trim($_POST['goods']);
            $comments = trim($_POST['comments']);
            $status = $_POST['status'];
            $fengmian = $_POST['fengmian'];
            $jianjie = trim($_POST['jianjie']);


            $m = M("Case");
            $cou = $m->where("title='" . $title . "' and type=2")->count();
            if ($cou > 0) {
                $this->error("该案例已经存在！");
                exit;
            }


            $tpjhlist = $this->gettpjhlist(); #图片集合分类
            $tjarr = $this->gettjkeys($tpjhlist); #图片集合的key值
            $tjinfo = $this->gettjarr($tpjhlist); #

            $path = "/Uploads/product/";
            $info = $this->upload("." . $path);
            $tj = array();

            if (!empty($info)) {
                foreach ($info as $k => $v) {
                    if (in_array($v['key'], $tjarr)) {
                        $i = explode("_", $v['key']);
                        $tj[$i[1]]["title"] = $tjinfo[$v['key']];
                        $tj[$i[1]]["img"][] = $path . $v['savename'];
                    }
                }
                $tuji = json_encode($tj);
            }

            $data = array();
            if (!empty($fengmian))
                $data['fmimg'] = $path . $fengmian;
            if (!empty($tuji))
                $data['img'] = $tuji;
            $data['type'] = 2;
            $data['title'] = $title;
            $data['is_jd'] = $is_jd;
            $data['hid'] = $hid;
            $data['fid'] = $fid;
            $data['price'] = $price;
            $data['mianji'] = $mianji;
            $data['description'] = $description;
            $data['keywords'] = $keywords;
            $data['goods'] = $goods;
            $data['comments'] = $comments;
            $data['jianjie'] = $jianjie;
            $data['p_id'] = $p_id;
            $data['c_id'] = $c_id;
            $data['uid'] = $gzid;
            $data['adduid'] = $_SESSION['my_info']['a_id'];
            $data['addtime'] = time();
            $data['status'] = $status;
            $rs = $m->add($data);
            if ($rs)
                $this->success("操作成功!", U('Sheji/index'));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $step = $_GET['step'];
        $step = empty($step) ? 1 : $step;
        if ($step == 1) {
            $this->assign("list", $this->getgzh());
            $this->display();
        } else {
            $uid = $_GET['gzid'];
            if (empty($uid)) {
                $this->error("请选择设计师！");
                exit;
            }

            $pc = $this->getprovcity($uid);
            $this->assign("p_id", $pc['p_id']);
            $this->assign("c_id", $pc['c_id']);
            $this->assign("gzid", $uid);
            $this->assign("step", $step);

            $this->assign("hxcategory", $this->gethxlist()); #户型
            $this->assign("fgcategory", $this->getfglist()); #风格
            $this->assign("tpjhlist", $this->gettpjhlist()); #图集分类
            $this->display("add_sj_case2");
        }
    }

    /**
     * 修改状态
     */
    public function casetatus() {
        $status = $_GET['status'];
        $id = $_GET['id'];
        $M = M("Case");
        if ($status == 1)
            $ups = 0;
        else
            $ups = 1;

        $rs = $M->where("id=" . $id)->save(array("status" => $ups));
        if ($rs) {
            $this->success("操作成功！");
        } else {
            $this->error("操作失败！");
        }
    }

    /**
     * 删除案例
     */
    public function del_case() {
        $id = $_GET['id'];
        $M = M("Case");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }
    /**
     * 编辑案例
     */
    public function edit_case(){
        if(IS_POST){
            $id = $_POST['id'];
            $title = trim($_POST['title']);
            $is_jd = $_POST['is_jd'];
            $hid = $_POST['hid'];
            $fid = $_POST['fid'];
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $mianji = trim($_POST['mianji']);
            $price = trim($_POST['price']);
            $goods = trim($_POST["goods"]);
            $comments = trim($_POST['comments']);
            $status = trim($_POST['status']);
            $jianjie = $_POST['jianjie'];
            $fengmian = $_POST['fengmian']; #封面
            $uid = $_POST['uid'];
            $tpjhlist = $this->gettpjhlist(); #图片集合分类
            $tjarr = $this->gettjkeys($tpjhlist); #图片集合的key值
            $tjinfo = $this->gettjarr($tpjhlist); #
            $path = "/Uploads/product/";
            $info = $this->upload("." . $path);
            $tj = array();

            if (!empty($info)) {
                foreach ($info as $k => $v) {
                    if (in_array($v['key'], $tjarr)) {
                        $i = explode("_", $v['key']);
                        $tj[$i[1]]["title"] = $tjinfo[$v['key']];
                        $tj[$i[1]]["img"][] = $path . $v['savename'];
                    }
                }
                $tuji = json_encode($tj);
            }
            $data = array();
            $M = M("Case");
            $info1 = $M->where("id=" . $id)->find();
            if (!empty($fengmian)) {
                $data['fmimg'] = $path . $fengmian;
                unlink("." . $path . $info1['fmimg']);
            }
            if (!empty($tuji))
                $data['img'] = $tuji;
            if ($title != $info1['title'])
                $data['title'] = $title;
            if ($is_jd != $info1['is_jd'])
                $data['is_jd'] = $is_jd;
            if ($hid != $info1['hid'])
                $data['hid'] = $hid;
            if ($fid != $info1['fid'])
                $data['fid'] = $fid;

            if ($price != $info1['price'])
                $data['price'] = $price;
            if ($mianji != $info1['mianji'])
                $data['mianji'] = $mianji;
            if ($description != $info1['description'])
                $data['description'] = $description;
            if ($keywords != $info1['keywords'])
                $data['keywords'] = $keywords;
            if ($goods != $info1['goods'])
                $data['goods'] = $goods;
            if ($comments != $info1['comments'])
                $data['comments'] = $comments;
            if ($jianjie != $info1['jianjie'])
                $data['jianjie'] = $jianjie;
            if ($uid != $info1['uid'])
                $data['uid'] = $uid;
            if ($status != $info1['status'])
                $data['status'] = $status;
            $data['addtime'] = time();
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！",U('Sheji/index'));
            else
                $this->error("操作失败！");
            
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $id=$_GET['id'];
        if(empty($id)){
            $this->error("请选择要编辑的案例");exit;
        }
        $M=M("Case");
        $info=$M->where("id=".$id)->find();
        $this->assign("info",$info);
        
        $this->assign("gzlist",$this->getgzh());#工长
        $this->assign("hxcategory",$this->gethxlist());#户型
        $this->assign("fgcategory",  $this->getfglist());#风格
        $this->assign("tpjhlist", $this->gettpjhlist()); #图集分类
        if (!empty($info['img']))
            $imgsrc = json_decode($info['img']);

        $imglist = array();
        foreach ($imgsrc as $k => $v) {
            $imglist[$k]['img'] = $v->img;
        }
        $this->assign("imglist", $imglist);
        $this->display();
    }

    //------------------------------------------------------------------
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
        $M = M("Shejiview");
        $list = $M->where($where)->field("a_id,a_name,truename")->select();
        return $list;
    }

    /**
     * 根据用户id 获取用户所属的省市
     * 
     */
    private function getprovcity($uid) {
        $m = M("Shejiview");
        $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        return $info;
    }

    /**
     * 户型
     */
    private function gethxlist() {
        $hxmod = M("Hxcategory");
        $hxlist = $hxmod->where(1)->select();
        return $hxlist;
    }

    /**
     * 风格
     */
    private function getfglist() {
        $fgmod = M("Fgcategory");
        $hxlist = $fgmod->where(1)->select();
        return $hxlist;
    }

    /**
     * 图片集合
     * 
     */
    private function gettpjhlist() {
        #图片集合分类
        $tpjhm = M("Tpjh");
        $tpjhlist = $tpjhm->where("1")->select();
        return $tpjhlist;
    }

    /**
     * 获取图片集合分类的key值
     */
    private function gettjkeys($list) {
        $arr = array();
        $i = 0;
        foreach ($list as $k => $v) {
            $arr[$i] = "imgsrc_" . $v['id'];
            $i++;
        }
        return $arr;
    }

    /**
     * 根据从数据库中获取的图片集合
     * 获取图片集合的数组以id为key值
     */
    private function gettjarr($list) {
        $arr = array();
        foreach ($list as $k => $v) {
            $arr["imgsrc_" . $v['id']] = $v['name'];
        }
        return $arr;
    }

    //-------------------------------------------------------------------
    /**
     * ajax 
     * 获取设计师
     */
    public function ajaxgetsj() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = trim($_POST['gname']);
        if (!empty($gname)) {
            $M = M("Shejiview");
            $list = $M->where("a_name like '%" . $gname . "%' or truename like '%" . $gname . "%'")->select();
            if ($list)
                $data = array("status" => 1, "data" => $list);
            else
                $data = array("status" => 0, "data" => "");
        }else {
            $M = M("Foremanview");
            $list = $M->where("a_name like '%" . $gname . "%' or truename like '%" . $gname . "%'")->select();
            if ($list)
                $data = array("status" => 1, "data" => $list);
            else
                $data = array("status" => 0, "data" => "");
        }
        echo json_encode($data);
    }

}

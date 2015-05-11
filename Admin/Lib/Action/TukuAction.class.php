<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TukuAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class TukuAction extends CommonAction {

    /**
     * 图库列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $where = "1";
        $keys = trim($_GET['keys']);
        $keys = ($keys == "请输入关键字") ? "" : $keys;
        if (!empty($keys))
            $where.=" and title like '%" . $keys . "%'";
        $uid = $_GET['uid'];
        if (!empty($uid))
            $where.=" and uid=" . $uid;
        $this->assign("uid", $uid);
        $this->assign("keys", $keys);
        $M = M("Tuku");
        import("ORG.Util.Page");
        $totalRows = $M->where($where)->count();
        $P = new Page($totalRows, 10);

        $list = $M->where($where)->order("addtime desc")->limit($P->firstRow . "," . $P->listRows)->select();

        $arr = array("0" => "未审核", "1" => "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['status_f'] = $arr[$v['status']];
        }
        $this->assign("list", $list);
        $cf = include("./Common/config2.php");
        $this->assign("tplist", $cf['memtp']);

        $this->assign("page", $P->show());

        $this->display();
    }

    /**
     * 添加图库
     */
    public function add_tuku() {
        if (IS_POST) {
            $uid = $_POST['uid'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $hxid = $_POST['hxid'];
            $jbid = $_POST['jbid'];
            $sxid = $_POST['sxid'];
            $fgid = $_POST['fgid'];
            $kjid = $_POST['kjid'];
            $ysid = $_POST['ysid'];
            $shoucang = trim($_POST['shoucang']);
            $status = $_POST['status'];
            $is_tj = $_POST['is_tj'];
            $fengmian = $_POST['fengmian'];
            $click = trim($_POST['click']);
            if (empty($title)) {
                $this->error("请填写标题！");
                exit;
            }
            if ($this->check_cz($title)) {
                $this->error("标题已经存在！");
                exit;
            }

            if (!empty($fengmian))
                $fengmian = "/Uploads/product/" . $fengmian;
            $addtime = time();
            $info = $this->upload();
            $imginfo = array();
            foreach ($info as $K => $v) {
                if (!empty($v['savename']))
                    $imginfo[] = "/Uploads/product/" . $v['savename'];
            }
            if (!empty($imginfo))
                $imgstr = json_encode($imginfo);

            $data = array(
                "title" => $title,
                "keywords" => $keywords,
                "description" => $description,
                "is_tj" => $is_tj,
                "fmimg" => $fengmian,
                "allimg" => $imgstr,
                "addtime" => $addtime,
                "click" => $click,
                "uid" => $uid,
                "adduid" => $_SESSION['my_info']['a_id'],
                "p_id" => $p_id,
                "c_id" => $c_id,
                "kjid" => $kjid,
                "fgid" => $fgid,
                "hxid" => $hxid,
                "jbid" => $jbid,
                "sxid" => $sxid,
                "ysid" => $ysid,
                "shoucang" => $shoucang,
                "status" => $status
            );
            $M = M("Tuku");
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！");
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $step = $_GET['step'];
        if (empty($step)) {
            $con = include './Common/config2.php';
            $this->assign("tplist", $con['memtp']);
            $this->display();
        } else {
            $type = $_GET['type'];
            $uid = $_GET['gzid'];
            $this->assign("hxlist", $this->get_hx()); #户型
            $this->assign("jubucategory", $this->get_jb()); #局部
            $this->assign("sexi", $this->get_sexi()); #色系
            $this->assign("fglist", $this->get_fege()); #风格
            $this->assign("kjlist", $this->get_kj()); #空间
            $this->assign("yslist", $this->get_ys()); #获取预算
            $this->assign("uid", $uid);
            $pc = $this->getprovcity($uid);
            $this->assign("p_id", $pc['p_id']);
            $this->assign("c_id", $pc['c_id']);

            $this->display("add_tuku2");
        }
    }

    /**
     * 编辑图库
     */
    public function edit_tuku() {
        if (IS_POST) {
            $title = trim($_POST['title']);
            if (empty($title)) {
                $this->error("标题不能为空！");
                exit;
            }
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $hxid = $_POST['hxid'];
            $jbid = $_POST['jbid'];
            $sxid = $_POST['sxid'];
            $fgid = $_POST['fgid'];
            $kjid = $_POST['kjid'];
            $ysid = $_POST['ysid'];
            $shoucang = trim($_POST['shoucang']);
            $click = trim($_POST['click']);
            $status = $_POST['status'];
            $is_tj = $_POST['is_tj'];
            $fengmian = $_POST['fengmian'];
            $id = $_POST['id'];
            $M = M("Tuku");
            $info1 = $M->where("id=" . $id)->find();
            $data = array();
            //封面
            if ($info1['fmimg'] != $fengmian) {
                unlink(".".$info1['fmimg']);
                $data['fmimg'] = $fengmian;
            }
            $img=$this->upload();
            $imgarr=array();
            foreach ($img as $k=>$v){
                $imgarr[]=$v['savename'];
            }
            
            if(!empty($imgarr)){
                $imgstr=  json_encode($imgarr);
                $data['allimg']=$imgstr;
            }
            if($title!=$info1['title'])
                $data['title']=$title;
            if($description!=$info1['description'])
                $data['description']=$description;
            if($keywords!=$info1['keywords'])
                $data['keywords']=$keywords;
            if($hxid!=$info1['hxid'])
                $data['hxid']=$hxid;
            if($jbid!=$info1['jbid'])
                $data['jbid']=$jbid;
            if($sxid!=$info1['sxid'])
                $data['sxid']=$sxid;
            if($fgid!=$info1['fgid'])
                $data['fgid']=$fgid;
            if($kjid!=$info1['kjid'])
                $data['kjid']=$kjid;
            if($ysid!=$info1['ysid'])
                $data['ysid']=$ysid;
            if($shoucang!=$info1['shoucang'])
                $data['shoucang']=$shoucang;
            if($click!=$info1['click'])
                $data['click']=$click;
            if($status!=$info1['status'])
                $data['status']=$status;
            if($is_tj!=$info1['is_tj'])
                $data['is_tj']=$is_tj;
            $rs=$M->where("id=" . $id)->save($data);
            
            if($rs)
                $this->success ("操作成功！",U("Tuku/index"));
            else
                $this->error ("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Tuku");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);

        $this->assign("hxlist", $this->get_hx()); #户型
        $this->assign("jubucategory", $this->get_jb()); #局部
        $this->assign("sexi", $this->get_sexi()); #色系
        $this->assign("fglist", $this->get_fege()); #风格
        $this->assign("kjlist", $this->get_kj()); #空间
        $this->assign("yslist", $this->get_ys()); #预算
        $this->assign("tplist", json_decode($info['allimg']));
        $this->display();
    }

    /**
     * 编辑状态
     */
    public function edit_status() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 1)
            $status_u = 0;
        else
            $status_u = 1;

        $M = M("Tuku");
        $rs = $M->where("id=" . $id)->save(array("status" => $status_u));
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 删除图库
     */
    public function del_tuku() {
        $id = $_GET['id'];
        $M = M("Tuku");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    //---------------------ajax
    /**
     * 获取用户列表
     */
    public function ajax_getulist() {
        header('Content-Type:application/json; charset=utf-8');
        $tid = $_POST['tid'];
        $aname = trim($_POST['aname']);
        if (!empty($aname))
            $map = " and truename like '" . $aname . "' or a_name like '%" . $aname . "%'";
        if ($tid == 1) {
            //普通会员
            $Mod = M("Kehuview");
            $list = $Mod->where("status=1 " . $map)->field("a_id,truename,a_name")->select();
        } elseif ($tid == 2) {
            //工长
            $Mod = M("Foremanview");
            $list = $Mod->where("status=1 " . $map)->field("a_id,truename,a_name")->select();
        } elseif ($tid == 3) {
            //店铺
            $Mod = M("Dianpumember");
            $list = $Mod->where("status=1 " . $map)->field("a_id,company,a_name")->select();
        } elseif ($tid == 4) {
            //设计
            $Mod = M("Shejiview");
            $list = $Mod->where("status=1 " . $map)->field("a_id,truename,a_name")->select();
        } elseif ($tid == 5) {
            $Mod = M("Gongrenview");
            $list = $Mod->where("status=1 " . $map)->field("a_id,truename,a_name")->select();
        }
        if (!empty($list)) {
            $js = json_encode(array("status" => 1, "data" => $list));
        } else {
            $js = json_encode(array("status" => 0, "data" => ""));
        }
        echo $js;
    }

    //-----------------------private
    /**
     * 获取户型
     * @return type
     */
    private function get_hx() {
        $hmod = M("Hxcategory");
        $list = $hmod->where(1)->select();
        return $list;
    }

    /**
     * 获取局部
     */
    private function get_jb() {
        $hmod = M("Jubucategory");
        $list = $hmod->where(1)->select();
        return $list;
    }

    /**
     * 获取色系
     */
    private function get_sexi() {
        $hmod = M("Sexicategory");
        $list = $hmod->where(1)->select();
        return $list;
    }

    /**
     * 获取风格
     */
    private function get_fege() {
        $hmod = M("Fgcategory");
        $list = $hmod->where(1)->select();
        return $list;
    }

    /**
     * 获取空间
     */
    private function get_kj() {
        $hmod = M("Kjcategory");
        $list = $hmod->where(1)->select();
        return $list;
    }

    /**
     * 获取预算
     */
    private function get_ys() {
        $hmod = M("Yusuan");
        $list = $hmod->where(1)->select();
        return $list;
    }

    /**
     * 获取省id和市id
     * @param type $uid
     * @return type
     */
    private function getprovcity($uid) {
        $M2 = M("Member");
        $info1 = $M2->where("a_id=" . $uid)->find();
        if ($info1['a_type'] == 1) {
            //普通
            $m = M("Webmember");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        } elseif ($info1['a_type'] == 2) {
            //工长
            $m = M("ForemanInfo");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        } elseif ($info1['a_type'] == 3) {
            //店铺
            $m = M("Dianpu");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        } elseif ($info1['a_type'] == 4) {
            //设计
            $m = M("Sheji");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        } else {
            //工人
            $m = M("Gongren");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        }
        return $info;
    }

    /**
     * 检查图片是否存在
     */
    private function check_cz($title) {
        $m = M("Tuku");

        $rs = $m->where("title like'%" . $title . "%'")->count();
        if ($rs > 0)
            return true;
        else
            return false;
    }

}

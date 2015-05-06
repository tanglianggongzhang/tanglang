<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForemanAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GongzhangAction extends CommonAction {

    /**
     * 施工动态列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);

        $this->display();
    }

    /**
     * 添加施工动态
     */
    public function addsgdt() {
        if (IS_POST) {
            $title=  trim($_POST['title']);
            if(empty($title)){
                $this->error("标题名称不能为空！");
                exit;
            }
            $m=M("Shigongdt");
            $cou=$m->where("title='".$title."'")->count();
            if($cou>0){
                $this->error("该施工动态已经存在！");
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
            $data["p_id"] = $_POST['p_id'];
            $data["c_id"] = $_POST['c_id'];
            $data['uid'] = $_POST['gzid'];
          
            $fm = $_POST['fengmian'];
            if (!empty($fm))
                $data['fmimg'] = $path . $fm;
            $sort=  trim($_POST['sort']);
            $data['sort']=$sort;
            $status=$_POST['status'];
            $data['status']=$status;
            $data['adduid']=$_SESSION['my_info']['a_id'];
            $data['img']=$tuji;
            $data['jieduan']=$_POST['jieduan'];
            $data['yezhu']=$_POST['yezhu'];
            $data['yusuan']=$_POST['yusuan'];
            $data['mianji']=$_POST['mianji'];
            $data['huxing']=$_POST['huxing'];
            $data['keywords']=trim($_POST['keywords']);
            $data['description']=trim($_POST['description']);
            $data['gaishu']=  trim($_POST['gaishu']);
            $data['title']=$title;
            $rs=$m->add($data);
            if($rs)
                $this->success ("操作成功",U("Gongzhang/index"));
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $step = $_REQUEST['step'];
        $step = empty($step) ? 1 : $step;
        if ($step == 1) {
            #工长
            $M = M("Foremanview");
            $list = $M->where("1")->field("a_id,a_name,truename")->select();
            $this->assign("list", $list);
            $this->assign("step", 2);
            $this->display();
        } else {
            $gzid = $_GET['gzid'];
            $this->assign("gzid", $gzid);
            $this->assign("step", $step);
            $conf = include_once './Common/config2.php';
            $jd = $conf['zxjd'];
            $this->assign("jd", $jd);
            $this->assign("hxlist", $this->gethxlist()); #户型分类
            $this->assign("tpjhlist", $this->gettpjhlist()); #图片集合分类
            $ins = $this->getprovcity($gzid);
            $this->assign("p_id", $ins['p_id']);
            $this->assign("c_id", $ins['c_id']);

            $this->display("addsgdt2");
        }
    }

    /**
     * 编辑施工动态
     */
    public function editsgdt() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->display("addsgdt");
    }

    /**
     * 删除施工动态
     */
    public function delsgtd() {
        
    }

    /**
     * ajax 获取工长
     */
    public function ajaxgetgz() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = trim($_POST['gname']);
        if (!empty($gname)) {
            $M = M("Foremanview");
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

    /**
     * 图片集合分类
     */
    public function tpjh() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        if (IS_POST) {
            $tpname = trim($_POST['tpname']);
            if (empty($tpname)) {
                $this->error("图片集合分类不能为空");
                exit;
            }
            #检查是否存在
            $M = M("Tpjh");
            $cou = $M->where("name ='" . $tpname . "'")->count();
            if ($cou > 0) {
                #存在
                $this->error("图片集合分类已经存在！");
                exit;
            }
            $rs = $M->add(array("name" => $tpname));
            if ($rs) {
                $this->success("操作成功!");
                exit;
            } else {
                $this->error("操作失败!");
                exit;
            }
        }
        $M1 = M("Tpjh");
        $list = $M1->where(1)->select();
        $this->assign("list", $list);
        $this->display();
    }

    /**
     * 删除图片集合分类
     */
    public function tpjhdel() {
        $id = $_GET['id'];
        $M1 = M("Tpjh");
        $rs = $M1->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败!");
    }

    /**
     * ajax
     * 改变图片集合名称
     */
    public function ajaxupdatejh() {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $m = M("Tpjh");
        $rr = $m->where("id=" . $id)->save(array("name" => $name));

        if ($rr)
            echo 1;
        else
            echo 0;
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
     * 户型
     */
    private function gethxlist() {
        $hxmod = M("Hxcategory");
        $hxlist = $hxmod->where(1)->select();
        return $hxlist;
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

    /**
     * 根据用户id 获取用户所属的省市
     * 
     */
    private function getprovcity($uid) {
        $m = M("Foremanview");
        $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        return $info;
    }

}

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
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $step = $_REQUEST['step'];
        $step = empty($step) ? 1 : $step;
        if ($step == 1) {
            #工长
            $M = M("Foremanview");
            $list = $M->where("1")->field("a_id,a_name,truename")->select();
            $this->assign("list", $list);
            $this->assign("step",2);
            $this->display();
        }else{
            $gzid=$_GET['gzid'];
            $this->assign("gzid",$gzid);
            $this->assign("step",$step);
            $conf=  include_once './Common/config2.php';
            $jd=$conf['zxjd'];
            $this->assign("jd",$jd);
            $hxmod=M("Hxcategory");
            $hxlist=$hxmod->where(1)->select();
            $this->assign("hxlist",$hxlist);
            
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
    public function tpjh(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        if(IS_POST){
            $tpname=trim($_POST['tpname']);
            if(empty($tpname)){
                $this->error("图片集合分类不能为空");exit;
            }
            #检查是否存在
            $M=M("Tpjh");
            $cou=$M->where("name ='".$tpname."'")->count();
            if($cou>0){
                #存在
                $this->error("图片集合分类已经存在！");
                exit;
            }
            $rs=$M->add(array("name"=>$tpname));
            if($rs){
                $this->success("操作成功!");exit;
            }else{
                $this->error("操作失败!");exit;
            }
        }
        $M1=M("Tpjh");
        $list=$M1->where(1)->select();
        $this->assign("list",$list);
        $this->display();
    }
    /**
     * 删除图片集合分类
     */
    public function tpjhdel(){
        $id=$_GET['id'];
        $M1=M("Tpjh");
        $rs=$M1->where("id=".$id)->delete();
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败!");
    }
    /**
     * ajax
     * 改变图片集合名称
     */
    public function ajaxupdatejh(){
        $id=$_POST['id'];
        $name=$_POST['name'];
        $m=M("Tpjh");
        $rr=$m->where("id=".$id)->save(array("name"=>$name));
       
        if($rr)
            echo 1;
        else
            echo 0;
    }
}

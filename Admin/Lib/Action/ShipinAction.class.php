<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShipinAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShipinAction extends CommonAction {
    /**
     * 视频列表
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $this->display();
    }
    /**
     * 添加视频
     */
    public function add_shipin(){
        if(IS_POST){
            $gzid=$_POST['gzid'];
            $title=trim($_POST['title']);
            $description=trim($_POST['description']);
            $keywords=trim($_POST['keywords']);
            $click=  trim($_POST['click']);
            $status=$_POST['status'];
            $spcontent=$_POST['spcontent'];
            if(empty($title)){
                $this->error("标题不能为空！");
                exit;
            }
            if($this->check_shipin($title)){
                $this->error("标题已经存在！");
                exit;
            }
            $imginfo=  $this->upload2();
            if(!empty($imginfo)){
                if(!empty($imginfo[0]['savename']))
                $spurl="/Uploads/shipin/".$imginfo[0]['savename'];
            }
            
            $spcontent=  trim($_POST['spcontent']);
            if(empty($spurl)&&empty($spcontent)){
                $this->error("请上传视频");exit;
            }
            $pc=  $this->getprovcity($gzid);
            $data=array();
            $data['title']=$title;
            $data['spcontent']=$spcontent;
            $data['spurl']=$spurl;
            $data['keywords']=$keywords;
            $data['description']=$description;
            $data['addtime']=  time();
            $data['click']=$click;
            $data['uid']=$gzid;
            $data['p_id']=$pc['p_id'];
            $data['c_id']=$pc['c_id'];
            $data['status']=$status;
            $data['adduid']=$_SESSION['my_info']['a_id'];
            $M=M("Shipin");
            $rs=$M->add($data);
            if($rs)
                $this->success ("操作成功！",U("Shipin/index"));
            else
                $this->error ("操作失败!");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $conf=  include './Common/config2.php';
        $inf=$conf['memtp'];
        $this->assign("tplist",$inf);
        $this->display();
    }
    /**
     * 编辑视频
     */
    public function edit_shipin(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $this->display("add_shipin");
    }
    /**
     * 删除视频
     */
    public function del_shipin(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $this->display();
    }
    //------------------private
    private function check_shipin($title){
        $M=M("Shipin");
        $cou=$M->where("title like '%".$title."%'")->count();
        if($cou>0){
            return true;
        }else{
            return FALSE;
        }
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
    //------------------ajax
    
}

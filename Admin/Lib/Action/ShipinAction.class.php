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
        $is_qx=$this->getqx($_SESSION['my_info']['role']);
        
        $where="1";
        if($is_qx==1){
            $where.=" and p_id=".$_SESSION['my_info']['proid'];
            $where.=" and c_id=".$_SESSION['my_info']['cityid'];
        }else{
            
            $citymod=new CityModel();
            $plist=$citymod->getprovince(1);
            $this->assign("plist",$plist);
            
            $p_id=$_GET['p_id'];
            $c_id=$_GET['c_id'];
            
            if(!empty($p_id))
                $where.=" and p_id=".$p_id;
            if(!empty($c_id))
                $where.=" and c_id=".$c_id;
            $this->assign("p_id",$p_id);
            $this->assign("c_id",$c_id);
            $this->assign("cname",  $citymod->getname($c_id));
        }
        $this->assign("is_qx",$is_qx);
        
        $keys=$_GET['keys'];
        $keys=$keys=="请输入关键字"?"":$keys;
        $uid=$_GET['uid'];
        if(!empty($keys))
            $where.=" and title like '%".$keys."%'";
        if(!empty($uid))
            $where.=" and uid=".$uid;
        $this->assign("uid",$uid);
        $this->assign("keys",$keys);
        
        $M=M("Shipin");
        $cou=$M->where($where)->count();
        import("ORG.Util.Page");
        $p=new Page($cou,10);
        $list=$M->where($where)->limit($p->firstRow.",".$p->listRows)->order("addtime desc")->select();
        
        $arr=array("未审核","已审核");
        foreach ($list as $k=>$v){
            $list[$k]['status_f']=$arr[$v['status']];
        }
        $this->assign("list",$list);
        $this->assign("page",$p->show());
        $conf=include './Common/config2.php';
        $memtype=$conf['memtp'];
        $this->assign("tplist",$memtype);
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
            $fengmian=$_POST['fengmian'];
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
            $data['fmimg']=$fengmian;
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
        if(IS_POST){
            $id=$_POST['id'];
            $title=  trim($_POST['title']);
            $description=  trim($_POST['description']);
            $keywords=  trim($_POST['keywords']);
            $click=  trim($_POST['click']);
            $status=  trim($_POST['status']);
            $spcontent=  trim($_POST['spcontent']);
            $imginf=$this->upload2();
            if(empty($spcontent)&&empty($imginf)){
                $this->error("请上传视频！");exit;
            }
            if(!empty($imginf[0]['savename']))
                $spstr="/Uploads/shipin/".$imginf[0]['savename'];
            $fengmian=$_POST['fengmian'];
            $M=M("Shipin");
            $info1=$M->where("id=".$id)->find();
            $data=array();
            if($info1['title']!=$title)
                $data['title']=$title;
            if($info1['description']!=$description)
                $data['description']=$description;
            if($info1['keywords']!=$keywords)
                $data['keywords']=$keywords;
            if($info1['click']!=$click)
                $data['click']=$click;
            if($info1['status']!=$status)
                $data['status']=$status;
            if($info1['spcontent']!=$spcontent)
                $data['spcontent']=$spcontent;
            if($info1['spurl']!=$spstr)
                $data['spurl']=$spstr;
            if($info1['fmimg']!=$fengmian)
                $data['fmimg']=$fengmian;
            $rs=$M->where("id=".$id)->save($data);
            if($rs)
                $this->success ("操作成功！",U("Shipin/index"));
            else
                $this->error ("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
        $id=$_GET['id'];
        $M=M("Shipin");
        $info=$M->where("id=".$id)->find();
        $this->assign("info",$info);
        $this->assign("uname",$this->getnamebyid($info['uid']));
        $this->assign("is_edit",1);
        $this->display("add_shipin");
    }
    /**
     * 删除视频
     */
    public function del_shipin(){
        $id=$_GET['id'];
        $M=M("Shipin");
        $info=$M->where("id=".$id)->find();
        if(!empty($info['spurl']))
            unlink (".".$info['spurl']);
        if(!empty($info['fmimg']))
            unlink (".".$info['fmimg']);
        $rs=$M->where("id=".$id)->delete();
        if($rs)
            $this->success ("删除成功！");
        else
            $this->error ("删除失败！");
    }
    /**
     * 修改状态
     */
    public function edit_status(){
        $id=$_GET['id'];
        $status=$_GET['status'];
        if($status==0)
            $status_u=1;
        else
            $status_u=0;
        $M=M("Shipin");
        $rs=$M->where("id=".$id)->save(array('status'=>$status_u));
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
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
    /**
     * 根据uid
     * 获取真实姓名和会员名
     */
    private function getnamebyid($uid) {
        $M2 = M("Member");
        $info1 = $M2->where("a_id=" . $uid)->find();
        if ($info1['a_type'] == 1) {
            //普通
            $m = M("Kehuview");
            $info = $m->where("a_id=" . $uid)->field("truename,a_name")->find();
        } elseif ($info1['a_type'] == 2) {
            //工长
            $m = M("Foremanview");
            $info = $m->where("a_id=" . $uid)->field("truename,a_name")->find();
        } elseif ($info1['a_type'] == 3) {
            //店铺
            $m = M("Dianpumember");
            $info = $m->where("a_id=" . $uid)->field("a_name,company as truename")->find();
        } elseif ($info1['a_type'] == 4) {
            //设计
            $m = M("Shejiview");
            $info = $m->where("a_id=" . $uid)->field("a_name,truename")->find();
        } else {
            //工人
            $m = M("Gongrenview");
            $info = $m->where("a_id=" . $uid)->field("a_name,truename")->find();
        }
        $str = $info['a_name'] . "-" . $info['truename'];
        return $str;
    }
    //------------------ajax
    
}

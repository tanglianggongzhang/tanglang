<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JiamengAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class JiamengAction extends CommonAction {
    /**
     * 加盟列表
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",  $this->systemConfig);
      
        $citymod = new CityModel();
        

        $province=$_GET['province'];
        $city=$_GET['city'];
        $where="1";
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            //非地区管理员
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);

            if (!empty($province)) {
                $where.=" and p_id=" . $province;
            }
            if (!empty($city)) {
                $where.=" and c_id=" . $city;
            }
            $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));
            $this->assign("province",$province);
            $this->assign("city",$city);
            $cityname=$citymod->getname($city);
            $this->assign("cityname",$cityname);
        } else {
            $where.=" and p_id=" . $_SESSION['my_info']['proid'];
            $where .= " and c_id=" . $_SESSION['my_info']['cityid'];
            $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));
        }
        $keys=$_GET['keys'];
        $keys=$keys=="请输入关键字"?"":$keys;
        $uid=$_GET['uid'];
        if(!empty($keys))
            $where.=" and company like '%".$keys."%'";
        
        $this->assign("keys",$keys);
        
        $M=M("Jiameng");
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
     * 删除加盟
     */
    public function del_jiameng(){
        $id=$_GET['id'];
        $M=M("Jiameng");
        $info=$M->where("id=".$id)->find();
        if(!empty($info['yingyezhizhao']))
            unlink (".".$info['yingyezhizhao']);
       
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
        $M=M("Jiameng");
        $rs=$M->where("id=".$id)->save(array('status'=>$status_u));
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }
    
    //------------------private
    
    
}

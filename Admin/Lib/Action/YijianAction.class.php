<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of YijianAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class YijianAction extends CommonAction {
    /**
     * 意见反馈列表
     */
    public function index(){
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        
        @import("ORG.Util.Page");
        $where="1";
        if ($is_qx == 0) {
            //非地区管理员
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            //省
            $mcity = new CityModel();
            $plist = $mcity->getprovince(1);
            $this->assign("plist", $plist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
            $this->assign("c_name", $mcity->getname($c_id));
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
        }
        if(!empty($p_id))
            $where.=" and Yijian.p_id=".$p_id;
        if(!empty($c_id))
            $where.=" and Yijian.c_id=".$c_id;
        $keys=trim($_GET['keys']);
        $keys=($keys=="请输入关键字")?"":$keys;
        $where.=" and Yijian.content like '%".$keys."%'";
        $this->assign("keys",$keys);
        $yjv=D("YijianView");
        $totalRows=$yjv->where($where)->count();
        $pm=new Page($totalRows,10);
        $list=$yjv->where($where)->order("Yijian.addtime desc")->limit($pm->firstRow.",".$pm->listRows)->select();
        $islx=array("0"=>"未联系","1"=>"已联系");
        foreach($list as $k=>$v){
            $list[$k]['addtimef']=date("Y-m-d H:i:s",$v['addtime']);
            $list[$k]['islxf']=$islx[$v['is_lx']];
        }
        $this->assign("list",$list);
        $this->assign("page",$pm->show());
        #echo $yjv->getLastSql();
        #省
        
        $this->display();
    }
    /**
     *
     * 修改状态
     */
    public function upstatus(){
        $id=$_GET['id'];
        $m=M("Yijian");
        $status=$_GET['status'];
        if($status==0)
            $statusu=1;
        if($status==1)
            $statusu=0;
        $res=$m->where("id=".$id)->save(array("is_lx"=>$statusu));
        if($res)
            $this->success ("操作成功！",U("Yijian/index"));
        else
            $this->error ('操作失败！');
    }
    /**
     * 删除
     */
    public function del(){
        $id=$_GET['id'];
        $m=M("Yijian");
        $res=$m->where("id=".$id)->delete();
        if($res)
            $this->success ("操作成功！",U("Yijian/index"));
        else
            $this->error ("操作失败！");
    }
}

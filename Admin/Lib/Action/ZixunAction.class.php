<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ZixunAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ZixunAction extends CommonAction {
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
                $where.=" and Zixun.p_id=" . $province;
            }
            if (!empty($city)) {
                $where.=" and Zixun.c_id=" . $city;
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
            $where.=" and Zixun.name like '%".$keys."%'";
        
        $this->assign("keys",$keys);
        
        $M=D("ZixunView");
        $cou=$M->where($where)->count();
        import("ORG.Util.Page");
        $p=new Page($cou,10);
        $list=$M->where($where)->limit($p->firstRow.",".$p->listRows)->order("Zixun.addtime desc")->select();
       
        $arr=array("未审核","已审核");
        foreach ($list as $k=>$v){
            $list[$k]['status_f']=$arr[$v['status']];
            $list[$k]['addtime_f']=date("Y-m-d",$v['addtime']);
        }
        $this->assign("list",$list);
        $this->assign("page",$p->show());
       
        $this->display();
    }
    
    /**
     * 删除咨询
     */
    public function del_zixun(){
        $id=$_GET['id'];
        $M=M("Zixun");
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
        $M=M("Zixun");
        $rs=$M->where("id=".$id)->save(array('status'=>$status_u));
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }
    
    //------------------private
    
    
}

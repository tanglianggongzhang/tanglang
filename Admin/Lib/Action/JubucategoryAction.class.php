<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HxcategoryAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class JubucategoryAction extends CommonAction {

    /**
     * 局部分类列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $hxmod=D("JubucategoryView");
        import("ORG.Util.Page");
        $keys=trim($_GET['keys']);
        if(!empty($keys))
        $where="Jubucategory.name like '%".$keys."%'";
        
        $totalRows=$hxmod->where($where)->order("Jubucategory.addtime desc")->count();
        $page=new Page($totalRows,10);
        $list=$hxmod->where($where)->order("Jubucategory.addtime desc")->limit($page->firstRow.",".$page->listRows)->select();
        
        $showpage=$page->show();
        foreach ($list as $k=>$v){
            $list[$k]['addtimef']=  date("Y-m-d H:i:s",$v['addtime']);
        }
       
        $this->assign("list",$list);
        
        $this->assign("page", $showpage);
        $this->assign("keys",$keys);
        $this->display();
    }

    /**
     * 添加局部
     */
    public function addjubucategory() {
        $a_id = ($_SESSION['my_info']['a_id']);
        if (IS_POST) {
            $name = $_POST['name'];
            $name = trim($name);
            if (empty($name)) {
                $this->error("请填写类型名称");
                exit;
            }
            $nmlist = explode("\n", $name);
            $m = M("Jubucategory");
            $i=0;
            foreach ($nmlist as $k => $v) {
                if (!empty($v)) {
                    $data = array(
                        "name" => $v,
                        "adduid" => $a_id,
                        "addtime" => time()
                    );
                    $res=$m->add($data);
                    if($res)
                        $i++;
                }
            }
            if($i){
                $this->success("成功添加了[".$i."]个局部",U("Jubucategory/index"));
            }else{
                $this->error("添加失败");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->display();
    }

    /**
     * 编辑局部
     */
    public function editjubucategory() {
        $m=M("Jubucategory");
        if(IS_POST){
            $id=$_POST['id'];
            $name=trim($_POST['name']);
            if(empty($name)){
                $this->error("名称不能为空！");
                exit;
            }
            $res=$m->where("id=".$id)->save(array("name"=>$name));
            if($res)
                $this->success ("操作成功！",U("Jubucategory/index"));
            else
                $this->error ("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id=$_GET['id'];
        
        $info=$m->where("id=".$id)->find();
        $this->assign("info",$info);
        $this->display("addjubucategory");
    }
    /**
     * 删除
     */
    public function del(){
        $id=$_GET['id'];
        $m=M("Jubucategory");
        $res=$m->where("id=".$id)->delete();
        if($res)
            $this->success ("操作成功！",U("Jubucategory/index"));
        else
            $this->error ("操作失败！");
    }
}

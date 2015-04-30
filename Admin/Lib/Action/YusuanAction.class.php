<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of YusuanAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class YusuanAction extends CommonAction {

    /**
     * 列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $m=M("Yusuan");
        import("ORG.Util.Page");
        $where=1;
        $keys=trim($_GET['keys']);
        $keys=$keys=="请输入关键字"?"":$keys;
        if(!empty($keys))
            $where="name like '%".$keys."%'";
        $totalRows=$m->where($where)->count();
        $p=new Page($totalRows,10);
        $list=$m->where($where)->limit($p->firstRow.",".$p->listRows)->select();
        $this->assign("list",$list);
        $this->assign("page",$p->show());
        $this->assign("keys",$keys);
        $this->display();
    }

    /**
     * 添加预算
     */
    public function addys() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        if (IS_POST) {
            $name = trim($_POST['name']);
            if (!empty($name)){
                $nlist=explode("\n", $name);
                $mod=M("Yusuan");
                $i=0;
                foreach ($nlist as $k=>$v){
                    $res=$mod->add(array("name"=>$v));
                    if($res)
                        $i++;
                }
                if($i)
                    $this->success ("添加【".$i."】条 预算成功!",U("Yusuan/index"));
                else
                    $this->error ("添加失败!");
                
            }else{
                $this->error("添加失败！");
            }
            exit;
        }

        $this->display();
    }

    /**
     * 编辑预算
     */
    public function editys() {
        if(IS_POST){
            $id=$_POST['id'];
            $name=trim($_POST['name']);
            $mod=M("Yusuan");
            $res=$mod->where("ysid=".$id)->save(array("name"=>$name));
            if($res)
                $this->success ("编辑成功!",U("Yusuan/index"));
            else
                $this->error ("编辑失败!");
            exit();
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id=$_GET['id'];
        $mod=M("Yusuan");
        $info=$mod->where("ysid=".$id)->find();
        $this->assign("info",$info);
        
        $this->display("addys");
    }

    /**
     * 删除预算
     */
    public function delys() {
        $id=$_GET['id'];
        $mod=M("Yusuan");
        $res=$mod->where("ysid=".$id)->delete();
        if($res)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }

}

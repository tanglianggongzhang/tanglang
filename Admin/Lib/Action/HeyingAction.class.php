<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HeyingAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class HeyingAction extends CommonAction {

    /**
     * 合影
     * 列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $M = D("HeyingView");
        $where = "";
        import("ORG.Util.Page");
        $cou = $M->where($where)->count();
        $p = new Page($cou, 10);

        $list = $M->where($where)->order("addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        foreach ($list as $k => $v) {
            $list[$k]['truename'] = $this->getgzname($v['uid']);
            $list[$k]['is_tj_f'] = $v['is_tj'] == 0 ? "否" : "是";
            $list[$k]['status_f'] = $v['status'] == 0 ? "未审核" : "已审核";
        }
        $this->assign("list", $list);
        $this->assign("page", $p->show());

        $this->display();
    }

    /**
     * 添加
     * 合影
     */
    public function add_hy() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh());
        if (IS_POST) {
            $gzid = $_POST['gzid'];
            $name = trim($_POST['name']);
            if (empty($name)) {
                $this->error("合影名称不能为空！");
                exit;
            }
            $info = $this->upload();
            if (!empty($info)) {
                $imgn = "/Uploads/product/" . $info[0]['savename'];
            }
            $is_tj = $_POST['is_tj'];
            $status = $_POST['status'];
            $aid = $_SESSION['my_info']['a_id'];
            $pc = $this->getprovcity($gzid);
            $p_id = $pc['p_id'];
            $c_id = $pc['c_id'];
            $M = M("Heying");
            $data = array(
                "uid" => $gzid,
                "adduid" => $aid,
                "img" => $imgn,
                "name" => $name,
                "is_tj" => $is_tj,
                "p_id" => $p_id,
                "c_id" => $c_id,
                "addtime" => time(),
                "status" => $status
            );
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！", U('Heying/index'));
            else
                $this->error("操作失败!");
            exit;
        }


        $this->display();
    }

    /**
     * 编辑
     * 合影
     */
    public function edit_hy() {
        if (IS_POST) {
            $id = $_POST['id'];
            $M=M("Heying");
            $info=$M->where("id=".$id)->find();
            $gzid = $_POST['gzid'];
            $pc=  $this->getprovcity($gzid);
            $name = trim($_POST['name']);
            if(empty($name)){
                $this->error("合影名称不能为空！");
                exit;
            }
            $is_tj = $_POST['is_tj'];
            $status = $_POST['status'];
            $imgf = $this->upload();
            if (!empty($imgf[0]['savename'])) {
                $img="/Uploads/product/".$imgf[0]['savename'];
                unlink($info['img']);
            }
            $data=array();
            $data['uid']=$gzid;
            $data['adduid']=$_SESSION['my_info']['a_id'];
            $data['img']=$img;
            $data['name']=$name;
            $data['is_tj']=$is_tj;
            $data['p_id']=$pc[p_id];
            $data['c_id']=$pc[c_id];
            $data['addtime']=  time();
            $data['status']=$status;
            $rs=$M->where("id=".$id)->save($data);
            if($rs)
                $this->success ("操作成功！",U("Heying/index"));
            else
                $this->error ("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Heying");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $this->assign("gzlist", $this->getgzh());
        $this->display("add_hy");
    }

    /**
     * 删除
     * 合影
     */
    public function del_hy() {
        $id=$_GET['id'];
        $M = M("Heying");
        $info = $M->where("id=" . $id)->find();
        if(!empty($info['img'])){
            unlink(".".$info['img']);
        }
        $rs=$M->where("id=".$id)->delete();
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }

    /**
     * 修改状态
     */
    public function xgtatus() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 0)
            $status_u = 1;
        else
            $status_u = 0;
        $M = M("Heying");
        $rs = $M->where("id=" . $id)->save(array("status" => $status_u));
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败!");
    }

    /**
     * 修改推荐
     */
    public function xgis_tj() {
        $id = $_GET['id'];
        $is_tj = $_GET['is_tj'];
        if ($is_tj == 0)
            $is_tju = 1;
        $M = M("Heying");
        $rs = $M->where("id=" . $id)->save(array("is_tj" => $is_tju));
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败!");
    }

    //------------------------------------------
    /**
     * 获取工长列表
     * @return type
     */
    private function getgzh() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $where = "1";
        if ($is_qx == 1) {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];

            $where.=" and p_id=" . $p_id;
            $where.=" and c_id=" . $c_id;
        }
        $M = M("Foremanview");
        $list = $M->where($where)->field("a_id,a_name,truename")->select();
        return $list;
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

    /**
     * 根据用户id
     * 获取工长名称
     */
    private function getgzname($uid) {
        $M = M("ForemanInfo");
        $info = $M->where("a_id=" . $uid)->field("truename")->find();

        return $info['truename'];
    }

}

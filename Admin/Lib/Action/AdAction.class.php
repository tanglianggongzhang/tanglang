<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class AdAction extends CommonAction {

    /**
     * 位置列表
     */
    function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);

        $hxmod = D("AdweizhiView");
        import("ORG.Util.Page");
        $keys = trim($_GET['keys']);
        if (!empty($keys))
            $where = "Adweizhi.name like '%" . $keys . "%'";

        $totalRows = $hxmod->where($where)->order("Adweizhi.addtime desc")->count();
        $page = new Page($totalRows, 10);
        $list = $hxmod->where($where)->order("Adweizhi.addtime desc")->limit($page->firstRow . "," . $page->listRows)->select();

        $showpage = $page->show();
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i:s", $v['addtime']);
        }

        $this->assign("list", $list);

        $this->assign("page", $showpage);
        $this->assign("keys", $keys);

        $this->display();
    }

    /**
     * 添加位置
     */
    function addwz() {
        if (IS_POST) {
            $name = trim($_POST['name']);
            $price = trim($_POST['price']);
            $width = trim($_POST['width']);
            $height = trim($_POST['height']);
            $a_id = $_SESSION['my_info']['a_id'];
            if (empty($name)) {
                $this->error("位置名称不能为空！");
                exit;
            }

            if (empty($width)) {
                $this->error("图片款不能为空！");
                exit;
            }
            if (empty($height)) {
                $this->error("图片高不能为空！");
                exit;
            }
            //上传位置图片
            $imginf = $this->upload("./Uploads/wz/");
            $wzimg = "/Uploads/wz/" . $imginf[0]['savename'];

            $price = empty($price) ? 0 : $price;
            $data = array("name" => $name, "addtime" => time(), "price" => $price, "width" => $width, "height" => $height, "adduid" => $a_id, "wzimg" => $wzimg);
            $m = M("Adweizhi");
            $res = $m->add($data);
            if ($res)
                $this->success("操作成功", U("Ad/index"));
            else
                $this->error("操作失败");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->display();
    }

    /**
     * 编辑位置
     */
    public function editwz() {
        $hxmod = D("AdweizhiView");
        if (IS_POST) {
            $name = trim($_POST['name']);
            $width = trim($_POST['width']);
            $height = trim($_POST['height']);
            $price = trim($_POST['price']);
            $a_id = $_SESSION['my_info']['a_id'];
            if (empty($name)) {
                $this->error("名称不能为空");
                exit;
            }
            if (empty($width)) {
                $this->error("图片宽度不能为空");
                exit;
            }
            if (empty($height)) {
                $this->error("图片高度不能为空");
                exit;
            }
            if ($_FILES['wzimg']['name'] != '') {
                $imginfo = $this->upload('./Uploads/wz/');
                if (!empty($imginfo[0]['savename']))
                    $logo = "/Uploads/wz/" . $imginfo[0]['savename'];
                else
                    $logo = "";
            }
            $data = array();
            if (!empty($logo))
                $data['wzimg'] = $logo;
            if (!empty($name))
                $data['name'] = $name;
            if (!empty($price))
                $data['price'] = $price;
            if (!empty($width))
                $data['width'] = $width;
            if (!empty($height))
                $data['height'] = $height;
            $data['addtime'] = time();
            $data['adduid'] = $a_id;
            $m = M("Adweizhi");
            $id = $_POST['id'];
            $res = $m->where("id=" . $id)->save($data);
            if ($res)
                $this->success("操作成功", U("Ad/index"));
            else
                $this->error("操作失败");
            exit;
        }

        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);

        $id = $_GET['id'];
        $info = $hxmod->where("id=" . $id)->find();
        $this->assign("info", $info);
        $this->display("addwz");
    }

    /**
     * 广告列表
     */
    public function adlist() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $M = M("Adview");
        $where = "1";
        $keys=$_GET['keys'];
        $keys=$keys=="请输入关键字"?"":$keys;
        //省
        $is_qx=$this->getqx($_SESSION['my_info']['role']);
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
        $this->assign("is_qx",$is_qx);
        if (!empty($p_id))
            $where .= " and p_id = '" . $p_id . "'";

        if (!empty($c_id))
            $where .= " and c_id = '" . $c_id . "'";
        if(!empty($keys))
            $where.=" and title like '%".$keys."%'";
        $this->assign("keys",$keys);
        $cou = $M->where($where)->count();
        @import("ORG.Util.Page");
        $page = new Page($cou, 10);
        $list = $M->where($where)->order("addtime desc")->limit($page->firstRow . "," . $page->listRows)->select();
        foreach ($list as $k => $v) {
            $list[$k]['addtimef'] = date("Y-m-d H:i:s", $v['addtime']);
        }
        $this->assign("list", $list);
        $showpage = $page->show();
        $this->assign("page", $showpage);
        
        
        
        $this->display();
    }

    /**
     * 添加广告
     */
    public function addad() {
        if (IS_POST) {
            $title = trim($_POST['title']);
            $link = trim($_POST['link']);
            $weizhi = trim($_POST['weizhi']);
            $p_id = trim($_POST['p_id']);
            $c_id = trim($_POST['c_id']);
            if (empty($title)) {
                $this->error("名称不能为空！");
                exit;
            }
            if (empty($weizhi)) {
                $this->error("位置不能为空！");
                exit;
            }

            if ($this->getqx($_SESSION['my_info']['role']) == 0) {
                //非地区管理员
                if (empty($p_id)) {
                    $this->error("请选择省");
                    exit;
                }
                if (empty($c_id)) {
                    $this->error("请选择市");
                    exit;
                }
            } else {
                $p_id = $_SESSION['my_info']['proid'];
                $c_id = $_SESSION['my_info']['cityid'];
                if (empty($p_id)) {
                    $this->error("该用户没有省的权限");
                    exit;
                }
                if (empty($c_id)) {
                    $this->error("该用户没有市的权限");
                    exit;
                }
            }
            $adimg = $this->upload("./Uploads/ad/");
            if (empty($adimg[0]['savename'])) {
                $img = "";
            } else {
                $img = "/Uploads/ad/" . $adimg[0]['savename'];
            }

            $data = array(
                "title" => $title,
                "img" => $img,
                "link" => $link,
                "addtime" => time(),
                "weizhi" => $weizhi,
                "p_id" => $p_id,
                "c_id" => $c_id,
                "adduid" => $_SESSION['my_info']['a_id']
            );
            $M = M("Ad");
            $res = $M->add($data);
            if ($res) {
                $this->success("操作成功!", U("Ad/adlist"));
            } else {
                $this->error("操作失败！");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        //获取位置
        $wzm = M("Adweizhi");
        $wzlist = $wzm->where("1")->order("addtime desc")->select();
        $this->assign("wzlist", $wzlist);
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            //非地区管理员
            #读取省
            $mcity = new CityModel();
            $plist = $mcity->getprovince(1);
            $this->assign("plist", $plist);
        }
        $is_qx=$this->getqx($_SESSION['my_info']['role']);
        $this->assign("is_qx", $is_qx);

        $this->display();
    }

    /**
     * 编辑广告
     */
    public function editad() {
        
        if (IS_POST) {
            $id=$_POST['id'];
            $M = M("Adview");
            $info = $M->where("id=" . $id)->find();
            $title = trim($_POST['title']);
            $link = trim($_POST['link']);
            $weizhi = $_POST['weizhi'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            if (empty($title)) {
                $this->error("名称不能为空！");
                exit;
            }
            if (empty($weizhi)) {
                $this->error("请选择位置！");
                exit;
            }
            if ($this->getqx($_SESSION['my_info']['role']) == 0) {
                //非地区管理员
                if (empty($p_id)) {
                    $this->error("请选省！");
                    exit;
                }
                if (empty($c_id)) {
                    $this->error("请选市！");
                    exit;
                }
            } else {
                $p_id = $_SESSION['my_info']['proid'];
                $c_id = $_SESSION['my_info']['cityid'];
                if (empty($p_id)) {
                    $this->error("该管理员没有选择省！");
                    exit;
                }
                if (empty($c_id)) {
                    $this->error("该管理员没有选择市！");
                    exit;
                }
            }
            if (!empty($_FILES['img']['name'])) {

                $imgsrc = $this->upload("./Uploads/ad/");
                if (empty($imgsrc[0]['savename']))
                    $img = "";
                else
                    $img = "/Uploads/ad/".$imgsrc[0]['savename'];
                if(!empty($info['img']))
                    unlink ($info['img']);
                
            }
            $data=array();
            if(!empty($img))
                $data['img']=$img;
            if(!empty($link))
                $data['link']=$link;
            if(!empty($title))
                $data['title']=$title;
            if(!empty($weizhi))
                $data['weizhi']=$weizhi;
            if(!empty($p_id))
                $data['p_id']=$p_id;
            if(!empty($c_id))
                $data['c_id']=$c_id;
            $data['addtime']=  time();
            $data['adduid']=$_SESSION['my_info']['a_id'];
            $res=M("Ad")->where("id=".$id)->save($data);
            if($res)
                $this->success ("操作成功",U("Ad/adlist"));
            else
                $this->error ("操作失败");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Adview");
        $info = $M->where("id=" . $id)->find();
        //位置
        $wzm = M("Adweizhi");
        $wzlist = $wzm->where("1")->order("addtime desc")->select();
        $this->assign("wzlist", $wzlist);
        //省
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            //非地区管理员
            $mcity = new CityModel();
            $plist = $mcity->getprovince(1);
            $this->assign("plist", $plist);
            $this->assign("c_id", $info['c_id']);
            $this->assign("c_name", $info['c_name']);
        }
        $this->assign("info", $info);
        $is_qx=$this->getqx($_SESSION['my_info']['role']);
        $this->assign("is_qx", $is_qx);
        
        $this->display("addad");
    }

    /**
     * 删除广告
     */
    public function delad() {
        
    }

}

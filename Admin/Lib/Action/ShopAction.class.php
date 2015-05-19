<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShopAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShopAction extends CommonAction {

    /**
     * 商品列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $citymod = new CityModel();
        if ($is_qx) {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            #区列表
            $qlist = $citymod->getcity($c_id);
            $this->assign("qlist", $qlist);
        } else {
            #省列表
            $plist = $citymod->getprovince(1);
            $this->assign("plist", $plist);
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
        }
        $q_id = $_GET['q_id'];
        $where = "1";
        if (!empty($p_id)) {
            $where.=" and p_id=" . $p_id;
        }
        if (!empty($c_id)) {
            $where.=" and c_id=" . $c_id;
        }
        if (!empty($q_id)) {
            $where.=" and q_id=" . $q_id;
        }
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("c_name", $citymod->getname($c_id));
        $this->assign("q_id", $q_id);
        $this->assign("q_name", $citymod->getname($q_id));
        $istj = $_GET['istj'];
        if (!empty($istj)) {

            $where.=" and is_tj=" . $istj;
            $istj1 = $istj;
            $this->assign("istj1", $istj1);
        }
        $uid = $_GET['uid'];
        if (!empty($uid)) {
            $where.=" and uid=" . $uid;
            $this->assign("uid", $uid);
        }
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        if (!empty($keys)) {
            $where.=" and name like '%" . $keys . "%'";
            $this->assign("keys", $keys);
        }
        $M = M("Goodsview");
        $cou = $M->where($where)->count();
        import("ORG.Util.Page");
        $p = new Page($cou, 10);
        $list = $M->where($where)->limit($p->firstRow . "," . $p->listRows)->order("addtime desc")->select();
        #推荐
        $conf = include './Common/config2.php';
        $istj = $conf['goodstj'];
        $this->assign("istj", $istj);
        #商城
        $sclist = $this->getgzh();
        $this->assign("sclist", $sclist);


        foreach ($list as $k => $v) {
            $list[$k]['tjjb'] = $istj[$v['is_tj']];
        }
        $this->assign("list", $list);
        $this->assign("page", $p->show());

        $this->display();
    }

    /**
     * 添加商品
     */
    public function add_goods() {
        if (IS_POST) {
            $name = $_POST['name'];
            $hdtitle = $_POST['hdtitle'];
            $hdztlink = $_POST['hdztlink'];
            $fenshu = $_POST['fenshu'];
            $comments = $_POST['comments'];
            $price = $_POST['price'];
            $ckprice = $_POST['ckprice'];
            $content = $_POST['content'];
            $fmimg = $_POST['fengmian'];
            $is_tj = $_POST['is_tj'];
            $kucun = $_POST["kucun"];
            $uid = $_POST['gzid'];
            $pc = $this->getprocity($uid);
            $p_id = $pc['p_id'];
            $c_id = $pc['c_id'];
            $q_id = $pc['q_id'];
            $classid = $_POST['classid'];
            $img = $this->upload();
            $status = $_POST['status'];
            $str = array();
            if (empty($name)) {
                $this->error("名称不能为空！");
                exit;
            }
            if ($this->check_goodname($name)) {
                $this->error("名称已经存在！");
                exit;
            }
            foreach ($img as $k => $v) {
                $str[] = "/Uploads/product/" . $v['savename'];
            }
            if (!empty($str)) {
                $imgstr = json_encode($str);
            }
            $data = array(
                "name" => $name,
                "hdtitle" => $hdtitle,
                "hdztlink" => $hdztlink,
                "fenshu" => $fenshu,
                "comments" => $comments,
                "imgsrc" => $imgstr,
                "price" => $price,
                "ckprice" => $ckprice,
                "content" => $content,
                "fmimg" => $fmimg,
                "is_tj" => $is_tj,
                "kucun" => $kucun,
                "p_id" => $p_id,
                "c_id" => $c_id,
                "q_id" => $q_id,
                "uid" => $uid,
                "adduid" => $_SESSION['my_info']['a_id'],
                "addtime" => time(),
                "status" => $status,
                "classid" => $classid
            );
            $M = M("Goods");
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！");
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $cof = include './Common/config2.php';
        $goodstj = $cof['goodstj'];
        $this->assign("goodstj", $goodstj); #推荐
        $this->assign("sjlist", $this->getgzh()); #店铺列表
        $cmod = new ShopcategoryModel();
        $splist = $cmod->getcategory();
        $this->assign("splist", $splist);

        $this->display();
    }

    /**
     * 编辑商品
     */
    public function edit_goods() {
        if (IS_POST) {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            if (empty($name)) {
                $this->error("名称不能为空！");
                exit;
            }
            $classid = $_POST['classid'];
            if (empty($classid)) {
                $this->error("请选择所属分类！");
                exit;
            }
            $status = $_POST['status'];

            $hdtitle = trim($_POST['hdtitle']);

            $hdztlink = trim($_POST['hdztlink']);
            $img = $this->upload();
            $imgr = array();
            if (!empty($img)) {
                foreach ($img as $K => $v) {
                    if (!empty($v['savename']))
                        $imgr[] = "/Uploads/product/" . $v['savename'];
                }
                $imgstr = json_encode($imgr);
            }


            $price = trim($_POST['price']);
            $ckprice = trim($_POST['ckprice']);
            $content = trim($_POST['content']);
            $fengmian = $_POST['fengmian'];
            $is_tj = $_POST['is_tj'];
            $kucun = trim($_POST['kucun']);
            $comments = trim($_POST['comments']);
            $M = M("Goods");
            $info1 = $M->where("id=" . $id)->find();
            $data = array();
            if ($info1['name'] != $name)
                $data['name'] = $name;
            if ($info1['classid'] != $classid)
                $data['classid'] = $classid;
            if ($info1['hdtitle'] != $hdtitle)
                $data['hdtitle'] = $hdtitle;
            if ($info1['hdztlink'] != $hdztlink)
                $data['hdztlink'] = $hdztlink;
            if ($info1['fenshu'] != $fenshu)
                $data['fenshu'] = $fenshu;
            if ($info1['comments'] != $comments)
                $data['comments'] = $comments;
            if (!empty($imgstr))
                $data['imgsrc'] = $imgstr;
            if ($price != $info1['price'])
                $data['price'] = $price;
            if ($ckprice != $info1['ckprice'])
                $data['ckprice'] = $ckprice;
            if ($content != $info1['content'])
                $data['content'] = $content;
            if ($fengmian != $info1['fmimg'])
                $data['fmimg'] = $fengmian;
            if ($is_tj != $info1['is_tj'])
                $data['is_tj'] = $is_tj;
            if ($kucun != $info1['kucun'])
                $data['kucun'] = $kucun;
            $data['addtime'] = time();
            $data['status'] = $status;
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U("Shop/index"));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Goodsview");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $this->assign("is_edit", 1);
        $cmod = new ShopcategoryModel();
        $splist = $cmod->getcategory();
        $this->assign("splist", $splist);
        $imgsrc = json_decode($info['imgsrc']);
        $this->assign("imgsrc", $imgsrc);

        $cof = include './Common/config2.php';
        $goodstj = $cof['goodstj'];
        $this->assign("goodstj", $goodstj); #推荐
        $this->display("add_goods");
    }

    /**
     * 删除商品
     */
    public function del_goods() {
        $id = $_GET['id'];
        $M = M("Goods");
        $inf = $M->where("id=" . $id)->find();
        if (!empty($inf['fmimg'])) {
            unlink("." . $inf['fmimg']);
        }
        if (!empty($inf['imgsrc'])) {
            $infarr = json_decode($inf['imgsrc']);
            foreach ($infarr as $k => $v) {
                unlink("." . $v);
            }
        }
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 套餐列表
     */
    public function taocan() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");

        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $citymod = new CityModel();
        if ($is_qx) {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            #区列表
            $qlist = $citymod->getcity($c_id);
            $this->assign("qlist", $qlist);
        } else {
            #省列表
            $plist = $citymod->getprovince(1);
            $this->assign("plist", $plist);
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
        }
        $q_id = $_GET['q_id'];
        $where = "1";
        if (!empty($p_id)) {
            $where.=" and p_id=" . $p_id;
        }
        if (!empty($c_id)) {
            $where.=" and c_id=" . $c_id;
        }
        if (!empty($q_id)) {
            $where.=" and q_id=" . $q_id;
        }
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("c_name", $citymod->getname($c_id));
        $this->assign("q_id", $q_id);
        $this->assign("q_name", $citymod->getname($q_id));
        $u_id = $_GET['uid'];
        if (!empty($u_id)) {
            $where.=" and uid=" . $u_id;
        }
        $this->assign("uid", $u_id);
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        if (!empty($keys))
            $where.=" and tcname like '%" . $keys . "%'";
        $this->assign("keys", $keys);
        $M = M("Taocanview");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $this->assign("page", $p->show());
        $list = $M->where($where)->order("id desc")->limit($p->firstRow . "," . $p->listRows)->select();
        $this->assign("list", $list);
        #商城列表
        $splist = $this->getgzh();
        $this->assign("sclist", $splist);

        $this->display();
    }

    /**
     * 添加套餐
     */
    public function add_taocan() {
        if (IS_POST) {
            $tcname = trim($_POST['tcname']);
            if (empty($tcname)) {
                $this->error("套餐名称不能为空！");
                exit;
            }
            if ($this->checktaocan($tcname)) {
                $this->error("套餐名称已经存在！");
                exit;
            }
            $uid = $_POST['uid'];
            $price = $_POST['price'];
            $goodsid = $_POST['goodsid'];

            $zuhe = array();
            foreach ($price as $k => $v) {
                $zuhe[$goodsid[$k]] = $v;
            }
            $zuhearr = json_encode($zuhe);
            $cou = count($zuhe);

            if ($cou < 2) {
                $this->error("套餐组合失败！");
                exit;
            }
            ksort($zuhe);

            $idarr = array();
            foreach ($zuhe as $k => $v) {
                $idarr[] = $k;
            }
            $idzuhe = implode(",", $idarr);
            $pc = $this->getprocity($uid);
            if ($this->checkzuhe($idzuhe)) {
                $this->error("组合已经存在！");
                exit;
            }

            $data = array(
                "tcname" => $tcname,
                "zuhe" => $zuhearr,
                "idzuhe" => $idzuhe,
                "p_id" => $pc['p_id'],
                "c_id" => $pc['c_id'],
                "uid" => $uid,
                "adduid" => $_SESSION['my_info']['a_id'],
                "addtime" => time()
            );
            $M = M("Taocan");
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！", U('Shop/taocan'));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("sjlist", $this->getgzh()); #店铺列表
        $step = $_GET['step'];
        $step = empty($step) ? 1 : $step;
        if ($step == 1) {
            $this->assign("sjlist", $this->getgzh()); #店铺列表
            $this->display();
        } else {
            $uid = $_GET['gzid'];
            if (empty($uid)) {
                $this->error("请选择商城！");
                exit;
            }
            $this->assign("uid", $uid);
            $this->assign("glist", $this->getgoodslist($uid));

            $this->display("add_taocan1");
        }
    }

    /**
     * 删除套餐
     */
    public function del_taocan() {
        $id = $_GET['id'];
        $M = M("Taocan");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 商城分类
     */
    public function shopcategory() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $cmod = new ShopcategoryModel();
        $list = $cmod->getcategory();
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 添加分类
     */
    public function addshopcategory() {
        if (IS_POST) {
            $cname = trim($_POST['cname']);
            if (empty($cname)) {
                $this->success("分类名称不能为空！");
                exit;
            }
            if (!empty($_FILES['clogo']['name'])) {
                $path = "/Uploads/shangjia/";
                $imgsrc = $this->upload("." . $path);
                if (!empty($imgsrc[0]['savename']))
                    $clogo = $path . $imgsrc[0]['savename'];
            }
            $cmod = new ShopcategoryModel();
            $data = array();
            $pid = $_POST['pid'];
            $data['cname'] = $cname;
            $data['clogo'] = $clogo;
            $data['pid'] = $pid;
            if ($cmod->checkname($cname)) {
                $this->error("商品分类已经存在！");
                exit;
            }

            if ($pid == 0) {
                $data['level'] = 0;
            } else {
                $data['level'] = $cmod->getlevel($pid);
                $data['level']+=1;
            }
            $res = $cmod->add($data);
            if ($res) {
                $this->success("操作成功!", U("Shop/shopcategory"));
            } else {
                $this->error("操作失败!");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $scmod = new ShopcategoryModel();
        $sclist = $scmod->getcategory();
        $this->assign("sclist", $sclist);
        $this->display();
    }

    /**
     * 编辑分类
     */
    public function editshopcategory() {
        if (IS_POST) {
            $cid = $_POST['cid'];
            $scmod = new ShopcategoryModel();
            $info = $scmod->where("cid=" . $cid)->find();

            $cname = trim($_POST['cname']);
            if (!empty($_FILES['clogo']['name'])) {

                $path = "/Uploads/shangjia/";


                $imgsrc = $this->upload("." . $path);
                if (!empty($imgsrc[0]['savename'])) {
                    $clogo = $path . $imgsrc[0]['savename'];
                    unlink("." . $path . $info['clogo']);
                }
            }
            $pid = $_POST['pid'];
            if ($pid == 0) {
                $level = 0;
            } else {
                $level = $scmod->getlevel($pid);
                $level+=1;
            }

            $data = array(
                "cname" => $cname,
                "clogo" => $clogo,
                "pid" => $pid,
                "level" => $level
            );
            $where = "cid=" . $cid;
            $res = $scmod->where($where)->save($data);
            if ($res) {
                $this->success("操作成功!", U("Shop/shopcategory"));
            } else {
                $this->error("操作失败!");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $cid = $_GET['cid'];
        $scmod = new ShopcategoryModel();
        $info = $scmod->getinfo($cid);
        $this->assign("info", $info);
        #分类
        $sclist = $scmod->getcategory();
        $this->assign("sclist", $sclist);

        $this->display("addshopcategory");
    }

    /**
     * 删除分类
     */
    public function delshopcategory() {
        $cid = $_GET['cid'];
        $scmod = new ShopcategoryModel();
        $info = $scmod->getinfo($cid);
        if (!empty($info['clogo'])) {
            unlink("." . $info['clogo']);
        }
        $rs = $scmod->where("cid=" . $cid)->delete();
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败!");
    }

    /**
     * 添加幻灯片
     */
    public function add_hdp() {
        if (IS_POST) {
            $name = trim($_POST['name']);
            $iminf = $this->upload();
            if (!empty($iminf[0]['savename'])) {
                $img = "/Uploads/product/" . $iminf[0]['savename'];
            }
            $link = trim($_POST['link']);
            $uid = $_POST['gzid'];
            $addtime = time();
            $status = $_POST['status'];

            $M = M("Hdpmember");
            $data = array(
                "name" => $name,
                "img" => $img,
                "link" => $link,
                "uid" => $uid,
                "addtime" => $addtime,
                "status" => $status,
                "type" => 2
            );
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！");
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("sjlist", $this->getgzh());
        $this->display();
    }

    /**
     * 幻灯片
     * 列表
     */
    public function list_hdp() {

        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh()); #设计师

        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $uid = $_GET['uid'];

        $this->assign("keys", $keys);
        $this->assign("uid", $uid);

        import("ORG.Util.Page");
        $where = "Hdp.type=2 ";

        if (!empty($keys))
            $where.=" and Hdp.name like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and Hdp.uid=" . $uid;


        $M = D("HdpshopView");
        $totalRows = $M->where($where)->count();

        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("Hdp.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();


        $this->assign("page", $p->show());

        foreach ($list as $k => $v) {

            $list[$k]['status_f'] = $v['status'] == 1 ? "已审核" : "未审核";
        }
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 删除幻灯片
     */
    public function del_hpd() {
        $id = $_GET['id'];
        $M = M("Hdpmember");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 编辑
     * 幻灯片
     */
    public function edit_hpd() {

        if (IS_POST) {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            $gzid = $_POST['gzid'];
            $status = trim($_POST['status']);
            $link = $_POST['link'];
            $imgi = $this->upload();
            if (!empty($imgi[0]['savename'])) {
                $img = "/Uploads/product/" . $imgi[0]['savename'];
            }

            $data = array();

            $M = M("Hdpmember");
            $info1 = $M->where("id=" . $id)->find();
            if (!empty($img)) {
                $data['img'] = $img;
                unlink("." . $info1['img']);
            }
            if (!empty($name))
                $data['name'] = $name;
            if ($link != $info1['link'])
                $data['link'] = $link;
            if ($gzid != $info1['uid'])
                $data['uid'] = $gzid;
            if ($status != $info1['status'])
                $data['status'] = $status;
            $data['addtime'] = time();
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U('Shop/list_hdp'));
            else {

                $this->error("操作失败！");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        if (empty($id)) {
            $this->error("请选择要编辑的幻灯片");
            exit;
        }
        $M = M("Hdpmember");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $gzlist = $this->getgzh();
        $this->assign("sjlist", $gzlist); #工长

        $this->display("add_hdp");
    }

    /**
     * 快速修改状态
     * 幻灯片
     */
    public function status_hdp() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 0)
            $statusf = 1;
        else
            $statusf = 0;
        $M = M("Hdpmember");
        $rs = $M->where("id=" . $id)->save(array("status" => $statusf));
        if ($rs)
            $this->success("操作成功");
        else
            $this->error("操作失败！");
    }

    /**
     * 添加优惠券
     */
    public function add_yhq() {
        if (IS_POST) {
            $now = time();
            $name = trim($_POST['name']);
            $uid = $_POST['gzid'];
            if (empty($name)) {
                $this->error("优惠券名称不能为空！");
                exit;
            }
            $M = M("Yhq");
            $cou = $M->where("name='" . $name . "'")->count();
            if ($cou > 0) {
                $this->error("优惠券名称已经存在！");
                exit;
            }
            if (empty($uid)) {
                $this->error("请选择商城！");
                exit;
            }
            $imginf = $this->upload();
            if (!empty($imginf)) {
                if (!empty($imginf[0]['savename']))
                    $yhimg = "/Uploads/product/" . $imginf[0]['savename'];
            }
            $is_tj = $_POST['is_tj'];
            $status = $_POST['status'];
            $dyprice = trim($_POST['dyprice']);
            $xyprice = trim($_POST['xyprice']);
            $startdate = $_POST['startdate'];
            $enddate = $_POST['enddate'];
            $yhqnum = trim($_POST['yhqnum']);
            $czsm = trim($_POST['czsm']);
            $sytj = trim($_POST['sytj']);
            if (empty($startdate)) {
                $this->error("开始日期不能为空！");
                exit;
            }
            if (empty($enddate)) {
                $this->error("结束日期不能为空！");
                exit;
            }
            if (!empty($startdate)) {
                $starttime = strtotime($startdate);
            }
            if (!empty($enddate)) {
                $endtime = strtotime($enddate);
            }
            if ($starttime > $endtime) {
                $this->error("开始日期不能大于结束日期！");
                exit;
            }
            if (empty($yhqnum)) {
                $this->error("优惠券数目不能为空！");
                exit;
            }
            $pc = $this->getprocity($uid);
            $p_id = $pc['p_id'];
            $c_id = $pc['c_id'];
            $data = array(
                "name" => $name,
                "is_tj" => $is_tj,
                "yhimg" => $yhimg,
                "dyprice" => $dyprice,
                "xyprice" => $xyprice,
                "startdate" => $starttime,
                "enddate" => $endtime,
                "yhqnum" => $yhqnum,
                "ysy" => 0,
                "addtime" => time(),
                "p_id" => $p_id,
                "c_id" => $c_id,
                "adduid" => $_SESSION['my_info']['a_id'],
                "uid" => $uid,
                "czsm" => $czsm,
                "sytj" => $sytj,
                "status" => $status
            );

            $rs = $M->add($data);
            $M2 = M("YhqXiangxi");
            if ($rs) {
                //生成优惠券密码

                $id = $M->getLastInsID();
                for ($y = 0; $y < $yhqnum; $y++) {
                    $pwd = $this->shengchengpwd();
                    $data1 = array(
                        "id" => $id,
                        "yhqpwd" => $pwd,
                        "issy" => 0
                    );
                    $M2->add($data1);
                }
            }
            $this->success("操作成功！", U('Shop/list_yhq'));
            exit;
        }

        parent::_initalize();
        $this->assign('systemConfig', $this->systemConfig);
        $this->assign("sjlist", $this->getgzh());

        $this->display();
    }

    /**
     * 优惠券列表
     */
    public function list_yhq() {
        parent::_initalize();
        $this->assign('systemConfig', $this->systemConfig);
        import("ORG.Util.Page");
        $M = M("Yhq");
        $where = '1';
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        if ($is_qx == 1) {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
        } else {
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
            $citymod = new CityModel();
            $plist = $citymod->getprovince(1);
            $this->assign("plist", $plist);
            $this->assign("cname", $citymod->getname($c_id));
        }
        $this->assign("is_qx", $is_qx);
        $startdate = $_GET['startdate'];
        $startdate1 = $_GET['startdate1'];
        $enddate = $_GET['enddate'];
        $enddate1 = $_GET['enddate1'];

        $this->assign("startdate", $startdate);
        $this->assign("startdate1", $startdate1);

        $this->assign("enddate", $enddate);
        $this->assign("enddate1", $enddate1);

        if (!empty($startdate)) {
            $starttime = strtotime($startdate);
        }
        if (!empty($startdate1)) {
            $starttime1 = strtotime($startdate1);
        }
        if (!empty($enddate)) {
            $endtime = strtotime($enddate);
        }
        if (!empty($enddate1)) {
            $endtime1 = strtotime($enddate1);
        }
        if ($starttime > $starttime1) {
            $this->error("开始时间第一个不能大于第二个");
            exit;
        }
        if ($endtime > $endtime1) {
            $this->error("结束时间第一个不能大于第二个");
            exit;
        }
        if (!empty($p_id))
            $where.=" and p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and c_id=" . $c_id;
        if (!empty($starttime) && !empty($starttime1)) {
            $where.=" and (startdate between '" . $starttime . "' and '" . $starttime1 . "' )";
        }
        if (!empty($endtime) && !empty($endtime1)) {
            $where.=" and (enddate between '" . $endtime . "' and '" . $endtime1 . "' )";
        }


        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order(" addtime desc ")->limit($p->firstRow . "," . $p->listRows)->select();
        $arr = array("普通", '推荐');
        $arr1 = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['is_tj_f'] = $arr[$v['is_tj']];
            $list[$k]['status_f'] = $arr1[$v['status']];
            $list[$k]['startdate'] = date("Y-m-d H:i", $v['startdate']);
            $list[$k]['enddate'] = date("Y-m-d H:i", $v['enddate']);
        }
        $this->assign("list", $list);
        $this->assign("page", $p->show());
        $this->display();
    }

    /**
     * 删除优惠券
     */
    public function del_yhq() {
        $id = $_GET['id'];
        #删除详细
        $Mx = M("YhqXiangxi");
        $rs1 = $Mx->where("id=" . $id)->delete();

        $M = M("Yhq");
        $inf = $M->where("id=" . $id)->find();
        if (!empty($inf)) {
            unlink("." . $inf['yhimg']);
        }
        $rs = $M->where("id=" . $id)->delete();

        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 编辑优惠券
     */
    public function edit_yhq() {
        if (IS_POST) {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            $img_inf = $this->upload();
            if (!empty($img_inf)) {
                $img = $img_inf[0]['savename'];
                if (!empty($img)) {
                    $img = "/Uploads/product/" . $img;
                }
            }
            $status = $_POST['status'];
            $is_tj = $_POST['is_tj'];
            $dyprice = trim($_POST['dyprice']);
            $xyprice = trim($_POST['xyprice']);
            $startdate = trim($_POST['startdate']);
            $enddate = trim($_POST['enddate']);
            $czsm = trim($_POST['czsm']);
            $sytj = trim($_POST['sytj']);
            if (empty($startdate)) {
                $this->error("开始日期不能为空！");
                exit;
            }
            if (empty($enddate)) {
                $this->error("结束日期不能为空！");
                exit;
            }
            if (!empty($startdate) && !empty($enddate)) {
                $starttime = strtotime($startdate);
                $endtime = strtotime($enddate);
            }

            $M = M("Yhq");
            $inf2 = $M->where("id=" . $id)->find();
            $data = array();
            if ($inf2['name'] != $name)
                $data['name'] = $name;
            if ($inf2['is_tj'] != $is_tj)
                $data['is_tj'] = $is_tj;
            if ($inf2['yhimg'] != $img)
                $data['yhimg'] = $img;
            if ($inf2['dyprice'] != $dyprice)
                $data['dyprice'] = $dyprice;
            if ($inf2['xyprice'] != $xyprice)
                $data['xyprice'] = $xyprice;
            if ($inf2['startdate'] != $starttime)
                $data['startdate'] = $starttime;
            if ($inf2['enddate'] != $endtime)
                $data['enddate'] = $endtime;
            if ($inf2['czsm'] != $czsm)
                $data['czsm'] = $czsm;
            if ($inf2['sytj'] != $sytj)
                $data['sytj'] = $sytj;
            if ($inf2['status'] != $status)
                $data['status'] = $status;
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U("Shop/list_yhq"));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign('systemConfig', $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Yhq");
        $info = $M->where("id=" . $id)->find();
        $info['startdate'] = date("Y-m-d H:i", $info['startdate']);
        $info['enddate'] = date("Y-m-d H:i", $info['enddate']);
        $this->assign("info", $info);
        $this->assign("is_edit", 1);
        $this->assign("comname", $this->getnamebyuid($info['uid']));
        $this->display("add_yhq");
    }

    /**
     * 优惠券详细表
     */
    public function list_xx_yhq() {
        parent::_initalize();
        $this->assign('systemConfig', $this->systemConfig);
        $M = M("YhqXiangxi");
        $id = $_GET['id'];

        $this->assign("id", $id);
        $issy = $_GET['issy'];
        $this->assign("issy", $issy);
        $wh = "id=" . $id;
        if ($issy == 2)
            $wh.=" and issy=0";
        elseif ($issy == 1)
            $wh.=" and issy=" . $issy;

        $list = $M->where($wh)->select();

        $arr = array("未使用", "已使用");
        foreach ($list as $k => $v) {
            $list[$k]['issy_f'] = $arr[$v['issy']];
        }
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 优惠券 删除详细表
     */
    public function del_xx_yhq() {
        $yid = $_GET['id'];
        $M = M("YhqXiangxi");
        $info = $M->where("yid=" . $yid)->find();
        $id = $info['id'];
        $M2 = M("Yhq");
        if ($info['yhqnum'] <= 1) {
            $rs1 = $M2->where("id=" . $id)->delete();
        } else {
            $yhqnum = $info['yhqnum'] - 1;
            $rs1 = $M2->where("id=" . $id)->save(array("yhqnum" => $yhqnum));
        }
        $rs = $M->where("yid=" . $yid)->delete();
        if ($rs && $rs1)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 设置优惠券 附加表 状态
     */
    public function edit_status_xxyhq() {
        if (IS_POST) {
            $gzid = $_POST['gzid'];
            $movphone = $_POST['movphone'];
            if (empty($gzid)) {
                $this->error("请选择客户！");
                exit;
            }
            if (empty($movphone)) {
                $this->error("请填写领取的手机号！");
                exit;
            }
            $mdid = $_POST['mdid'];
            if (empty($mdid)) {
                $this->error("请选择门店！");
                exit;
            }

            $yhqid = $_POST['yhqid']; #优惠券id

            $yid = $_POST['yid']; #优惠券密码id
            $pc = $this->getprocity_kh($gzid);
            $data = array(
                "uid" => $gzid,
                "addtime" => time(),
                "mdid" => $mdid,
                "movphone" => $movphone,
                "yhqid" => $yhqid,
                "yid" => $yid,
                "p_id" => $pc['p_id'],
                "c_id" => $pc['c_id']
            );
            $M = M("Yhjjl");
            $M1 = M("Yhq");
            $M2 = M("YhqXiangxi");
            $rs = $M->add($data); //添加一条记录
            $rs1 = $M2->where("yid=" . $yid)->save(array("issy" => 1)); //设置优惠券密码记录为已使用

            $inf = $M1->where("id=" . $yhqid)->field("ysy")->find(); //增加一张张优惠券 使用状态
            $ysy = $inf['ysy'] + 1;
            $rs2 = $M1->where("id=" . $yhqid)->save(array("ysy" => $ysy));

            if ($rs && $rs1 && $rs2)
                $this->success("操作成功！", U("Shop/list_xx_yhq", array('id' => $yhqid)));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $yid = $_GET['yid']; #密码id
        $this->assign("khlist", $this->getkehu());
        $this->assign("yid", $yid); #优惠券密码id
        $M = M("YhqXiangxi");

        $info = $M->where("yid=" . $yid)->find();
        $this->assign("yhqid", $info['id']); #优惠券id
        $M2 = M("Yhqmd");
        $info2 = $M2->where("yhqid=" . $info['id'])->field("id,mdname")->select();

        $this->assign("mdlist", $info2); #门店列表
        $this->display();
    }

    /**
     * 设置优惠券 主表 状态
     */
    public function edit_status_yhq() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 1)
            $status_f = 0;
        else
            $status_f = 1;
        $M = M("Yhq");
        $rs = $M->where("id=" . $id)->save(array("status" => $status_f));
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败!");
    }

    /**
     * 查看优惠券记录
     */
    public function yhqjl() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $yid = $_GET['yid'];
        $id = $_GET['id'];
        $this->assign("id", $id);
        $M = M("Yhjjl");
        $info = $M->where("yid=" . $yid)->find();
        $info['addtime'] = date("Y-m-d H:i:s", $info['addtime']);
        $M1 = M("YhqXiangxi");
        $info2 = $M1->where("yid=" . $info['yid'])->field("yhqpwd")->find();
        $this->assign("yhqpwd", $info2['yhqpwd']);
        $this->assign("info", $info);
        $M2 = M("Yhq");
        $info3 = $M2->where("id=" . $info['yhqid'])->field("name")->find();
        $this->assign("yhqname", $info3['name']);

        $M3 = M("Yhqmd");
        $info3 = $M3->where("id=" . $info['mdid'])->field("mdname")->find();

        $this->assign("mname", $info3['mdname']);

        $M4 = M("Kehuview");
        $info4 = $M4->where("a_id=" . $info['uid'])->field("a_name,truename")->find();
        ##echo $M4->getLastSql();
        $this->assign("kename", $info4['a_name'] . "[" . $info4['truename'] . "]");

        $this->display();
    }

    /**
     * 查看门店
     */
    public function ck_mendian() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        if (empty($id)) {
            $this->error("请选择优惠券");
        }
        $M = M("Yhqmd");
        $list = $M->where("yhqid=" . $id)->order("id desc")->select();
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 添加门店
     */
    public function add_mendian() {
        if (IS_POST) {
            $mdname = trim($_POST['mdname']);
            if (empty($mdname)) {
                $this->error("优惠券门店名称不能为空");
                exit;
            }
            $M = M("Yhqmd");
            $id = $_POST['id'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];

            $data = array(
                "yhqid" => $id,
                "mdname" => $mdname,
                "p_id" => $p_id,
                "c_id" => $c_id
            );
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！", U('Shop/list_yhq'));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $p_id = $_GET['p_id'];
        $c_id = $_GET['c_id'];
        $this->assign("id", $id);
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);

        $this->display();
    }

    /**
     * 删除门店
     */
    public function del_mendian() {
        $id = $_GET['id'];
        $M = M("Yhqmd");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 修改门店
     */
    public function edit_mendian() {
        if (IS_POST) {
            $id = $_POST['id'];
            $mdname = trim($_POST['mdname']);
            if (empty($mdname)) {
                $this->error("门店名称不能为空！");
                exit;
            }
            $M = M("Yhqmd");
            $rs = $M->where("id=" . $id)->save(array("mdname" => $mdname));
            $yhqid = $_POST['yhqid'];

            if ($rs)
                $this->success("操作成功！", U("Shop/ck_mendian", array('id' => $yhqid)));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Yhqmd");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $this->assign("id", $id);
        $this->assign("is_edit", 1);

        $this->display("add_mendian");
    }

    /**
     * 口碑点评
     */
    public function koubeicomment() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "type=1";
        $is_hf = $_GET['is_hf'];
        $this->assign("is_hf", $is_hf);
        $uid = $_GET['uid'];
        $this->assign("uid", $uid);
        if (!empty($is_hf)) {
            $is_hf = $is_hf == 2 ? 0 : $is_hf;
            $where .= " and is_hf=" . $is_hf;
        }
        if (!empty($uid))
            $where .= " and sjuid=" . $uid;

        $M = M("Koubeicomment");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        $arrhf = array("未回复", "已回复");
        $arrs = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['is_hf_f'] = $arrhf[$v['is_hf']];
            $list[$k]['status_f'] = $arrs[$v['status']];
        }

        $this->assign("list", $list);
        $this->assign("page", $p->show());
        $gzlist = $this->getgzh(); #获取商城
        $this->assign("gzlist", $gzlist);
        $this->display();
    }

    /**
     * 口碑状态
     */
    public function status_koubei() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 1) {
            $status_f = 0;
        } else {
            $status_f = 1;
        }
        $M = M("Koubeicomment");
        $rs = $M->where("id=" . $id)->save(array("status" => $status_f));
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 删除口碑
     */
    public function del_koubei() {
        $id = $_GET['id'];
        $M = M("Koubeicomment");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("删除成功！");
        else
            $this->error("删除失败！");
    }

    /**
     * 回复
     */
    public function hf() {
        if (IS_POST) {
            $id = $_POST['id'];
            $hf_content = trim($_POST['hf_content']);
            $M = M("Koubeicomment");
            if (empty($hf_content)) {
                $this->error("回复内容不能为空！");
                exit;
            }
            $rs = $M->where("id=" . $id)->save(array("hf_content" => $hf_content, "hf_time" => time(), "is_hf" => 1));
            if ($rs)
                $this->success("操作成功！", U("Shop/koubeicomment"));
            else
                $this->error("操作失败！");

            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $M = M("Koubeicomment");
        $id = $_GET['id'];
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $kh = $this->getkehu_ins($info['uid']);
        $this->assign("khmem", $kh['a_name'] . "[" . $kh['truename'] . "]");
        $this->assign("addtime", date("Y-m-d H:i:s", $info['addtime']));
        $is_good = $info['is_good'] == 1 ? "好评" : "差评";
        $this->assign("is_good_f", $is_good);
        $sj = $this->getgzh_ins($info['sjuid']);
        $this->assign("sjmem", $sj['a_name'] . "[" . $sj['company'] . "]");
        $status_f = $info['status'] == 1 ? "已审核" : "未审核";

        $this->assign("status_f", $status_f);

        $this->display();
    }

    /**
     * 商品评论列表
     */
    public function comments() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "1";
        $is_hf=$_GET['is_hf'];
        $this->assign("is_hf",$is_hf);
        $uid=$_GET['uid'];
        $this->assign("uid",$uid);
        if(!empty($is_hf)){
            $is_hf=$is_hf==2?0:$is_hf;
            $where.=" and Gcomments.is_hf=".$is_hf;
        }
        if(!empty($uid)){
            $where.=" and Gcomments.sjid=".$uid;
        }
        
        #工长
        $this->assign("gzlist",$this->getgzh());
        $M = D("GcommentsView");
        $cou = $M->where($where)->count();
        $p = new Page($cou, 10);
        $list = $M->where($where)->limit($p->firstRow . "," . $p->listRows)->order("Gcomments.addtime desc")->select();
        $statusarr=array("未审核","已审核");
        $hfstatus=array("未回复","已回复");
        
        foreach($list as $k=>$v){
            $list[$k]['is_hf_f']=$hfstatus[$v['is_hf']];
            $list[$k]['status_f']=$statusarr[$v['status']];
        }
        $this->assign("list", $list);
        $this->assign("page", $p->show());

        $this->display();
    }
    /**
     * 修改状态
     */
    public function  status_gcomment(){
        $id=$_GET['id'];
        $status=$_GET['statuss'];
        $status=$status==1?0:1;
        $M=M("Gcomments");
        $rs=$M->where("id=".$id)->save(array("status"=>$status));
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }
    /**
     * 删除商品评论
     */
    public function del_gcomment(){
        $id=$_GET['id'];
        $M=M("Gcomments");
        $info=$M->where("id=".$id)->find();
        if(!empty($info['imgsrc'])){
            $imgsrc=json_decode($info['imgsrc']);
            foreach ($imgsrc as $k=>$v){
                unlink(".".$v);
            }
        }
        $rs=$M->where("id=".$id)->delete();
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }
    /**
     * 回复评论
     */
    public function hf_gcomment(){
        
        if (IS_POST) {
            $id = $_POST['id'];
            $hf_content = trim($_POST['hf_content']);
            $M = M("Gcomments");
            if (empty($hf_content)) {
                $this->error("回复内容不能为空！");
                exit;
            }
            $rs = $M->where("id=" . $id)->save(array("hfcontent" => $hf_content, "hftime" => time(), "is_hf" => 1));
            if ($rs)
                $this->success("操作成功！", U("Shop/comments"));
            else
                $this->error("操作失败！");

            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $M = M("Gcomments");
        $id = $_GET['id'];
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $kh = $this->getkehu_ins($info['uid']);
        $this->assign("khmem", $kh['a_name'] . "[" . $kh['truename'] . "]");
        $this->assign("addtime", date("Y-m-d H:i:s", $info['addtime']));
        $sj = $this->getgzh_ins($info['sjid']);
        $this->assign("sjmem", $sj['a_name'] . "[" . $sj['company'] . "]");
        $status_f = $info['status'] == 1 ? "已审核" : "未审核";
        $this->assign("status_f", $status_f);
        if(!empty($info['imgsrc'])){
            $imglist=json_decode($info['imgsrc']);
            $this->assign("imglist",$imglist);
        }
        $this->display();
    }

    /**
     * 商店留言咨询
     */
    public function message() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");

        $conf = include './Common/config2.php';
        $messagetype = $conf['messagetype'];
        $this->assign("messagetype", $messagetype);
        $this->assign("gzlist", $this->getgzh());
        $is_hf = $_GET['is_hf'];
        $lytype = $_GET['lytype'];
        $uid = $_GET['uid'];
        $this->assign("is_hf", $is_hf);
        $this->assign("lytype", $lytype);
        $this->assign("uid", $uid);

        $where = "1";
        if (!empty($is_hf)) {
            if ($is_hf == 2)
                $is_hf = 0;
            $where.=" and ishf=" . $is_hf;
        }
        if (!empty($lytype)) {
            $where.=" and lytype=" . $lytype;
        }
        if (!empty($uid)) {
            $where.=" and sjid=" . $uid;
        }
        $M = M("Message");
        $cou = $M->where($where)->count();
        $p = new Page($cou, 10);
        $list = $M->where($where)->order("addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        $hfarr = array("0" => "未回复", "1" => "已回复");
        $starr = array("0" => "未审核", "1" => "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['ishf_f'] = $hfarr[$v['ishf']];
            $list[$k]['status_f'] = $starr[$v['status']];
        }
        $this->assign("list", $list);
        $this->assign("page", $p->show());

        $this->display();
    }

    /**
     * 修改留言状态
     */
    public function status_message() {
        $status = $_GET['status'];
        $id = $_GET['id'];
        $statusf = $status == 1 ? "0" : "1";
        $M = M("Message");
        $res = $M->where("id=" . $id)->save(array("status" => $statusf));
        if ($res)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 删除留言
     */
    public function del_message() {
        $M = M("Message");
        $id = $_GET['id'];
        $res = $M->where("id=" . $id)->delete();
        if ($res)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 回复留言
     */
    public function hf_message() {
        if (IS_POST) {
            $id = $_POST['id'];
            $hf_content = trim($_POST['hf_content']);
            if (empty($hf_content)) {
                $this->error("请填写回复内容");
                exit;
            }
            $M = M("Message");
            $rs = $M->where("id=" . $id)->save(array("hfcontent" => $hf_content, "hftime" => time(), "ishf" => 1));
            if ($rs)
                $this->success("操作成功！", U("Shop/message"));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Message");
        $info = $M->where("id=" . $id)->find();

        $this->assign("info", $info);
        $arr = include './Common/config2.php';
        $messagetype = $arr['messagetype'];
        $lytype = $messagetype[$info['lytype']];
        $this->assign("lytype", $lytype);
        $sj = $this->getgzh_ins($info['sjid']);
        $this->assign("sj", $sj['a_name'] . "[" . $sj['company'] . "]");
        if ($info['status'] == 1)
            $statusf = "已审核";
        else
            $statusf = "未审核";
        $this->assign("statusf", $statusf);
        $addtimef = date("Y-m-d H:i:s", $info['addtime']);
        $this->assign("addtimef", $addtimef);

        $this->display();
    }

    /**
     * 订单列表
     */
    public function order() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $M = D("OrderView");
        $where = "1";
        #商家条件
        $sjid = $_GET['sjid'];
        if (!empty($sjid))
            $where .= " and o.sjid=" . $sjid;
        $this->assign("sjid", $sjid);
        #状态条件
        $status = $_GET['status'];
        $status = $status == '2' ? "0" : $status;
        if (!empty($status))
            $where .= " and o.status=" . $status;
        $this->assign("status", $status);
        #订单号条件
        $keys = $_GET['keys'];
        $keys = $keys == '请填写订单号' ? "" : $keys;
        if (!empty($keys))
            $where .= " and o.ordersn like '%" . $keys . "%'";
        $this->assign("keys", $keys);

        $cou = $M->where($where)->count();
        $p = new Page($cou, 10);
        $list = $M->where($where)->limit($p->firstRow . "," . $p->listRows)->select();
        # echo $M->getLastSql();
        $conf = include './Common/config2.php';
        $statuslist = $conf['status'];
        foreach ($list as $k => $v) {
            $list[$k]['status_f'] = $statuslist[$v['status']];
        }
        $this->assign("list", $list);
        $this->assign("page", $p->show());

        $this->assign("statuslist", $statuslist);
        $this->assign('sclist', $this->getgzh());

        $this->display();
    }

    /**
     * 删除订单
     */
    public function del_order() {
        $id = $_GET['id'];
        $M = M("Orderxiangxi");
        $M1 = M("Order");
        $r = $M->where("id=" . $id)->delete();
        $r1 = $M1->where("id=" . $id)->delete();
        if ($r1 && $r) {
            $this->success("操作成功！");
        } else {
            $this->error("操作失败！");
        }
    }

    /**
     * 订单详细列表
     */
    public function ins_order() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $where = "o.orderid=" . $id;
        $M = D("OrderinsView");
        import("ORG.Util.Page");
        $list = $M->where($where)->order("o.id desc")->select();
        $this->assign("list", $list);
        
        $M1=M("Order");
        $info=$M1->where("id=".$id)->find();
        $conf=include'./Common/config2.php';
        $status=$conf['status'];
        $info['statusf']=$status[$info['status']];
        
        $this->assign("info",$info);
        #配送类型
        $info1=M("Peisongtype")->where("id=".$info['pstype'])->field("name")->find();
        $this->assign("pstypef",$info1['name']);
        #支付方式
        $info2=M("Zhifutype")->where("id=".$info['paytype'])->field("name")->find();
        $this->assign("paytypef",$info2['name']);
        #会员
        $info3=M("Member")->where("a_id=".$info['uid'])->field("a_name")->find();
        $this->assign("uname",$info3['a_name']);
        $this->assign("addtimef",date("Y-m-d H:i:s",$info['addtime']));
        #商家$company
        $info4=M("Dianpu")->where("a_id=".$info['sjid'])->field("company")->find();
        $this->assign("company",$info4['company']);
        
        $this->assign("addtimef",date("Y-m-d H:i:s",$info['addtime']));
        $this->assign("fhaddtimef",date("Y-m-d H:i:s",$info['fhtime']));
        
        
        
        $this->display();
    }

    /**
     * 删除订单详细
     */
    public function del_insorder() {
        $id = $_GET['id'];
        $M = M("Orderxiangxi"); 
        $r = $M->where("id=" . $id)->delete();
        if ( $r) {
            $this->success("操作成功！");
        } else {
            $this->error("操作失败！");
        }
    }
    
    /**
     * 发货
     */
    public function fh(){
        if(IS_POST){
            $fhsn=trim($_POST['fhsn']);
            if(empty($fhsn)){
                $this->error("发货单号不能为空！"); 
                exit;
            }
            $id=$_POST['id'];
            if(empty($id)){
                $this->error("请选择订单！");
                exit;
            }
            $M=M("Order");
            $rs=$M->where("id=".$id)->save(array("fhsn"=>$fhsn,"fhtime"=>time(),"status"=>2));
            if($rs)
                $this->success ("操作成功!",U("Shop/order"));
            else
                $this->error ("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $id=$_GET['id'];
        $M=M("Order");
        $info=$M->where("id=".$id)->find();
        $conf=include'./Common/config2.php';
        $status=$conf['status'];
        $info['statusf']=$status[$info['status']];
        
        $this->assign("info",$info);
        #配送类型
        $info1=M("Peisongtype")->where("id=".$info['pstype'])->field("name")->find();
        $this->assign("pstypef",$info1['name']);
        #支付方式
        $info2=M("Zhifutype")->where("id=".$info['paytype'])->field("name")->find();
        $this->assign("paytypef",$info2['name']);
        #会员
        $info3=M("Member")->where("a_id=".$info['uid'])->field("a_name")->find();
        $this->assign("uname",$info3['a_name']);
        $this->assign("addtimef",date("Y-m-d H:i:s",$info['addtime']));
        #商家$company
        $info4=M("Dianpu")->where("a_id=".$info['sjid'])->field("company")->find();
        $this->assign("company",$info4['company']);
        
        $this->assign("addtimef",date("Y-m-d H:i:s",$info['addtime']));
        #商品
        $M1=D("OrderinsView");
        $glist=$M1->where("o.orderid=".$id)->select();
        
        $this->assign("glist",$glist);
        
        $this->display();
    }
    /**
     * 退货
     */
    public function th(){
        
        if(IS_POST){
            
            $id=$_POST['id'];
            if(empty($id)){
                $this->error("请选择订单！");
                exit;
            }
            $M=M("Order");
            $rs=$M->where("id=".$id)->save(array("status"=>5));
            if($rs)
                $this->success ("操作成功!",U("Shop/order"));
            else
                $this->error ("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig",$this->systemConfig);
        $id=$_GET['id'];
        $M=M("Order");
        $info=$M->where("id=".$id)->find();
        $conf=include'./Common/config2.php';
        $status=$conf['status'];
        $info['statusf']=$status[$info['status']];
        $info['fhtimef']=date("Y-m-d H:i:s",$info['fhtime']);
        $info['thtimef']=date("Y-m-d H:i:s",$info['thtime']);
        
        $this->assign("info",$info);
        #配送类型
        $info1=M("Peisongtype")->where("id=".$info['pstype'])->field("name")->find();
        $this->assign("pstypef",$info1['name']);
        #支付方式
        $info2=M("Zhifutype")->where("id=".$info['paytype'])->field("name")->find();
        $this->assign("paytypef",$info2['name']);
        #会员
        $info3=M("Member")->where("a_id=".$info['uid'])->field("a_name")->find();
        $this->assign("uname",$info3['a_name']);
        $this->assign("addtimef",date("Y-m-d H:i:s",$info['addtime']));
        #商家$company
        $info4=M("Dianpu")->where("a_id=".$info['sjid'])->field("company")->find();
        $this->assign("company",$info4['company']);
        
        $this->assign("addtimef",date("Y-m-d H:i:s",$info['addtime']));
        #商品
        $M1=D("OrderinsView");
        $glist=$M1->where("o.orderid=".$id)->select();
        
        $this->assign("glist",$glist);
        
        $this->display();
    }
    /**
     * 确认收货
     */
    public function qr_order(){
        $id=$_GET['id'];
        $M=M("Order");
        $rs=$M->where("id=".$id)->save(array("qrtime"=>time(),"status"=>1));
        if($rs)
            $this->success ("操作成功！");
        else
            $this->error ("操作失败！");
    }
    //----------------------private-------
    /**
     * 获取商城列表
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
        $M = M("Dianpumember");
        $list = $M->where($where)->field("a_id,a_name,lxrname,company")->select();

        return $list;
    }

    /**
     * 获取商家详细
     */
    private function getgzh_ins($aid) {
        $where = "a_id=" . $aid;
        $M = M("Dianpumember");
        $info = $M->where($where)->field("a_id,a_name,lxrname,company")->find();
        return $info;
    }

    /**
     * 获取客户列表
     */
    private function getkehu() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $where = "1";
        if ($is_qx == 1) {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];

            $where.=" and p_id=" . $p_id;
            $where.=" and c_id=" . $c_id;
        }
        $M = M("Kehuview");
        $list = $M->where($where)->field("a_id,a_name,truename")->select();

        return $list;
    }

    /**
     * 获取客户详细
     */
    private function getkehu_ins($aid) {
        $where = "a_id=" . $aid;
        $M = M("Kehuview");
        $info = $M->where($where)->field("a_id,a_name,truename")->find();
        return $info;
    }

    /**
     * 根据用户id
     * 获取省和市
     */
    private function getprocity($uid) {

        $where = "a_id=" . $uid;

        $M = M("Dianpu");
        $list = $M->where($where)->field("p_id,c_id,q_id")->find();

        return $list;
    }

    /**
     * 检查密码是否存在
     */
    private function ischeck($name) {
        $M = M("YhqXiangxi");
        $rs = $M->where("yhqpwd='" . $name . "'")->count();
        if ($rs > 0)
            return true;
        else
            return false;
    }

    /**
     * 生成密码
     * @return string
     */
    private function shengchengpwd() {
        do {
            $sj1 = rand(65, 90);
            $or1 = chr($sj1);
            $sj2 = rand(65, 90);
            $or2 = chr($sj2);
            $sj3 = rand(65, 90);
            $or3 = chr($sj3);
            $sj4 = rand(65, 90);
            $or4 = chr($sj4);
            $sj5 = rand(0, 9);
            $sj6 = rand(0, 9);
            $sj7 = rand(0, 9);
            $sj8 = rand(0, 9);
            $yhqpwd = $or1 . $or2 . $or3 . $or4 . "_" . $sj8 . $sj5 . $sj6 . $sj7;
            $tf = $this->ischeck($yhqpwd);
        } while ($tf);
        return $yhqpwd;
    }

    /**
     * 根据用户id
     * 商城名称
     */
    private function getnamebyuid($uid) {
        $M = M("Dianpu");
        $rs = $M->where("a_id=" . $uid)->field("company")->find();
        return $rs['company'];
    }

    /**
     * 根据用户id
     * 获取省和市
     */
    private function getprocity_kh($uid) {

        $where = "a_id=" . $uid;

        $M = M("Webmember");
        $list = $M->where($where)->field("p_id,c_id")->find();
        return $list;
    }

    /**
     * 检查名称是否存在
     */
    private function check_goodname($name) {
        $M = M("Goods");
        $cou = $M->where("name='" . $name . "'")->count();
        if ($cou > 0)
            return true;
        else
            return false;
    }

    /**
     * 获取商品
     * @param uid
     */
    private function getgoodslist($uid) {
        $M = M("Goodsview");
        $list = $M->field("id,name,price,ckprice")->where("uid=" . $uid)->select();
        return $list;
    }

    /**
     * 检查套餐名称是否存在
     */
    private function checktaocan($name) {
        $M = M("Taocan");
        $rs = $M->where("tcname ='" . $name . "'")->count();
        if ($rs > 0)
            return true;
        else
            return false;
    }

    /**
     * 检查套餐组合是否存在
     * 
     */
    private function checkzuhe($zh) {
        $M = M("Taocan");
        $rs = $M->where("idzuhe ='" . $zh . "'")->count();
        if ($rs > 0)
            return true;
        else
            return false;
    }

    //------------------ajax-------------------------------------------------
    /**
     * ajax 获取 设计师
     */
    public function ajaxgetsj() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = $_POST['gname'];
        $m = M("Dianpumember");
        $where = "company like '%" . $gname . "%' or a_name like '%" . $gname . "%' or lxrname like '%" . $gname . "%'";
        $rs = $m->where($where)->select();
        if ($rs)
            $data = array("status" => 1, "data" => $rs);
        else
            $data = array("status" => 0, "data" => "");
        echo json_encode($data);
    }

    /**
     * ajax
     * 获取 客户
     */
    public function ajaxgetkehu() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = $_POST['gname'];
        $m = M("Kehuview");
        $where = "truename like '%" . $gname . "%' or a_name like '%" . $gname . "%'";
        $rs = $m->where($where)->select();
        if ($rs)
            $data = array("status" => 1, "data" => $rs);
        else
            $data = array("status" => 0, "data" => "");
        echo json_encode($data);
    }

    /**
     * ajax
     * 改变商品标题
     */
    public function ajax_gbnme() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = $_POST['name'];
        $id = $_POST['id'];
        $M = M("Goods");
        $rs = $M->where("id=" . $id)->save(array("name" => $gname));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

    /**
     * ajax
     * 改变价格
     */
    public function ajax_updateprice() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = $_POST['price'];
        $id = $_POST['id'];
        $M = M("Goods");
        $rs = $M->where("id=" . $id)->save(array("price" => $gname));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

    /**
     * ajax
     * 改变参考价格
     */
    public function ajax_updateckprice() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = $_POST['price'];
        $id = $_POST['id'];
        $M = M("Goods");
        $rs = $M->where("id=" . $id)->save(array("ckprice" => $gname));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

    /**
     * ajax
     * 改变库存
     */
    public function ajax_updatekucun() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = $_POST['price'];
        $id = $_POST['id'];
        $M = M("Goods");
        $rs = $M->where("id=" . $id)->save(array("kucun" => $gname));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

    /**
     * ajax 
     * 改变套餐名称
     */
    public function ajax_updatetcname() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = $_POST['name'];
        $id = $_POST['id'];
        $M = M("Taocan");
        $rs = $M->where("id=" . $id)->save(array("tcname" => $gname));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

}

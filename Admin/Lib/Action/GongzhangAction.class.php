<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForemanAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class GongzhangAction extends CommonAction {

    /**
     * 施工动态列表
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh()); #工长
        $cmod = new CityModel();
        $pro_list = $cmod->getcity(1);
        $this->assign("pro_list", $pro_list);
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $this->assign("is_qx", $is_qx);
        if ($is_qx == 0) {
            $p_id = $_GET['province'];
            $c_id = $_GET['city'];
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
        }
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $uid = $_GET['uid'];

        $this->assign("keys", $keys);
        $this->assign("uid", $uid);
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("cityname", $cmod->getname($c_id));
        $jieduan = $_GET['jieduan'];
        import("ORG.Util.Page");
        $where = "1";
        if (!empty($p_id))
            $where.=" and Shigongdt.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and Shigongdt.c_id=" . $c_id;
        if (!empty($keys))
            $where.=" and Shigongdt.title like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and Shigongdt.uid=" . $uid;
        if (!empty($jieduan))
            $where.=" and Shigongdt.jieduan=" . $jieduan;

        $M = D("ShigongdtView");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("Shigongdt.sort desc")->limit($p->firstRow . "," . $p->listRows)->select();
        #echo $M->getLastSql();

        $this->assign("page", $p->show());
        $inf = include_once './Common/config2.php';
        foreach ($list as $k => $v) {
            $list[$k]['jieduans'] = $inf["zxjd"][$v['jieduan']];
            $list[$k]['status_f'] = $v['status'] == 1 ? "已审核" : "未审核";
        }
        $this->assign("list", $list);
        $this->assign("jd", $inf['zxjd']);
        $this->assign("jieduan", $jieduan);
        $this->display();
    }

    /**
     * 添加施工动态
     */
    public function addsgdt() {
        if (IS_POST) {
            $title = trim($_POST['title']);
            if (empty($title)) {
                $this->error("标题名称不能为空！");
                exit;
            }
            $m = M("Shigongdt");
            $cou = $m->where("title='" . $title . "'")->count();
            if ($cou > 0) {
                $this->error("该施工动态已经存在！");
                exit;
            }
            $tpjhlist = $this->gettpjhlist(); #图片集合分类
            $tjarr = $this->gettjkeys($tpjhlist); #图片集合的key值
            $tjinfo = $this->gettjarr($tpjhlist); #

            $path = "/Uploads/product/";
            $info = $this->upload("." . $path);
            $tj = array();

            if (!empty($info)) {
                foreach ($info as $k => $v) {
                    if (in_array($v['key'], $tjarr)) {
                        $i = explode("_", $v['key']);
                        $tj[$i[1]]["title"] = $tjinfo[$v['key']];
                        $tj[$i[1]]["img"][] = $path . $v['savename'];
                    }
                }
                $tuji = json_encode($tj);
            }

            $data = array();
            $data["p_id"] = $_POST['p_id'];
            $data["c_id"] = $_POST['c_id'];
            $data['uid'] = $_POST['gzid'];

            $fm = $_POST['fengmian'];
            if (!empty($fm))
                $data['fmimg'] = $path . $fm;
            $sort = trim($_POST['sort']);
            $data['sort'] = $sort;
            $status = $_POST['status'];
            $data['status'] = $status;
            $data['adduid'] = $_SESSION['my_info']['a_id'];
            $data['img'] = $tuji;
            $data['jieduan'] = $_POST['jieduan'];
            $data['yezhu'] = $_POST['yezhu'];
            $data['yusuan'] = $_POST['yusuan'];
            $data['mianji'] = $_POST['mianji'];
            $data['huxing'] = $_POST['huxing'];
            $data['keywords'] = trim($_POST['keywords']);
            $data['description'] = trim($_POST['description']);
            $data['gaishu'] = trim($_POST['gaishu']);
            $data['title'] = $title;
            $rs = $m->add($data);
            if ($rs)
                $this->success("操作成功", U("Gongzhang/index"));
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $step = $_REQUEST['step'];
        $step = empty($step) ? 1 : $step;
        if ($step == 1) {
            $this->assign("list", $this->getgzh()); #工长
            $this->assign("step", 2);
            $this->display();
        } else {

            $gzid = $_GET['gzid'];
            if (empty($gzid)) {
                $this->error("请选择工长！");
                exit;
            }
            $this->assign("gzid", $gzid);
            $this->assign("step", $step);
            $conf = include_once './Common/config2.php';
            $jd = $conf['zxjd'];
            $this->assign("jd", $jd);
            $this->assign("hxlist", $this->gethxlist()); #户型分类
            $this->assign("tpjhlist", $this->gettpjhlist()); #图片集合分类
            $ins = $this->getprovcity($gzid);
            $this->assign("p_id", $ins['p_id']);
            $this->assign("c_id", $ins['c_id']);

            $this->display("addsgdt2");
        }
    }

    /**
     * 编辑施工动态
     */
    public function editsgdt() {
        if (IS_POST) {
            $id = $_POST['id'];
            $M1 = M("Shigongdt");
            $info = $M1->where("id=" . $id)->find();

            $title = trim($_POST['title']);
            $gaishu = trim($_POST['gaishu']);
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $huxing = $_POST['huxing'];
            $mianji = trim($_POST['mianji']);
            $yusuan = trim($_POST['yusuan']);
            $yezhu = trim($_POST['yezhu']);
            $jieduan = $_POST['jieduan'];
            $sort = $_POST['sort'];
            $status = $_POST['status'];
            $fengmian = $_POST['fengmian']; #封面

            $tpjhlist = $this->gettpjhlist(); #图片集合分类
            $tjarr = $this->gettjkeys($tpjhlist); #图片集合的key值
            $tjinfo = $this->gettjarr($tpjhlist); #
            $path = "/Uploads/product/";
            $info = $this->upload("." . $path);
            $tj = array();

            if (!empty($info)) {
                foreach ($info as $k => $v) {
                    if (in_array($v['key'], $tjarr)) {
                        $i = explode("_", $v['key']);
                        $tj[$i[1]]["title"] = $tjinfo[$v['key']];
                        $tj[$i[1]]["img"][] = $path . $v['savename'];
                    }
                }
                $tuji = json_encode($tj);
            }
            $data = array();
            if (!empty($fengmian))
                $data['fmimg'] = $fengmian;
            if (!empty($tuji))
                $data['img'] = $tuji;
            if ($title != $info['title'] && !empty($title))
                $data['title'] = $title;
            if ($gaishu != $info['gaishu'] && !empty($gaishu))
                $data['gaishu'] = $gaishu;
            if ($description != $info['description'] && !empty($description))
                $data['description'] = $description;
            if ($keywords != $info['keywords'] && !empty($keywords))
                $data['keywords'] = $keywords;
            if ($huxing != $info['huxing'] && !empty($huxing))
                $data['huxing'] = $huxing;
            if ($mianji != $info['mianji'] && !empty($mianji))
                $data['mianji'] = $mianji;
            if ($yusuan != $info['yusuan'] && !empty($yusuan))
                $data['yusuan'] = $yusuan;
            if ($yezhu != $info['yezhu'] && !empty($yezhu))
                $data['yezhu'] = $yezhu;
            if ($jieduan != $info['jieduan'] && !empty($jieduan))
                $data['jieduan'] = $jieduan;
            if ($sort != $info['sort'] && !empty($sort))
                $data['sort'] = $sort;
            if ($uid != $info['uid'] && !empty($uid))
                $data['uid'] = $uid;
            if ($status != $info['status'])
                $data['status'] = $status;
            $res = $M1->where("id=" . $id)->save($data);
            if ($res) {
                $this->success("操作成功!", U("Gongzhang/index"));
            } else {
                $this->error("操作失败!");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Shigongdt");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        if (!empty($info['img']))
            $imgsrc = json_decode($info['img']);

        $imglist = array();
        foreach ($imgsrc as $k => $v) {
            $imglist[$k]['img'] = $v->img;
        }
        $this->assign("imglist", $imglist);

        $this->assign("hxlist", $this->gethxlist()); #户型列表
        $conf = include_once './Common/config2.php';
        $jd = $conf['zxjd'];
        $this->assign("jd", $jd);
        $this->assign("tpjhlist", $this->gettpjhlist()); #图片集合分类
        $this->assign("gzlist", $this->getgzh());
        $this->display();
    }

    /**
     * 删除施工动态
     */
    public function delsgtd() {
        $id = $_GET['id'];
        $m = M("Shigongdt");
        $info = $m->where("id=" . $id)->field("img,fmimg")->find();
        if (!empty($info['fmimg'])) {
            unlink("." . $info['fmimg']);
        }
        if (!empty($info['img'])) {
            $imgjx = json_decode($info['img']);
            #print_r($imgjx);
            foreach ($imgjx as $k => $v) {
                foreach ($v->img as $k1 => $v1) {
                    unlink("." . $v1);
                }
            }
        }

        $rs = $m->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败!");
    }

    /**
     * 图片集合分类
     */
    public function tpjh() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        if (IS_POST) {
            $tpname = trim($_POST['tpname']);
            if (empty($tpname)) {
                $this->error("图片集合分类不能为空");
                exit;
            }
            #检查是否存在
            $M = M("Tpjh");
            $cou = $M->where("name ='" . $tpname . "'")->count();
            if ($cou > 0) {
                #存在
                $this->error("图片集合分类已经存在！");
                exit;
            }
            $rs = $M->add(array("name" => $tpname));
            if ($rs) {
                $this->success("操作成功!");
                exit;
            } else {
                $this->error("操作失败!");
                exit;
            }
        }
        $M1 = M("Tpjh");
        $list = $M1->where(1)->select();
        $this->assign("list", $list);
        $this->display();
    }

    /**
     * 删除图片集合分类
     */
    public function tpjhdel() {
        $id = $_GET['id'];
        $M1 = M("Tpjh");
        $rs = $M1->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败!");
    }

    /**
     * 修改状态
     */
    public function sgdtstatus() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 1)
            $status_u = 0;
        if ($status == 0)
            $status_u = 1;
        $M = M("Shigongdt");
        $rs = $M->where("id=" . $id)->save(array("status" => $status_u));
        if ($rs)
            $this->success("修改成功！");
        else
            $this->error("修改失败！");
    }

    /**
     * 修改状态
     */
    public function rijistatus() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 1)
            $status_u = 0;
        if ($status == 0)
            $status_u = 1;
        $M = M("Riji");
        $rs = $M->where("id=" . $id)->save(array("status" => $status_u));
        if ($rs)
            $this->success("修改成功！");
        else
            $this->error("修改失败！");
    }

    /**
     * 日记分类
     */
    public function category_riji() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh());
        if (IS_POST) {
            $uid = $_POST['uid'];
            $cname = trim($_POST['cname']);
            if (empty($cname)) {
                $this->error("分类名称不能为空！");
                exit;
            }
            #检查分类名称是否存在
            if ($this->category_rj_exist($cname, $uid)) {
                $this->error("分类名称已经存在！");
                exit;
            }
            $adduid = $_SESSION['my_info']['a_id'];
            $data = array(
                "cname" => $cname,
                "uid" => $uid,
                "adduid" => $adduid
            );
            $M = M("Rijicategory");
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！");
            else
                $this->error("操作失败！");
            exit;
        }

        $where = "1";
        @import("ORG.Util.Page");

        $M = D("RijicategoryView");
        $cou = $M->where($where)->order("cid desc")->count();
        $page = new Page($cou, 10);
        $list = $M->where($where)->order("cid desc")->limit($page->firstRow . "," . $page->listRows)->select();
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 删除日记分类
     */
    public function del_cate_rj() {
        $id = $_GET['id'];
        $M = M("Rijicategory");
        $rs = $M->where("cid=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 添加装修日记
     */
    public function add_riji() {
        if (IS_POST) {
            $gzid = $_POST['gzid'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $adduid = $_SESSION['my_info']['a_id'];
            $title = trim($_POST['title']);
            if (empty($title)) {
                $this->error("标题不能为空！");
                exit;
            }
            if ($this->rj_exist($title)) {
                $this->error("日记标题已经存在！");
                exit;
            }
            $jianjie = trim($_POST['jianjie']);
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $classid = $_POST['classid'];
            $click = $_POST['click'];
            $content = $_POST['content'];
            $source = $_POST['source'];

            $data = array();
            $data['title'] = $title;
            $data['fmimg'] = "/Uploads/product/" . $_POST['fengmian'];
            $data["jianjie"] = $jianjie;
            $data['description'] = $description;
            $data['keywords'] = $keywords;
            $data['classid'] = $classid;
            $data['click'] = $click;
            $data['addtime'] = time();
            $data['content'] = $content;
            $data['p_id'] = $p_id;
            $data['c_id'] = $c_id;
            $data['uid'] = $gzid;
            $data['adduid'] = $adduid;
            $data['source'] = $source;
            $M = M("Riji");
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功", U("Gongzhang/list_riji"));
            else
                $this->error("操作失败");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("list", $this->getgzh()); #工长列表
        $step = $_GET['step'];
        $step = empty($step) ? 1 : $step;
        if ($step == 1) {

            $this->display();
            exit;
        } else {
            $uid = $_GET['gzid'];
            if (empty($uid)) {
                $this->error("请选择工长！");
                exit;
            }
            $this->assign("rijicategory", $this->getrijicategory($uid));
            $pc = $this->getprovcity($uid);
            $this->assign("p_id", $pc['p_id']);
            $this->assign("c_id", $pc['c_id']);
            $this->assign("gzid", $uid);
            $this->assign("step", $step);
            $this->display("add_riji2");
        }
    }

    /**
     * 编辑装修日记
     */
    public function edit_riji() {
        if (IS_POST) {
            $id = $_POST['id'];
            $M = M("Riji");
            $info = $M->where("id=" . $id)->find();
            $content = trim($_POST['content']);
            $uid = $_POST['uid'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $jianjie = trim($_POST['jianjie']);
            $classid = $_POST['classid'];
            $click = trim($_POST['click']);
            $source = trim($_POST['source']);
            $shoucang = trim($_POST['shoucang']);
            $status = trim($_POST['status']);
            $fengmian = trim($_POST['fengmian']);


            $data = array();
            if (empty($title)) {
                $this->error("标题不能为空！");
                exit;
            }
            if (!empty($fengmian)) {
                $fengmians = "/Uploads/product/" . $fengmian;
                $data['fmimg'] = $fengmians;
            }

            if ($info['title'] != $title)
                $data['title'] = $title;
            if ($info['jianjie'] != $jianjie)
                $data['jianjie'] = $jianjie;
            if ($info['description'] != $description)
                $data['description'] = $description;
            if ($info['keywords'] != $keywords)
                $data['keywords'] = $keywords;
            if ($info['classid'] != $classid)
                $data['classid'] = $classid;
            if ($info['click' != $click])
                $data['click'] = $click;
            if ($info['content'] != $content)
                $data['content'] = $content;
            if ($info['uid'] != $uid)
                $data['uid'] = $uid;
            if ($info['source'] != $source)
                $data['source'] = $source;
            if ($info['shoucang'] != $shoucang)
                $data['shoucang'] = $shoucang;

            if ($info['status'] != $status)
                $data['status'] = $status;

            $data['addtime'] = time();

            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U("Gongzhang/list_riji"));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Riji");
        $info = $M->where("id=" . $id)->find();
        $this->assign("gzlist", $this->getgzh()); #工长列表
        $this->assign("rijicategory", $this->getrijicategory($info["uid"]));
        $this->assign("info", $info);
        $this->display();
    }

    /**
     * 删除装修日记
     */
    public function del_riji() {
        $id = $_GET['id'];
        $M = M("Riji");
        $fmimg = $M->where("id=" . $id)->field("fmimg")->find();
        if (!empty($fmimg)) {
            unlink("." . $fmimg['fmimg']);
        }
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功");
        else
            $this->error("操作失败");
    }

    /**
     * 列表 
     * 装修日记
     */
    public function list_riji() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh()); #工长
        $cmod = new CityModel();
        $pro_list = $cmod->getcity(1);
        $this->assign("pro_list", $pro_list);
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $this->assign("is_qx", $is_qx);
        if ($is_qx == 0) {
            $p_id = $_GET['province'];
            $c_id = $_GET['city'];
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
        }
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $uid = $_GET['uid'];

        $this->assign("keys", $keys);
        $this->assign("uid", $uid);
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("cityname", $cmod->getname($c_id));

        import("ORG.Util.Page");
        $where = "1";
        if (!empty($p_id))
            $where.=" and Riji.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and Riji.c_id=" . $c_id;
        if (!empty($keys))
            $where.=" and Riji.title like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and Riji.uid=" . $uid;


        $M = D("RijiView");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("Riji.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        #echo $M->getLastSql();

        $this->assign("page", $p->show());

        foreach ($list as $k => $v) {
            $list[$k]['status_f'] = $v['status'] == 1 ? "已审核" : "未审核";
        }
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 装修案例
     * 列表
     */
    public function list_case() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh()); #工长
        $cmod = new CityModel();
        $pro_list = $cmod->getcity(1);
        $this->assign("pro_list", $pro_list);
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $this->assign("is_qx", $is_qx);
        if ($is_qx == 0) {
            $p_id = $_GET['province'];
            $c_id = $_GET['city'];
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
        }
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $uid = $_GET['uid'];

        $this->assign("keys", $keys);
        $this->assign("uid", $uid);
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("cityname", $cmod->getname($c_id));

        import("ORG.Util.Page");
        $where = "cases.type=1 ";
        if (!empty($p_id))
            $where.=" and cases.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and cases.c_id=" . $c_id;
        if (!empty($keys))
            $where.=" and cases.title like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and cases.uid=" . $uid;


        $M = D("CaseView");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("cases.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();


        $this->assign("page", $p->show());
        $inf = include_once './Common/config2.php';
        foreach ($list as $k => $v) {
            $list[$k]['is_jds'] = $v['is_jd'] == 1 ? "经典" : "普通";
            $list[$k]['status_f'] = $v['status'] == 1 ? "已审核" : "未审核";
        }
        $this->assign("list", $list);
        $this->assign("jd", $inf['zxjd']);


        $this->display();
    }

    /**
     * 装修案例
     * 添加
     */
    public function add_case() {
        if (IS_POST) {
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $gzid = $_POST['gzid'];
            $type = 1;
            $title = trim($_POST['title']);
            $is_jd = $_POST['is_jd'];
            $hid = $_POST['hid'];
            $fid = $_POST['fid'];
            $price = trim($_POST['price']);
            $mianji = trim($_POST['mianji']);
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $goods = trim($_POST['goods']);
            $comments = trim($_POST['comments']);
            $status = $_POST['status'];
            $fengmian = $_POST['fengmian'];
            $jianjie = trim($_POST['jianjie']);


            $m = M("Case");
            $cou = $m->where("title='" . $title . "' and type=1")->count();
            if ($cou > 0) {
                $this->error("该案例已经存在！");
                exit;
            }


            $tpjhlist = $this->gettpjhlist(); #图片集合分类
            $tjarr = $this->gettjkeys($tpjhlist); #图片集合的key值
            $tjinfo = $this->gettjarr($tpjhlist); #

            $path = "/Uploads/product/";
            $info = $this->upload("." . $path);
            $tj = array();

            if (!empty($info)) {
                foreach ($info as $k => $v) {
                    if (in_array($v['key'], $tjarr)) {
                        $i = explode("_", $v['key']);
                        $tj[$i[1]]["title"] = $tjinfo[$v['key']];
                        $tj[$i[1]]["img"][] = $path . $v['savename'];
                    }
                }
                $tuji = json_encode($tj);
            }

            $data = array();
            if (!empty($fengmian))
                $data['fmimg'] = $path . $fengmian;
            if (!empty($tuji))
                $data['img'] = $tuji;
            $data['type'] = 1;
            $data['title'] = $title;
            $data['is_jd'] = $is_jd;
            $data['hid'] = $hid;
            $data['fid'] = $fid;
            $data['price'] = $price;
            $data['mianji'] = $mianji;
            $data['description'] = $description;
            $data['keywords'] = $keywords;
            $data['goods'] = $goods;
            $data['comments'] = $comments;
            $data['jianjie'] = $jianjie;
            $data['p_id'] = $p_id;
            $data['c_id'] = $c_id;
            $data['uid'] = $gzid;
            $data['adduid'] = $_SESSION['my_info']['a_id'];
            $data['addtime'] = time();
            $data['status'] = $status;
            $rs = $m->add($data);
            if ($rs)
                $this->success("操作成功!", U('Gongzhang/list_case'));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $step = $_GET['step'];
        $step = empty($step) ? 1 : $step;
        if ($step == 1) {
            $this->assign("list", $this->getgzh());
            $this->display();
        } else {
            $uid = $_GET['gzid'];
            if (empty($uid)) {
                $this->error("请选择工长！");
                exit;
            }

            $pc = $this->getprovcity($uid);
            $this->assign("p_id", $pc['p_id']);
            $this->assign("c_id", $pc['c_id']);
            $this->assign("gzid", $uid);
            $this->assign("step", $step);

            $this->assign("hxcategory", $this->gethxlist()); #户型
            $this->assign("fgcategory", $this->getfglist()); #风格
            $this->assign("tpjhlist", $this->gettpjhlist()); #图集分类
            $this->display("add_case2");
        }
    }

    /**
     * 装修案例
     * 编辑
     */
    public function edit_case() {
        if (IS_POST) {
            $id = $_POST['id'];
            $title = trim($_POST['title']);
            $is_jd = $_POST['is_jd'];
            $hid = $_POST['hid'];
            $fid = $_POST['fid'];
            $description = trim($_POST['description']);
            $keywords = trim($_POST['keywords']);
            $mianji = trim($_POST['mianji']);
            $price = trim($_POST['price']);
            $goods = trim($_POST["goods"]);
            $comments = trim($_POST['comments']);
            $status = trim($_POST['status']);
            $jianjie = $_POST['jianjie'];
            $fengmian = $_POST['fengmian']; #封面
            $uid = $_POST['uid'];
            $tpjhlist = $this->gettpjhlist(); #图片集合分类
            $tjarr = $this->gettjkeys($tpjhlist); #图片集合的key值
            $tjinfo = $this->gettjarr($tpjhlist); #
            $path = "/Uploads/product/";
            $info = $this->upload("." . $path);
            $tj = array();

            if (!empty($info)) {
                foreach ($info as $k => $v) {
                    if (in_array($v['key'], $tjarr)) {
                        $i = explode("_", $v['key']);
                        $tj[$i[1]]["title"] = $tjinfo[$v['key']];
                        $tj[$i[1]]["img"][] = $path . $v['savename'];
                    }
                }
                $tuji = json_encode($tj);
            }
            $data = array();
            $M = M("Case");
            $info1 = $M->where("id=" . $id)->find();
            if (!empty($fengmian)) {
                $data['fmimg'] = $path . $fengmian;
                unlink("." . $path . $info1['fmimg']);
            }
            if (!empty($tuji))
                $data['img'] = $tuji;
            if ($title != $info1['title'])
                $data['title'] = $title;
            if ($is_jd != $info1['is_jd'])
                $data['is_jd'] = $is_jd;
            if ($hid != $info1['hid'])
                $data['hid'] = $hid;
            if ($fid != $info1['fid'])
                $data['fid'] = $fid;

            if ($price != $info1['price'])
                $data['price'] = $price;
            if ($mianji != $info1['mianji'])
                $data['mianji'] = $mianji;
            if ($description != $info1['description'])
                $data['description'] = $description;
            if ($keywords != $info1['keywords'])
                $data['keywords'] = $keywords;
            if ($goods != $info1['goods'])
                $data['goods'] = $goods;
            if ($comments != $info1['comments'])
                $data['comments'] = $comments;
            if ($jianjie != $info1['jianjie'])
                $data['jianjie'] = $jianjie;
            if ($uid != $info1['uid'])
                $data['uid'] = $uid;
            if ($status != $info1['status'])
                $data['status'] = $status;
            $data['addtime'] = time();
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U('Gongzhang/list_case'));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("hxcategory", $this->gethxlist()); #户型
        $this->assign("fgcategory", $this->getfglist()); #风格
        $this->assign("tpjhlist", $this->gettpjhlist()); #图集分类
        $id = $_GET['id'];
        $M = M("Case");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $this->assign("gzlist", $this->getgzh());
        if (!empty($info['img']))
            $imgsrc = json_decode($info['img']);

        $imglist = array();
        foreach ($imgsrc as $k => $v) {
            $imglist[$k]['img'] = $v->img;
        }
        $this->assign("imglist", $imglist);


        $this->display();
    }

    /**
     * 装修案例
     * 删除
     */
    public function del_case() {
        $id = $_GET['id'];
        $m = M("Case");
        $info = $m->where("id=" . $id)->field("img,fmimg")->find();
        if (!empty($info['fmimg'])) {
            unlink("." . $info['fmimg']);
        }
        if (!empty($info['img'])) {
            $imgjx = json_decode($info['img']);
            #print_r($imgjx);
            foreach ($imgjx as $k => $v) {
                foreach ($v->img as $k1 => $v1) {
                    unlink("." . $v1);
                }
            }
        }

        $rs = $m->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败!");
    }

    /**
     * 修改装修案例状态
     */
    public function casetatus() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 1)
            $statuss = 0;
        else
            $statuss = 1;
        $M = M("Case");
        $rs = $M->where("id=" . $id)->save(array("status" => $statuss));
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败！");
    }

    /**
     * 添加工地
     */
    public function add_gd() {
        if (IS_POST) {
            $name = trim($_POST['name']);
            if (empty($name)) {
                $this->error("施工工地名称不能为空！");
                exit;
            }
            $fengmian = $_POST['fengmian'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $gzid = $_POST['gzid'];
            $status = $_POST['status'];
            $data = array();
            if (!empty($fengmian)) {
                $data['fmimg'] = "/Uploads/product/" . $fengmian;
            }
            if (!empty($name)) {
                $data['name'] = $name;
            }
            $iminfo = $this->upload();
            $imarr = array();
            if (!empty($iminfo)) {
                $k = 0;
                foreach ($iminfo as $k => $v) {
                    if (!empty($v['savename'])) {
                        $imarr[$k] = "/Uploads/product/" . $v['savename'];
                        $k++;
                    }
                }
                if (!empty($imarr))
                    $imstr = json_encode($imarr);
            }
            if (!empty($imstr))
                $data['gdimg'] = $imstr;
            $data['addtime'] = time();
            $data['p_id'] = $p_id;
            $data['c_id'] = $c_id;
            $data['uid'] = $gzid;
            $data['adduid'] = $_SESSION['my_info']['a_id'];
            $data['status'] = $status;
            $M = M("Gongdi");
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！", U("Gongzhang/list_gd"));
            else
                $this->error("操作失败!");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);

        $step = $_GET['step'];
        $step = empty($step) ? 1 : $step;
        if ($step == 1) {
            $this->assign("list", $this->getgzh());
            $this->display();
        } else {
            $uid = $_GET['gzid'];
            if (empty($uid)) {
                $this->error("请选择工长！");
                exit;
            }

            $pc = $this->getprovcity($uid);
            $this->assign("p_id", $pc['p_id']);
            $this->assign("c_id", $pc['c_id']);
            $this->assign("gzid", $uid);
            $this->assign("step", $step);

            $this->assign("hxcategory", $this->gethxlist()); #户型
            $this->assign("fgcategory", $this->getfglist()); #风格
            $this->assign("tpjhlist", $this->gettpjhlist()); #图集分类
            $this->display("add_gd2");
        }
    }

    /**
     * 工地列表
     */
    public function list_gd() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh()); #工长
        $cmod = new CityModel();
        $pro_list = $cmod->getcity(1);
        $this->assign("pro_list", $pro_list);
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $this->assign("is_qx", $is_qx);
        if ($is_qx == 0) {
            $p_id = $_GET['province'];
            $c_id = $_GET['city'];
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
        }
        $keys = $_GET['keys'];
        $keys = $keys == "请输入关键字" ? "" : $keys;
        $uid = $_GET['uid'];

        $this->assign("keys", $keys);
        $this->assign("uid", $uid);
        $this->assign("p_id", $p_id);
        $this->assign("c_id", $c_id);
        $this->assign("cityname", $cmod->getname($c_id));

        import("ORG.Util.Page");
        $where = "1 ";
        if (!empty($p_id))
            $where.=" and Gongdi.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and Gongdi.c_id=" . $c_id;
        if (!empty($keys))
            $where.=" and Gongdi.name like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and Gongdi.uid=" . $uid;


        $M = D("GongdiView");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("Gongdi.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();


        $this->assign("page", $p->show());

        foreach ($list as $k => $v) {
            $list[$k]['status_f'] = $v['status'] == 1 ? "已审核" : "未审核";
        }
        $this->assign("list", $list);



        $this->display();
    }

    /**
     * 修改状态
     * 施工工地
     */
    public function gdstatus() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 1)
            $statuss = 0;
        else
            $statuss = 1;
        $M = M("Gongdi");
        $rs = $M->where("id=" . $id)->save(array("status" => $statuss));
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败！");
    }

    /**
     * 编辑
     * 工地
     */
    public function edit_gd() {
        if (IS_POST) {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            $status = trim($_POST['status']);
            $fengmian = $_POST['fengmian']; #封面
            $uid = $_POST['uid'];

            $path = "/Uploads/product/";
            $iminfo = $this->upload("." . $path);
            $imarr = array();
            if (!empty($iminfo)) {
                $k = 0;
                foreach ($iminfo as $k => $v) {
                    if (!empty($v['savename'])) {
                        $imarr[$k] = "/Uploads/product/" . $v['savename'];
                        $k++;
                    }
                }
                if (!empty($imarr))
                    $imstr = json_encode($imarr);
            }

            $data = array();
            $M = M("Gongdi");
            $info1 = $M->where("id=" . $id)->find();
            if (!empty($fengmian)) {
                $data['fmimg'] = $path . $fengmian;
                unlink("." . $info1['fmimg']);
            }
            if (!empty($imstr)) {
                $gdimg = json_decode($info1['gdimg']);
                foreach ($gdimg as $k => $v) {
                    unlink("." . $v);
                }
                $data['gdimg'] = $imstr;
            }


            if ($name != $info1['name'])
                $data['name'] = $name;

            if ($uid != $info1['uid'])
                $data['uid'] = $uid;
            if ($status != $info1['status'])
                $data['status'] = $status;
            $data['addtime'] = time();
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U('Gongzhang/list_gd'));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Gongdi");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        $this->assign("gzlist", $this->getgzh());
        if (!empty($info['gdimg']))
            $gdimg = json_decode($info['gdimg']);


        $this->assign("imglist", $gdimg);


        $this->display();
    }

    /**
     * 删除
     * 工地
     */
    public function del_gd() {
        $id = $_GET['id'];
        $M = M("Gongdi");
        $rss = $M->where("id=" . $id)->find();
        if (!empty($rss['gdimg'])) {
            $imgarr = json_decode($rss['gdimg']);
            foreach ($imgarr as $k => $v) {
                unlink("." . $v);
            }
        }
        if (!empty($rss['fmimg'])) {
            unlink("." . $rss['fmimg']);
        }

        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功!");
        else
            $this->error("操作失败!");
    }

    /**
     * 口碑点评
     */
    public function koubeicomment() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "type=2";
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
                $this->success("操作成功！", U("Gongzhang/koubeicomment"));
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
        $this->assign("sjmem", $sj['a_name'] . "[" . $sj['truename'] . "]");
        $status_f = $info['status'] == 1 ? "已审核" : "未审核";

        $this->assign("status_f", $status_f);

        $this->display();
    }

    /**
     * 友情商铺列表
     */
    public function firendgx() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $M = M("Gzgxview");
        $where = "1";
        $uid = $_GET['uid'];
        if (!empty($uid))
            $where.=" and uid=" . $uid;

        $cou = $M->where($where)->count();
        $p = new Page($cou, 10);
        $list = $M->where($where)->order("addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        # echo $M->getLastSql();
        $this->assign("list", $list);
        $this->assign("page", $p->show());
        $this->assign("gzlist", $this->getgzh()); #工长
        $this->assign("uid", $uid);

        $this->display();
    }

    /**
     * 添加友情商铺
     */
    public function add_flink() {
        if (IS_POST) {
            $gzid = $_POST['gzid'];
            $gzid1 = $_POST['gzid1'];
            if (empty($gzid) || empty($gzid1)) {
                $this->error("请选择工长！");
                exit;
            }
            if ($gzid == $gzid1) {
                $this->error("您选择的工长相同！");
                exit;
            }
            if ($this->check_flink($gzid, $gzid1)) {
                $this->error("您选择的工长已经是好友！");
                exit;
            }
            $data = array(
                "uid" => $gzid,
                "fuid" => $gzid1,
                "addtime" => time()
            );
            $M = M("Firendgx");
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！");
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("list", $this->getgzh());
        $this->display();
    }

    /**
     * 删除关系
     */
    public function del_flink() {
        $id = $_GET['id'];
        $M = M("Firendgx");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 日记评论
     */
    public function rcomments() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "typeid=1";

        $is_hf = $_GET['is_hf'];
        $this->assign("is_hf", $is_hf);
        $uid = $_GET['uid'];
        $this->assign("uid", $uid);
        if (!empty($is_hf)) {
            $is_hf = $is_hf == 2 ? 0 : $is_hf;
            $where .= " and ishf=" . $is_hf;
        }
        if (!empty($uid))
            $where .= " and arid=" . $uid;

        $M = M("Comments");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        $arrhf = array("未回复", "已回复");
        $arrs = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['is_hf_f'] = $arrhf[$v['ishf']];
            $list[$k]['status_f'] = $arrs[$v['status']];
        }

        $this->assign("list", $list);
        $this->assign("page", $p->show());
        $rjlist = $this->getrj(); #获取装修日记列表
        $this->assign("rjlist", $rjlist);
        $this->display();
    }

    /**
     * 删除日记评论
     */
    public function del_rcomments() {
        $id = $_GET['id'];
        $M = M("Comments");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 修改状态日记评论
     */
    public function status_rcomments() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        $status = $status == 1 ? "0" : "1";
        $data = array("status" => $status);
        $M = M("Comments");
        $rs = $M->where("id=" . $id)->save($data);
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 回复日记评论
     */
    public function hf_rcomments() {
        if (IS_POST) {
            $id = $_POST['id'];
            $hfcontent = trim($_POST['hf_content']);
            if (empty($hfcontent)) {
                $this->error("回复内容不能为空！");
                exit;
            }
            $M = M("Comments");
            $info = $M->where("id=" . $id)->find();
            if ($info['typeid'] == 1)
                $link = U("Gongzhang/rcomments");
            elseif ($info['typeid'] == 2)
                $link = U("Gongzhang/dtcomments");
            elseif ($info['typeid'] == 3)
                $link = U("Gongzhang/casecomments");

            $data = array("ishf" => 1, "hfcontent" => $hfcontent, "hftime" => time());
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", $link);
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $M = M("Comments");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);

        $kh = $this->getkehu_ins($info['uid']);
        $this->assign("khmem", $kh['a_name'] . "[" . $kh['truename'] . "]");
        $this->assign("addtime", date("Y-m-d H:i:s", $info['addtime']));

        $status_f = $info['status'] == 1 ? "已审核" : "未审核";

        $this->assign("status_f", $status_f);
        if ($info['typeid'] == 1){
            $this->assign("rjtitle", $this->getrj_ins($info['arid']));
            $this->assign("links","<a href=".U('Gongzhang/rcomments')." class='a1' >装修日记评论列表</a>");
            $this->assign("tnames","日记");    
        }
        elseif ($info['typeid'] == 2){
            $this->assign("rjtitle", $this->getdt_ins($info['arid']));
            $this->assign("links","<a href=".U('Gongzhang/dtcomments')." class='a1' >施工动态评论列表</a>");
            $this->assign("tnames","施工动态");    
        }
        elseif($info['typeid']==3){
            $this->assign("rjtitle", $this->getcase_ins($info['arid']));
            $this->assign("links","<a href=".U('Gongzhang/casecomments')." class='a1' >装修案例评论列表</a>");
            $this->assign("tnames","装修案例");    
        }
        $this->display();
    }

    /**
     * 施工动态评论
     */
    public function dtcomments() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "typeid=2";

        $is_hf = $_GET['is_hf'];
        $this->assign("is_hf", $is_hf);
        $uid = $_GET['uid'];
        $this->assign("uid", $uid);
        if (!empty($is_hf)) {
            $is_hf = $is_hf == 2 ? 0 : $is_hf;
            $where .= " and ishf=" . $is_hf;
        }
        if (!empty($uid))
            $where .= " and arid=" . $uid;

        $M = M("Comments");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        $arrhf = array("未回复", "已回复");
        $arrs = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['is_hf_f'] = $arrhf[$v['ishf']];
            $list[$k]['status_f'] = $arrs[$v['status']];
        }

        $this->assign("list", $list);
        $this->assign("page", $p->show());
        $rjlist = $this->getdt(); #获取施工动态列表
        $this->assign("rjlist", $rjlist);
        $this->display();
    }

    /**
     * 装修案例 评论
     */
    public function casecomments() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "typeid=3";

        $is_hf = $_GET['is_hf'];
        $this->assign("is_hf", $is_hf);
        $uid = $_GET['uid'];
        $this->assign("uid", $uid);
        if (!empty($is_hf)) {
            $is_hf = $is_hf == 2 ? 0 : $is_hf;
            $where .= " and ishf=" . $is_hf;
        }
        if (!empty($uid))
            $where .= " and arid=" . $uid;

        $M = M("Comments");
        $totalRows = $M->where($where)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();
        $arrhf = array("未回复", "已回复");
        $arrs = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['is_hf_f'] = $arrhf[$v['ishf']];
            $list[$k]['status_f'] = $arrs[$v['status']];
        }

        $this->assign("list", $list);
        $this->assign("page", $p->show());
        $rjlist = $this->getcase(); #获取装修日记列表
        $this->assign("rjlist", $rjlist);
        $this->display();
    }

//----------------------------private----------------------
    /**
     * 图片集合
     * 
     */
    private function gettpjhlist() {
        #图片集合分类
        $tpjhm = M("Tpjh");
        $tpjhlist = $tpjhm->where("1")->select();
        return $tpjhlist;
    }

    /**
     * 户型
     */
    private function gethxlist() {
        $hxmod = M("Hxcategory");
        $hxlist = $hxmod->where(1)->select();
        return $hxlist;
    }

    /**
     * 风格
     */
    private function getfglist() {
        $fgmod = M("Fgcategory");
        $hxlist = $fgmod->where(1)->select();
        return $hxlist;
    }

    /**
     * 获取图片集合分类的key值
     */
    private function gettjkeys($list) {
        $arr = array();
        $i = 0;
        foreach ($list as $k => $v) {
            $arr[$i] = "imgsrc_" . $v['id'];
            $i++;
        }
        return $arr;
    }

    /**
     * 根据从数据库中获取的图片集合
     * 获取图片集合的数组以id为key值
     */
    private function gettjarr($list) {
        $arr = array();
        foreach ($list as $k => $v) {
            $arr["imgsrc_" . $v['id']] = $v['name'];
        }
        return $arr;
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
     * 获取工长列表
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
     * 获取工长详细
     */
    private function getgzh_ins($aid) {
        $where = "a_id=" . $aid;
        $M = M("Foremanview");
        $info = $M->where($where)->field("a_id,a_name,truename")->find();
        return $info;
    }

    /**
     * 检查
     * 日记分类名称是否存在
     */
    private function category_rj_exist($cname, $uid) {
        $m = M("Rijicategory");
        $rs = $m->where(array("cname" => $cname, "uid" => $uid))->count();
        if ($rs > 0)
            return 1;
        else
            return 0;
    }

    /**
     * 检查日记标题是否存在
     */
    private function rj_exist($title) {
        $m = M("Riji");
        $rs = $m->where("title='" . $title . "'")->count();
        if ($rs > 0)
            return 1;
        else
            return 0;
    }

    /**
     * 根据uid
     * 获取
     * 日记分类
     * 
     */
    private function getrijicategory($uid) {
        $M = M("Rijicategory");
        $list = $M->where("uid=" . $uid)->select();

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
     * 检查好友关系是否存在
     */
    private function check_flink($uid, $fuid) {
        $M = M("Firendgx");
        $cou = $M->where(array("uid" => $uid, "fuid" => $fuid))->count();
        if ($cou > 0)
            return 1;
        else
            return 0;
    }

    /**
     * 获取装修日记列表
     */
    private function getrj() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $where = "1";
        if ($is_qx == 1) {
            $where.=" and p_id=" . $_SESSION['my_info']['proid'];
            $where.=" and c_id=" . $_SESSION['my_info']['cityid'];
        }
        $M = M("Riji");
        $list = $M->where($where)->select();
        return $list;
    }

    /**
     * 获取装修日记详细
     */
    private function getrj_ins($id) {

        $where = "id=" . $id;
        $M = M("Riji");
        $list = $M->where($where)->field("title")->find();
        return $list['title'];
    }

    /**
     * 获取装修动态列表
     */
    private function getdt() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $where = "1";
        if ($is_qx == 1) {
            $where.=" and p_id=" . $_SESSION['my_info']['proid'];
            $where.=" and c_id=" . $_SESSION['my_info']['cityid'];
        }
        $M = M("Shigongdt");
        $list = $M->where($where)->select();
        return $list;
    }

    /**
     * 获取施工动态详细
     */
    private function getdt_ins($id) {

        $where = "id=" . $id;
        $M = M("Shigongdt");
        $list = $M->where($where)->field("title")->find();
        return $list['title'];
    }
/**
     * 获取装修案例列表
     */
    private function getcase() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $where = "type=1";
        if ($is_qx == 1) {
            $where.=" and p_id=" . $_SESSION['my_info']['proid'];
            $where.=" and c_id=" . $_SESSION['my_info']['cityid'];
        }
        $M = M("Case");
        $list = $M->where($where)->select();
        return $list;
    }

    /**
     * 获取装修案例详细
     */
    private function getcase_ins($id) {

        $where = "id=" . $id;
        $M = M("Case");
        $list = $M->where($where)->field("title")->find();
        return $list['title'];
    }
    //------------------------ajax--------------
    /**
     * ajax 获取工长
     */
    public function ajaxgetgz() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = trim($_POST['gname']);
        if (!empty($gname)) {
            $M = M("Foremanview");
            $list = $M->where("a_name like '%" . $gname . "%' or truename like '%" . $gname . "%'")->select();
            if ($list)
                $data = array("status" => 1, "data" => $list);
            else
                $data = array("status" => 0, "data" => "");
        }else {
            $M = M("Foremanview");
            $list = $M->where("a_name like '%" . $gname . "%' or truename like '%" . $gname . "%'")->select();
            if ($list)
                $data = array("status" => 1, "data" => $list);
            else
                $data = array("status" => 0, "data" => "");
        }
        echo json_encode($data);
    }

    /**
     * ajax
     * 改变图片集合名称
     */
    public function ajaxupdatejh() {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $m = M("Tpjh");
        $rr = $m->where("id=" . $id)->save(array("name" => $name));

        if ($rr)
            echo 1;
        else
            echo 0;
    }

    /**
     * 修改排序
     */
    public function ajaxupdatesort() {
        $sort = $_POST['sort'];
        $id = $_POST['id'];
        $m = M("Shigongdt");
        $rs = $m->where("id=" . $id)->save(array("sort" => $sort));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

    /**
     * 快速修改
     * 日记 点击次数
     * 
     */
    public function ajaxupdate_rj_click() {
        $sort = $_POST['sort'];
        $id = $_POST['id'];
        $m = M("Riji");
        $rs = $m->where("id=" . $id)->save(array("click" => $sort));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

    /**
     * 快速
     * ajax
     * 修改日记分类名称
     */
    public function ajaxupdate_cate_rj() {
        $name = $_POST['name'];
        $id = $_POST['id'];
        $m = M("Rijicategory");
        $rs = $m->where("cid=" . $id)->save(array("cname" => $name));
        if ($rs)
            echo 1;
        else
            echo 0;
    }

}

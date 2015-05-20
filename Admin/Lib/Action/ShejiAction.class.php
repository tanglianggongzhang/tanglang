<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShejiAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ShejiAction extends CommonAction {

    /**
     * 设计师案例
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $this->assign("gzlist", $this->getgzh()); #设计师
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
        $where = "cases.type=2 ";
        if (!empty($p_id))
            $where.=" and cases.p_id=" . $p_id;
        if (!empty($c_id))
            $where.=" and cases.c_id=" . $c_id;
        if (!empty($keys))
            $where.=" and cases.title like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and cases.uid=" . $uid;


        $M = D("SjcaseView");
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
     * 添加
     * 设计师
     */
    public function add_sj_case() {
        if (IS_POST) {
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $gzid = $_POST['gzid'];
            $type = 2;
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
            $cou = $m->where("title='" . $title . "' and type=2")->count();
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
            $data['type'] = 2;
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
                $this->success("操作成功!", U('Sheji/index'));
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
                $this->error("请选择设计师！");
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
            $this->display("add_sj_case2");
        }
    }

    /**
     * 修改状态
     */
    public function casetatus() {
        $status = $_GET['status'];
        $id = $_GET['id'];
        $M = M("Case");
        if ($status == 1)
            $ups = 0;
        else
            $ups = 1;

        $rs = $M->where("id=" . $id)->save(array("status" => $ups));
        if ($rs) {
            $this->success("操作成功！");
        } else {
            $this->error("操作失败！");
        }
    }

    /**
     * 删除案例
     */
    public function del_case() {
        $id = $_GET['id'];
        $M = M("Case");
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 编辑案例
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
                $this->success("操作成功！", U('Sheji/index'));
            else
                $this->error("操作失败！");

            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        if (empty($id)) {
            $this->error("请选择要编辑的案例");
            exit;
        }
        $M = M("Case");
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);

        $this->assign("gzlist", $this->getgzh()); #工长
        $this->assign("hxcategory", $this->gethxlist()); #户型
        $this->assign("fgcategory", $this->getfglist()); #风格
        $this->assign("tpjhlist", $this->gettpjhlist()); #图集分类
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
                "type" => 1
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
        $where = "Hdp.type=1 ";

        if (!empty($keys))
            $where.=" and Hdp.name like '%" . $keys . "%'";
        if (!empty($uid))
            $where.=" and Hdp.uid=" . $uid;


        $M = D("HdpView");
        $totalRows = $M->where($where)->count();

        $p = new Page($totalRows, 10);
        $list = $M->where($where)->order("Hdp.addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();


        $this->assign("page", $p->show());
        $inf = include_once './Common/config2.php';
        foreach ($list as $k => $v) {

            $list[$k]['status_f'] = $v['status'] == 1 ? "已审核" : "未审核";
        }
        $this->assign("list", $list);
        $this->assign("jd", $inf['zxjd']);


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
                $this->success("操作成功！", U('Sheji/list_hdp'));
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
     * 口碑点评
     */
    public function koubeicomment() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "type=3";
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
                $this->success("操作成功！", U("Sheji/koubeicomment"));
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
     * 装修案例 评论
     */
    public function casecomments() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "typeid=5";

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
            
            $link = U("Sheji/casecomments");

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

        $this->assign("rjtitle", $this->getcase_ins($info['arid']));
        $this->assign("links", "<a href=" . U('Sheji/casecomments') . " class='a1' >设计案例评论列表</a>");
        $this->assign("tnames", "设计案例");

        $this->display();
    }

    //------------------------------------------------------------------
    /**
     * 获取设计师列表
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
        $M = M("Shejiview");
        $list = $M->where($where)->field("a_id,a_name,truename")->select();

        return $list;
    }

    /**
     * 获取工长详细
     */
    private function getgzh_ins($aid) {
        $where = "a_id=" . $aid;
        $M = M("Shejiview");
        $info = $M->where($where)->field("a_id,a_name,truename")->find();
        return $info;
    }

    /**
     * 根据用户id 获取用户所属的省市
     * 
     */
    private function getprovcity($uid) {
        $m = M("Shejiview");
        $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        return $info;
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
     * 获取装修案例列表
     */
    private function getcase() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $where = "type=2";
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
    /**
     * 获取客户详细
     */
    private function getkehu_ins($aid) {
        $where = "a_id=" . $aid;
        $M = M("Kehuview");
        $info = $M->where($where)->field("a_id,a_name,truename")->find();
        return $info;
    }
    //-------------------------------------------------------------------
    /**
     * ajax 
     * 获取设计师
     */
    public function ajaxgetsj() {
        header('Content-Type:application/json; charset=utf-8');
        $gname = trim($_POST['gname']);
        if (!empty($gname)) {
            $M = M("Shejiview");
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

    

}

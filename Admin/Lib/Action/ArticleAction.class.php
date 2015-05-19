<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ArticleAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class ArticleAction extends CommonAction {

    /**
     * 列表文章
     */
    public function index() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $M = M("Article");
        $keys = trim($_GET['keys']);
        $keys = $keys == "请输入关键字" ? "" : $keys;

        $map = "1";
        if (!empty($keys))
            $map.=" and title like '%" . $keys . "%'";
        $this->assign("keys", $keys);

        $totalRows = $M->where($map)->count();
        $p = new Page($totalRows, 10);
        $list = $M->where($map)->order("addtime desc")->limit($p->firstRow . "," . $p->listRows)->select();

        $this->assign("page", $p->show());
        $arr1 = array("普通", "推荐");
        $arr2 = array("未审核", "已审核");
        foreach ($list as $k => $v) {
            $list[$k]['is_tj_f'] = $arr1[$v['is_tj']];
            $list[$k]['status_f'] = $arr2[$v['status']];
            $list[$k]['uname'] = $this->getnamebyid($v['uid']);
            $list[$k]['addtime_f'] = date("Y-m-d H:i:s", $v['addtime']);
        }
        $this->assign("list", $list);

        $this->display();
    }

    /**
     * 添加文章
     */
    public function add_art() {
        if (IS_POST) {
            $title = trim($_POST['title']);
            $classid = $_POST['classid'];
            $keywords = trim($_POST['keywords']);
            $description = trim($_POST['description']);
            $fengmian = trim($_POST['fengmian']);
            $is_tj = $_POST['is_tj'];
            $content = trim($_POST['content']);
            $click = trim($_POST['click']);
            $uid = $_POST['uid'];
            $status = $_POST['status'];
            $parr = $this->getprovcity($uid);
            $p_id = $parr['p_id'];
            $c_id = $parr['c_id'];


            if (empty($title)) {
                $this->error("标题不能为空！");
                exit;
            }
            $M = new ArticleModel();
            if ($M->check_art($title)) {
                $this->error("标题已经存在！");
                exit;
            }
            if (empty($classid)) {
                $this->error("请选择分类");
                exit;
            }
            if (!empty($fengmian))
                $fmimg = "/Uploads/product/" . $fengmian;
            $data = array(
                "title" => $title,
                "classid" => $classid,
                "keywords" => $keywords,
                "description" => $description,
                "fmimg" => $fmimg,
                "is_tj" => $is_tj,
                "content" => $content,
                "addtime" => time(),
                "click" => $click,
                "uid" => $uid,
                "adduid" => $_SESSION['my_info']['a_id'],
                "p_id" => $p_id,
                "c_id" => $c_id,
                "status" => $status
            );
            $rs = $M->add($data);
            if ($rs)
                $this->success("操作成功！", U("Article/index"));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        #文章分类
        $this->assign("artlist", $this->getartclass());
        #会员列表
        $this->assign("ulist", $this->getumemlist());

        $this->display();
    }

    /**
     * 编辑文章
     */
    public function edit_art() {
        if (IS_POST) {
            $id = $_POST['id'];
            $M = new ArticleModel();
            $info = $M->where("id=" . $id)->find();
            $title = trim($_POST['title']);
            $uid = $_POST['uid'];
            $description = $_POST['description'];
            $keywords = $_POST['keywords'];
            $status = $_POST['status'];
            $is_tj = $_POST['is_tj'];
            $click = $_POST['click'];
            $classid = $_POST['classid'];
            $fengmian = $_POST['fengmian'];
            if (!empty($fengmian))
                $fengmian = "/Uploads/product/" . $fengmian;
            $content = trim($_POST['content']);

            if (empty($title)) {
                $this->error("标题不能为空！");
                exit;
            }
            $data = array();
            if ($title != $info['title'])
                $data['title'] = $title;
            if ($classid != $info['classid'])
                $data['classid'] = $classid;
            if ($uid != $info['uid'])
                $data['uid'] = $uid;
            if ($keywords != $info['keywords'])
                $data['keywords'] = $keywords;
            if ($description != $info['description'])
                $data['description'] = $description;
            if ($fengmian != $info['fmimg'])
                $data['fmimg'] = $fengmian;
            if ($is_tj != $info['is_tj'])
                $data['is_tj'] = $is_tj;
            if ($content != $info['content'])
                $data['content'] = $content;
            if ($click != $info['click'])
                $data['click'] = $click;
            $data['addtime'] = time();
            $rs = $M->where("id=" . $id)->save($data);
            if ($rs)
                $this->success("操作成功！", U('Article/index'));
            else
                $this->error("操作失败！");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        if (empty($id)) {
            $this->error("请选择所编辑的文章");
            exit;
        }
        $M = new ArticleModel();
        $info = $M->where("id=" . $id)->find();
        $this->assign("info", $info);
        #文章分类
        $this->assign("artlist", $this->getartclass());
        #会员列表
        $this->assign("ulist", $this->getumemlist());

        $this->display("add_art");
    }

    /**
     * 删除文章
     */
    public function del_art() {
        $id = $_GET['id'];
        $M = new ArticleModel();
        $rs = $M->where("id=" . $id)->delete();
        if ($rs)
            $this->success("删除文章成功！");
        else
            $this->error("删除文章失败！");
    }

    /**
     * 编辑文章状态
     */
    public function edit_status() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        if ($status == 1)
            $statusu = 0;
        else
            $statusu = 1;
        $M = new ArticleModel();
        $data = array("status" => $statusu);
        $rs = $M->where("id=" . $id)->save($data);
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 编辑文章推荐
     */
    public function edit_tj() {
        $id = $_GET['id'];
        $is_tj = $_GET['is_tj'];
        if ($is_tj == 1)
            $is_tj = 0;
        else
            $is_tj = 1;
        $M = new ArticleModel();
        $data = array("is_tj" => $is_tj);
        $rs = $M->where("id=" . $id)->save($data);
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 文章分类
     */
    public function artcategory() {
        if (IS_POST) {
            $cname = trim($_POST['cname']);
            $artmod = new ArtcategoryModel();
            if (empty($cname)) {
                $this->error("分类名称不能为空！");
                exit;
            }
            if ($artmod->checkname($cname)) {
                $this->error("分类名称已经存在！");
                exit;
            }
            $pid = $_POST['pid'];
            if ($pid == 0) {
                $level = 0;
            } else {
                $level = $artmod->getlevel($pid);
                $level = $level + 1;
            }
            $data = array(
                "cname" => $cname,
                "pid" => $pid,
                "level" => $level
            );
            $rs = $artmod->add($data);
            if ($rs)
                $this->success("操作成功！");
            else
                $this->error("操作失败!");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $artmod = new ArtcategoryModel();
        $artlist = $artmod->getcategory();

        $this->assign("list", $artlist);

        $this->display();
    }

    /**
     * 删除分类
     */
    public function del_cat() {
        $cid = $_GET['cid'];
        $M = M("Artcategory");
        $rs = $M->where("cid=" . $cid)->delete();
        if ($rs)
            $this->success("操作成功！");
        else
            $this->error("操作失败！");
    }

    /**
     * 修改分类名称
     */
    public function xgcname() {
        header('Content-Type:application/json; charset=utf-8');
        $cname = trim($_POST['cname']);
        $id = $_POST['id'];
        $M = M("Artcategory");
        $data = array("cname" => $cname);
        $rs = $M->where("cid=" . $id)->save($data);
        echo $rs;
    }

    /**
     * 文章评论
     */
    public function comments() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        import("ORG.Util.Page");
        $where = "typeid=4";

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

            $link = U("Article/comments");

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
        $this->assign("links", "<a href=" . U('Article/comments') . " class='a1' >文章评论</a>");
        $this->assign("tnames", "文章");

        $this->display();
    }

    //----------------
    /**
     * 获取省id和市id
     * @param type $uid
     * @return type
     */
    private function getprovcity($uid) {
        $M2 = M("Member");
        $info1 = $M2->where("a_id=" . $uid)->find();
        if ($info1['a_type'] == 1) {
            //普通
            $m = M("Webmember");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        } elseif ($info1['a_type'] == 2) {
            //工长
            $m = M("ForemanInfo");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        } elseif ($info1['a_type'] == 3) {
            //店铺
            $m = M("Dianpu");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        } elseif ($info1['a_type'] == 4) {
            //设计
            $m = M("Sheji");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        } else {
            //工人
            $m = M("Gongren");
            $info = $m->where("a_id=" . $uid)->field("p_id,c_id")->find();
        }
        return $info;
    }

    /**
     * 根据uid
     * 获取真实姓名和会员名
     */
    private function getnamebyid($uid) {
        $M2 = M("Member");
        $info1 = $M2->where("a_id=" . $uid)->find();
        if ($info1['a_type'] == 1) {
            //普通
            $m = M("Kehuview");
            $info = $m->where("a_id=" . $uid)->field("truename,a_name")->find();
        } elseif ($info1['a_type'] == 2) {
            //工长
            $m = M("Foremanview");
            $info = $m->where("a_id=" . $uid)->field("truename,a_name")->find();
        } elseif ($info1['a_type'] == 3) {
            //店铺
            $m = M("Dianpumember");
            $info = $m->where("a_id=" . $uid)->field("a_name,company as truename")->find();
        } elseif ($info1['a_type'] == 4) {
            //设计
            $m = M("Shejiview");
            $info = $m->where("a_id=" . $uid)->field("a_name,truename")->find();
        } else {
            //工人
            $m = M("Gongrenview");
            $info = $m->where("a_id=" . $uid)->field("a_name,truename")->find();
        }
        $str = $info['a_name'] . "-" . $info['truename'];
        return $str;
    }

    /**
     * 获取文章分类
     */
    private function getartclass() {
        $artmod = new ArtcategoryModel();
        $artlist = $artmod->getcategory();
        return $artlist;
    }

    /**
     * 获取会员
     */
    private function getumemlist() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        if ($is_qx == 1) {
            $p_id = $_SESSION['my_info']['p_id'];
            $c_id = $_SESSION['my_info']['c_id'];

            $map = " and p_id=" . $p_id . " and c_id=" . $c_id;
        }
        //普通会员
        $M1 = M("Kehuview");
        $list1 = $M1->where("status=1 " . $map)->field("a_id,a_name,truename")->select();
        //工长
        $M2 = M("Foremanview");
        $list2 = $M2->where("status=1 " . $map)->field("a_id,a_name,truename")->select();

        //店铺
        $M3 = M("Dianpumember");
        $list3 = $M3->where("status=1 " . $map)->field("a_id,a_name,company")->select();


        //设计
        $M4 = M("Shejiview");
        $list4 = $M4->where("status=1 " . $map)->field("a_id,a_name,truename")->select();
        //工人
        $M5 = M("Gongrenview");
        $list5 = $M5->where("status=1 " . $map)->field("a_id,a_name,truename")->select();

        $ulist = array();
        foreach ($list1 as $k => $v) {
            $ulist[$v['a_id']]['a_id'] = $v['a_id'];
            $ulist[$v['a_id']]['a_name'] = $v['a_name'];
            $ulist[$v['a_id']]['truename'] = $v['truename'];
        }
        foreach ($list2 as $k1 => $v1) {
            $ulist[$v1['a_id']]['a_id'] = $v1['a_id'];
            $ulist[$v1['a_id']]['a_name'] = $v1['a_name'];
            $ulist[$v1['a_id']]['truename'] = $v1['truename'];
        }
        foreach ($list3 as $k2 => $v2) {
            $ulist[$v2['a_id']]['a_id'] = $v2['a_id'];
            $ulist[$v2['a_id']]['a_name'] = $v2['a_name'];
            $ulist[$v2['a_id']]['truename'] = $v2['company'];
        }
        foreach ($list4 as $k3 => $v3) {
            $ulist[$v3['a_id']]['a_id'] = $v3['a_id'];
            $ulist[$v3['a_id']]['a_name'] = $v3['a_name'];
            $ulist[$v3['a_id']]['truename'] = $v3['truename'];
        }
        foreach ($list5 as $k4 => $v4) {
            $ulist[$v4['a_id']]['a_id'] = $v4['a_id'];
            $ulist[$v4['a_id']]['a_name'] = $v4['a_name'];
            $ulist[$v4['a_id']]['truename'] = $v4['truename'];
        }
        return $ulist;
    }

    /**
     * 获取装修案例列表
     */
    private function getcase() {
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $where = "1";
        if ($is_qx == 1) {
            $where.=" and p_id=" . $_SESSION['my_info']['proid'];
            $where.=" and c_id=" . $_SESSION['my_info']['cityid'];
        }
        $M = M("Article");
        $list = $M->where($where)->select();
        return $list;
    }

    /**
     * 获取装修案例详细
     */
    private function getcase_ins($id) {

        $where = "id=" . $id;
        $M = M("Article");
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

}

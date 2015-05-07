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
        $is_qx=$_SESSION['my_info']['role'];
        $where ="1";
        if($is_qx==1){
            $p_id=$_SESSION['my_info']['proid'];
            $c_id=$_SESSION['my_info']['cityid'];
            
            $where.=" and p_id=".$p_id;
            $where.=" and c_id=".$c_id;
        }
        $M = M("Foremanview");
        $list = $M->where($where)->field("a_id,a_name,truename")->select();
        return $list;
    }

}

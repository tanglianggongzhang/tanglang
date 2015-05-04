<?php

/**
 * 
 * @author 李雪莲 <lixuelianlk@163.com>
 * 前台用户  登录
 * 2015-03-06 15:14
 */
class MemberAction extends CommonAction {

    /**
     * 列表页面
     */
    public function index() {
        #var_dump($_SESSION[C('USER_AUTH_KEY')]);exit;
        parent::_initalize();

        $systemConfig = $this->systemConfig;
        $this->assign("systemConfig", $systemConfig);
        #获取省份
        $citymod = new CityModel();

        $memmod = new MemberModel();
        $year = $memmod->getyear();
        $this->assign("year", $year);
        $month = $memmod->getmonth();
        $this->assign("month", $month);
        $days = $memmod->getday($year[0], $month[0]);
        $this->assign("day", $days);

        //列表页面---------------start


        $keys = trim($_GET['keys']);
        $keys = ($keys == '请输入关键字') ? "" : $keys;
        $start_date = trim($_GET['start_date']);
        $end_date = trim($_GET['end_date']);
        $province = trim($_GET['province']);
        $city = trim($_GET['city']);
        $qu = trim($_GET['qu']);

        $this->assign("keys", $keys);
        $this->assign("start_date", $start_date);
        $this->assign("end_date", $end_date);
        $this->assign("province", $province);
        $this->assign("city", $city);
        $this->assign("qu", $qu);


        $cityname = $citymod->getname($city);
        $this->assign("cityname", $cityname);
        $map = "a_type=1 ";
        if (!empty($start_date)) {
            $start_date = strtotime($start_date . "0:0:0");
            $map.=" and UNIX_TIMESTAMP(create_time)>= " . $start_date;
        }
        if (!empty($end_date)) {
            $end_date = strtotime($end_date . "23:59:59");
            $map.=" and UNIX_TIMESTAMP(create_time)<= " . $end_date;
        }
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            //非地区管理员
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);

            if (!empty($province)) {
                $map.=" and p_id=" . $province;
            }
            if (!empty($city)) {
                $map.=" and c_id=" . $city;
            }
            $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));
        } else {
            $map.=" and p_id=" . $_SESSION['my_info']['proid'];
            $map .= " and c_id=" . $_SESSION['my_info']['cityid'];
            #区
            $qlist = $citymod->getcity($_SESSION['my_info']['cityid']);
            $this->assign("qlist", $qlist);

            $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));
        }

        if (!empty($keys)) {
            $map.=" and (nickname like '%" . $keys . "%' or truename like '%" . $keys . "%' )";
        }
        if (!empty($qu))
            $map.=" and q_id=" . $qu;
        import("ORG.Util.Page");
        $pumod = M("Kehu");
        $count = $pumod->where($map)->count();

        $page = new Page($count, 12);
        $showpage = $page->show();
        $list = $pumod->where($map)->order("a_id desc")->limit($page->firstRow . "," . $page->listRows)->select();
        foreach ($list as $k => $v) {
            if (!empty($vo['last_login']))
                $list[$k]['last_login'] = date("Y-m-d", $vo['last_login']);
            else
                $list[$k]['last_login'] = "从未登录";

            $list[$k]['sex'] = ($vo['sex'] == 1) ? "男" : "女";
        }
        $this->assign("list", $list);

        $this->assign("page", $showpage);
        //列表页面--------------end

        $this->display();
    }

    /**
     * ajax
     * 添加保存
     */
    public function addmember() {
        header("Content-Type:text/html; charset=utf-8");
        header('Content-Type:application/json; charset=utf-8');
        $a_name = trim($_POST['a_name']);
        $pwd = trim($_POST['pwd']);
        $confpwd = trim($_POST['cof_pwd']);
        $returnarr = array();
        if (empty($a_name)) {
            $returnarr['status'] = 0;
            $returnarr['info'] = "登录名不能为空";
            echo json_encode($returnarr);
            exit;
        }
        if ($pwd != $confpwd) {

            $returnarr['status'] = 0;
            $returnarr['info'] = "两次输入的密码必须一致";
            echo json_encode($returnarr);

            exit;
        }
        //检查登录名是否已经在数据库中存在
        $memmod = new MemberModel();
        if ($memmod->check_name($a_name) > 0) {
            $returnarr['status'] = 0;
            $returnarr['info'] = "登录名已经存在，请重新命名";
            echo json_encode($returnarr);
            exit;
        }

        $maindata = array(
            "a_name" => $a_name,
            "a_pwd" => encrypt($pwd),
            "a_type" => 1,
            "status" => 1
        );
        $nickname = trim($_POST['nickname']);
        $truename = trim($_POST['truename']);
        $sex = trim($_POST['sex']);
        $email = trim($_POST['email']);
        $qq = trim($_POST['qq']);
        $movphone = trim($_POST['telphone']);
        $birthday = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
        $cityid = $_POST['cityid'];
        $proid = $_POST['proid'];
        if (empty($proid)) {
            $returnarr['status'] = 0;
            $returnarr['info'] = "请选择省份";
            echo json_encode($returnarr);
            exit;
        }
        if (empty($cityid)) {
            $returnarr['status'] = 0;
            $returnarr['info'] = "请选择城市";
            echo json_encode($returnarr);
            exit;
        }
        $fjdata = array(
            "nickname" => $nickname,
            "truename" => $truename,
            "sex" => $sex,
            "email" => $email,
            "qq" => $qq,
            "movphone" => $movphone,
            "birthday" => $birthday,
            "city_id" => $cityid,
            "province_id" => $proid
        );



        $res = $memmod->add_member($maindata, $fjdata);

        echo json_encode($res);

        exit;
    }

    /**
     * 获取城市
     */
    public function getcity() {

        header('Content-Type:application/json; charset=utf-8');
        $pid = $_POST['fid'];
        $citymod = new CityModel();
        $pro_list = $citymod->getprovince($pid);
        if ($pro_list)
            echo json_encode(array("status" => 1, "data" => $pro_list));
        else
            echo json_encode(array("status" => 0, "data" => array()));
    }

    /**
     * 获取地区
     */
    public function getqu() {

        header('Content-Type:application/json; charset=utf-8');

        $cid = $_POST['cid'];

        $citymod = new CityModel();
        $pro_list = $citymod->getqu($cid);
        if ($pro_list)
            echo json_encode(array("status" => 1, "data" => $pro_list));
        else
            echo json_encode(array("status" => 0, "data" => array()));
    }

    /**
     * 获取日期
     */
    public function getdays() {
        header("Content-Type:text/html; charset=utf-8");
        header('Content-Type:application/json; charset=utf-8');
        $year = $_POST['year'];
        $mon = $_POST['mon'];
        $memmod = new MemberModel();
        $list = $memmod->getday($year, $mon);
        echo json_encode($list);
    }

    /**
     * 查看
     */
    public function getinfo() {

        header('Content-Type:application/json; charset=utf-8');

        $aid = $_POST['aid'];
        $ptmod = M("Kehuview");
        $info = $ptmod->where("a_id=" . $aid)->find();
        $arr = include_once './Common/config2.php';
        if (!$_POST['is_cl']) {
            $info['sex'] = ($info['sex'] == 1) ? "男" : "女";
            if (!empty($info['last_login']))
                $info['last_login'] = date("Y-m-d", $info['last_login']);
            else
                $info['last_login'] = "从未登录";
            $info['zxjd'] = $arr['zxjd'][$info['zxjd']];
            $info['status'] = $info['status'] == 1 ? "启用" : "禁用";
        }

        echo json_encode($info);
    }

    /**
     * 编辑
     */
    public function edit_user() {
        header('Content-Type:application/json; charset=utf-8');
        $aid = $_POST['aid'];
        $a_name = trim($_POST['a_name']);
        $pwd = trim($_POST['pwd']);
        $cof_pwd = trim($_POST['cof_pwd']);
        $nickname = trim($_POST['nickname']);
        $truename = trim($_POST['truename']);
        $sex = trim($_POST['sex']);
        $email = trim($_POST['email']);
        $qq = trim($_POST['qq']);
        $telphone = trim($_POST['telphone']);
        $year = trim($_POST['year']);
        $month = trim($_POST['month']);
        $day = trim($_POST['day']);
        $proid = trim($_POST['proid']);
        $cityid = trim($_POST['cityid']);
        $birthday = $year . "-" . $month . "-" . $day;


        $amod = new Admininfo1Model();
        $ainfo = $amod->getinfo($aid, 0);
        $data = array();
        if ($ainfo['a_name'] != $a_name) {
            //修改账户
            $data['a_name'] = $a_name;
        }
        if ($pwd != "" && $cof_pwd != "" && $pwd == $cof_pwd) {
            //两次输入的密码一致
            $data['a_pwd'] = encrypt($pwd);
        }
        //编辑登录账户
        $mod = M("Member");
        $mod->where(array("a_id" => $aid))->save($data);
        #echo $mod->getLastSql();
        $datainfo = array();
        if ($ainfo['nickname'] != $nickname)
            $datainfo['nickname'] = $nickname;

        if ($ainfo['truename'] != $truename)
            $datainfo['truename'] = $truename;

        if ($ainfo['sex'] != $sex)
            $datainfo['sex'] = $sex;

        if ($ainfo['email'] != $email)
            $datainfo['email'] = $email;

        if ($ainfo['qq'] != $qq)
            $datainfo['qq'] = $qq;

        if ($ainfo['movphone'] != $telphone)
            $datainfo['movphone'] = $telphone;

        if ($ainfo['birthday'] != $birthday)
            $datainfo['birthday'] = $birthday;

        if ($ainfo['city_id'] != $cityid)
            $datainfo['city_id'] = $cityid;

        if ($ainfo['province_id'] != $proid)
            $datainfo['province_id'] = $proid;

        //编辑详情页面
        $infmod = M("Webmember");
        $infmod->where(array("a_id" => $aid))->save($datainfo);

        echo json_encode(array("status" => 1, "info" => "操作成功"));
    }

    /**
     * 删除
     */
    public function del() {
        $aid = $_GET['aid'];
        $mod1=M("Kehuview");
        $info=$mod1->where("a_id=".$aid)->find();
        if(!empty($info['logo'])){
            unlink("./avatar/".$info['logo']);
            unlink("./avatar/".$info['logo']."_30.jpg");
            unlink("./avatar/".$info['logo']."_60.jpg");
            unlink("./avatar/".$info['logo']."_100.jpg");
        }
        
        $mod = M("Member");
        $imod = M("Webmember");
        $res = $mod->where(array("a_id" => $aid))->delete();
        $res2 = $imod->where(array("a_id" => $aid))->delete();
        if ($res && $res2) {
            $this->success("操作成功");
        } else {
            $this->error("操作失败");
        }
    }

    /**
     * 明星工长
     */
    public function foreman() {
        parent::_initalize();
        $systemConfig = $this->systemConfig;
        $this->assign("systemConfig", $systemConfig);
        if ($_GET['is_sq']) {
            //申请列表
            $m = M("Shenqingview");
            import("ORG.Util.Page");
            $status = $_GET['status'];
            $map = "status=" . $status . " and u_type=2";

            $keys = trim($_GET['keys']);

            $keys = ($keys == "请输入关键字") ? "" : $keys;

            $start_date = trim($_GET['start_date']);
            $this->assign("start_date", $start_date);

            $end_date = trim($_GET['end_date']);
            $this->assign("end_date", $end_date);

            if (!empty($start_date)) {
                $start_date = strtotime($start_date . "0:0:0");
                $map.=" and UNIX_TIMESTAMP(addtime)>= " . $start_date;
            }
            if (!empty($end_date)) {
                $end_date = strtotime($end_date . "23:59:59");
                $map.=" and UNIX_TIMESTAMP(addtime)<= " . $end_date;
            }
            if ($_SESSION['my_info']['role'] != 2) {
                //非地区管理员
                $province = $_GET['province'];
                $this->assign("province", $province);
                $city = $_GET['city'];
                if (!empty($province)) {
                    $map.=" and province_id=" . $province;
                }
                if (!empty($city)) {
                    $map.=" and city_id=" . $city;
                }
                $this->assign("city", $city);
            } else {
                if ($_SESSION['my_info']['cityid'])
                    $map.=" and city_id=" . $_SESSION['my_info']['cityid'];
                else {
                    $this->error("该管理员没有分配城市顾没有权限查看此页！", U('Index/index'));
                    exit;
                }
            }



            if (!empty($keys)) {
                $map.=" and (truename like '%" . $keys . "%' or nickname like '%" . $keys . "%')";
            }

            $cou = $m->where($map)->order("u_id desc")->count();
            $page = new Page($cou, 12);
            $showpage = $page->show();
            $list = $m->where($map)->order("u_id desc")->select();
            foreach ($list as $k => $v) {
                $list[$k]['sex_f'] = $this->get_sex($v['sex']);
                $list[$k]['status_f'] = $this->get_status($v['status']);
            }
            $this->assign("list", $list);

            $this->assign("page", $showpage);

            $citymod = new CityModel();
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);

            $this->assign("keys", $keys);

            $cityname = $citymod->getname($city);
            $this->assign("cityname", $cityname);

            $this->display("sqlist" . $status);
            exit;
        } else {
            $m = M("Foremanview");
            import("ORG.Util.Page");
            $map = "1";

            $keys = trim($_GET['keys']);

            $keys = ($keys == "请输入关键字") ? "" : $keys;

            $start_date = trim($_GET['start_date']);
            $this->assign("start_date", $start_date);
            $end_date = trim($_GET['end_date']);
            $this->assign("end_date", $end_date);

            if (!empty($start_date)) {
                $start_date = strtotime($start_date . "0:0:0");
                $map.=" and UNIX_TIMESTAMP(f_addtime)>= " . $start_date;
            }
            if (!empty($end_date)) {
                $end_date = strtotime($end_date . "23:59:59");
                $map.=" and UNIX_TIMESTAMP(f_addtime)<= " . $end_date;
            }
            if ($_SESSION['my_info']['role'] != 2) {
                //非地区管理员
                $province = $_GET['province'];
                $this->assign("province", $province);
                $city = $_GET['city'];
                if (!empty($province)) {
                    $map.=" and f_p_id=" . $province;
                }
                if (!empty($city)) {
                    $map.=" and f_c_id=" . $city;
                }
                $this->assign("city", $city);
            } else {
                if ($_SESSION['my_info']['cityid'])
                    $map.=" and f_c_id=" . $_SESSION['my_info']['cityid'];
                else {
                    $this->error("该管理员没有分配城市顾没有权限查看此页！", U('Index/index'));
                    exit;
                }
            }



            if (!empty($keys)) {
                $map.=" and (f_company like '%" . $keys . "%' or f_truename like '%" . $keys . "%')";
            }


            $cou = $m->where($map)->order("a_id desc")->count();

            $page = new Page($cou, 12);
            $showpage = $page->show();
            $list = $m->where($map)->order("a_id desc")->select();
            $this->assign("list", $list);
            $this->assign("page", $showpage);


            $memmod = new MemberModel();
            $year = $memmod->getyear();
            $this->assign("year", $year);
            $month = $memmod->getmonth();
            $this->assign("month", $month);
            $days = $memmod->getday($year[0], $month[0]);
            $this->assign("day", $days);

            $citymod = new CityModel();
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);

            $this->assign("keys", $keys);

            $cityname = $citymod->getname($city);
            $this->assign("cityname", $cityname);

            $this->display();
        }
    }

    /**
     * 添加明星工长
     */
    public function addforeman() {
        header('Content-Type:application/json; charset=utf-8');
        $a_name = trim($_POST['a_name']);
        $pwd = trim($_POST['pwd']);
        $cof_pwd = trim($_POST['cof_pwd']);
        $truename = trim($_POST['truename']);
        $sex = trim($_POST['sex']);
        $company = trim($_POST['company']);
        $jibie = trim($_POST['jibie']);
        $telphone = trim($_POST['telphone']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $qq = trim($_POST['qq']);
        $year = trim($_POST['year']);
        $month = trim($_POST['month']);
        $day = trim($_POST['day']);
        $proid = trim($_POST['proid']);
        $cityid = trim($_POST['cityid']);
        $address = trim($_POST['address']);
        $collect = trim($_POST['collect']);
        $jibie = trim($_POST['jibie']);
        $addtime = trim($_POST['addtime']);
        $koubei = trim($_POST['koubei']);
        if (empty($a_name)) {
            $returnarr['status'] = 0;
            $returnarr['info'] = "登录名不能为空";
            echo json_encode($returnarr);
            exit;
        }
        if ($pwd != $cof_pwd) {

            $returnarr['status'] = 0;
            $returnarr['info'] = "两次输入的密码必须一致";
            echo json_encode($returnarr);

            exit;
        }
        //检查登录名是否已经在数据库中存在
        $memmod = new MemberModel();
        if ($memmod->check_name($a_name) > 0) {
            $returnarr['status'] = 0;
            $returnarr['info'] = "登录名已经存在，请重新命名";
            echo json_encode($returnarr);
            exit;
        }

        $maindata = array(
            "a_name" => $a_name,
            "a_pwd" => encrypt($pwd),
            "a_type" => 2,
            "status" => 1
        );
        if (empty($proid)) {
            $returnarr['status'] = 0;
            $returnarr['info'] = "请选择省份";
            echo json_encode($returnarr);
            exit;
        }
        if (empty($cityid)) {
            $returnarr['status'] = 0;
            $returnarr['info'] = "请选择城市";
            echo json_encode($returnarr);
            exit;
        }
        $birthday = $year . "-" . $month . "-" . $day;
        $fjdata = array(
            "f_truename" => $truename,
            "f_company" => $company,
            "f_collect" => $collect,
            "f_koubei" => $koubei,
            "f_jibie" => $jibie,
            "f_telphone" => $telphone,
            "f_phone" => $phone,
            "f_sex" => $sex,
            "f_email" => $email,
            "f_birthday" => $birthday,
            "f_address" => $address,
            "f_qq" => $qq,
            "f_addtime" => $addtime,
            "f_p_id" => $proid,
            "f_c_id" => $cityid
        );

        $res = $memmod->add_member($maindata, $fjdata);

        echo json_encode($res);
    }

    /**
     * 添加工长
     */
    public function addgz() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $citymod = new CityModel();
        $pro_list = $citymod->getprovince(1);
        $this->assign("pro_list", $pro_list);
        if (IS_POST) {
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            #$nickname=trim($_POST['dinfo']['nickname']);
            $picName = trim($_POST['picName']);
            $truename = trim($_POST['dinfo']['truename']);
            $company = trim($_POST['dinfo']['company']);
            $sex = trim($_POST['dinfo']['sex']);
            $email = trim($_POST['dinfo']['email']);
            $qq = trim($_POST['dinfo']['qq']);
            $phone = trim($_POST['dinfo']['phone']);
            $telphone = trim($_POST['dinfo']['telphone']);
            $birthday = trim($_POST['dinfo']['birthday']);
            $p_id = trim($_POST['dinfo']['p_id']);
            $c_id = trim($_POST['dinfo']['c_id']);
            $address = trim($_POST['dinfo']['address']);
            $collect = trim($_POST['dinfo']['collect']);
            $koubei = trim($_POST['dinfo']['koubei']);
            if (empty($a_name)) {
                $this->error("请填写登录名！");
                exit;
            }
            if (empty($pwd)) {
                $this->error("请填写密码！");
                exit;
            }
            if (empty($confirm_pwd)) {
                $this->error("确认密码不能为空！");
                exit;
            }
            if ($confirm_pwd != $pwd) {
                $this->error("两次输入的密码不一致!");
                exit;
            }
            if (empty($truename)) {
                $this->error("真实姓名不能为空！");
                exit;
            }
            if (empty($company)) {
                $this->error("公司名称不能为空！");
                exit;
            }
            if (empty($sex)) {
                $this->error("请选择性别！");
                exit;
            }
            if (empty($phone)) {
                $this->error("手机号不能为空！");
                exit;
            }
            if (empty($telphone)) {
                $this->error("座机不能为空！");
                exit;
            }
            if ($_SESSION['my_info']['role'] != 2) {
                //非地区管理员
                if (empty($p_id)) {
                    $this->error("请选择省份!");
                    exit;
                }
                if (empty($c_id)) {
                    $this->error("请选择城市!");
                    exit;
                }
            } else {
                $c_id = $_SESSION['my_info']['cityid'];
                $m = new CityModel();
                $p_id = $m->getprovinceid($c_id);
            }
            if (empty($collect))
                $collect = 10;
            if (empty($koubei))
                $koubei = 10;

            $maindata = array(
                "a_name" => $a_name,
                "a_pwd" => encrypt($pwd),
                "a_type" => 2,
                "status" => $status
            );
            $fujdata = array(
                "f_truename" => $truename,
                "f_logo" => $picName,
                "f_company" => $company,
                "f_collect" => $collect,
                "f_koubei" => $koubei,
                "f_telphone" => $telphone,
                "f_phone" => $phone,
                "f_sex" => $sex,
                "f_email" => $email,
                "f_birthday" => $birthday,
                "f_address" => $address,
                "f_qq" => $qq,
                "f_p_id" => $p_id,
                "f_c_id" => $c_id
            );
            $memmod = new MemberModel();
            $res = $memmod->add_member($maindata, $fujdata);
            if ($res) {
                $this->success("操作成功！", U("Member/foreman"));
                exit;
            } else {
                $this->error("操作失败！");
                exit;
            }
        }
        $this->display();
    }

    /**
     * ajax
     * 获取 明星工长
     */
    public function getforeman() {
        $aid = $_POST['aid'];
        $is_cl = $_POST['is_cl'];
        header('Content-Type:application/json; charset=utf-8');

        $mod = M("Foremanview");
        $info = $mod->where("a_id=" . $aid)->find();
        if (!$is_cl) {
            $info['sex_1'] = ($info['sex'] == 1) ? "男" : "女";
        }
        echo json_encode($info);
    }

    /**
     * ajax
     * 获取申请详情
     */
    public function getshenqing() {
        $aid = $_POST['aid'];
        $is_cl = $_POST['is_cl'];
        header('Content-Type:application/json; charset=utf-8');

        $mod = M("Shenqingview");
        $info = $mod->where("u_id=" . $aid)->find();
        #echo $mod->getLastSql();
        if (!$is_cl) {
            $info['sex_1'] = ($info['sex'] == 1) ? "男" : "女";
        }
        echo json_encode($info);
    }

    /**
     * 编辑
     * 明星工长
     */
    public function edit_foreman() {
        header('Content-Type:application/json; charset=utf-8');
        $a_name = trim($_POST['a_name']);
        $pwd = trim($_POST['pwd']);
        $cof_pwd = trim($_POST['cof_pwd']);
        $aid = trim($_POST['aid']);
        $truename = trim($_POST['truename']);
        $sex = trim($_POST['sex']);
        $company = trim($_POST['company']);
        $telphone = trim($_POST['telphone']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $qq = trim($_POST['qq']);
        $year = trim($_POST['year']);
        $month = trim($_POST['month']);
        $day = trim($_POST['day']);
        $proid = trim($_POST['proid']);
        $cityid = trim($_POST['cityid']);
        $address = trim($_POST['address']);
        $collect = trim($_POST['collect']);
        $koubei = trim($_POST['koubei']);
        $jibie = trim($_POST['jibie']);
        $addtime = trim($_POST['addtime']);
        $birthday = $year . "-" . $month . "-" . $day;
        if (empty($a_name)) {
            $return = array("status" => 0, "info" => "登录账户不能为空!");
            echo json_encode($return);
            exit;
        }

        $mod = M("Member");
        $minfo = $mod->where("a_id=" . $aid)->field("a_id,a_name,a_pwd")->find();
        $data = array();
        $map = array("a_id" => $aid);
        if (!empty($pwd) && !empty($cof_pwd) && $cof_pwd == $pwd) {
            $pwd = encrypt($pwd);
            if ($pwd != $minfo['a_pwd']) {
                $data['a_pwd'] = $pwd;
            }
        }
        if ($a_name != $minfo['a_name']) {
            $data['a_name'] = $a_name;
        }
        if (!empty($data)) {
            $mod->where($map)->save($data);
        }
        $fjdata = array();
        $map2 = array("f_id" => $aid);
        $mod2 = M("ForemanInfo");
        $fjinfo = $mod2->where($map2)->find();
        if ($fjinfo['f_truename'] != $truename) {
            $fjdata['f_truename'] = $truename;
        }
        if ($fjinfo['f_company'] != $company) {
            $fjdata['f_company'] = $company;
        }
        if ($fjinfo['f_collect'] != $collect) {
            $fjdata['f_collect'] = $collect;
        }
        if ($fjinfo['f_koubei'] != $koubei) {
            $fjdata['f_koubei'] = $koubei;
        }
        if ($fjinfo['f_jibie'] != $jibie) {
            $fjdata['f_jibie'] = $jibie;
        }
        if ($fjinfo['f_telphone'] != $telphone) {
            $fjdata['f_telphone'] = $telphone;
        }
        if ($fjinfo['f_phone'] != $phone) {
            $fjdata['f_phone'] = $phone;
        }
        if ($fjinfo['f_sex'] != $sex) {
            $fjdata['f_sex'] = $sex;
        }
        if ($fjinfo['f_email'] != $email) {
            $fjdata['f_email'] = $email;
        }
        if ($fjinfo['f_birthday'] != $birthday) {
            $fjdata['f_birthday'] = $birthday;
        }
        if ($fjinfo['f_address'] != $address) {
            $fjdata['f_address'] = $address;
        }
        if ($fjinfo['f_qq'] != $qq) {
            $fjdata['f_qq'] = $qq;
        }
        if ($fjinfo['f_addtime'] != $addtime) {
            $fjdata['f_addtime'] = $addtime;
        }
        if ($fjinfo['f_p_id'] != $proid) {
            $fjdata['f_p_id'] = $proid;
        }
        if ($fjinfo['f_c_id'] != $cityid) {
            $fjdata['f_c_id'] = $cityid;
        }


        if (!empty($fjdata)) {
            $mod2->where($map2)->save($fjdata);
        }
        $return = array("status" => 1, "info" => "操作成功!");
        echo json_encode($return);
        exit;
    }

    /**
     * 删除
     * 明星工厂
     */
    public function del_foreman() {
        $aid = $_GET['aid'];
        $mod = M("Member");
        $imod = M("ForemanInfo");
        $res = $mod->where(array("a_id" => $aid))->delete();
        $res2 = $imod->where(array("f_id" => $aid))->delete();
        if ($res && $res2) {
            $this->success("操作成功");
        } else {
            $this->error("操作失败");
        }
    }

    /**
     * 删除
     * 设计师
     */
    public function del_shejishi() {
        $aid = $_GET['aid'];
        $mod = M("Member");
        $imod = M("Sheji");
        $res = $mod->where(array("a_id" => $aid))->delete();
        $res2 = $imod->where(array("f_id" => $aid))->delete();
        if ($res && $res2) {
            $this->success("操作成功");
        } else {
            $this->error("操作失败");
        }
    }

    /**
     * 删除申请
     */
    public function del_shenqing() {
        $aid = $_GET['aid'];
        $m = M("Shenqing");
        $map = "u_id=" . $aid;
        $res = $m->where($map)->delete();
        if ($res) {
            $this->success("操作成功！");
        } else {
            $this->error("操作失败！");
        }
    }

    /**
     * 店铺管理员列表
     */
    public function dianpu() {

        parent::_initalize();
        $systemConfig = $this->systemConfig;
        $this->assign("systemConfig", $systemConfig);

        $m = M("Dianpumember");
        import("ORG.Util.Page");
        $map = "1";
        $keys = trim($_GET['keys']);

        $keys = ($keys == "请输入关键字") ? "" : $keys;

        $start_date = trim($_GET['start_date']);
        $this->assign("start_date", $start_date);
        $end_date = trim($_GET['end_date']);
        $this->assign("end_date", $end_date);

        if (!empty($start_date)) {
            $start_date = strtotime($start_date . "0:0:0");
            $map.=" and UNIX_TIMESTAMP(create_time)>= " . $start_date;
        }
        if (!empty($end_date)) {
            $end_date = strtotime($end_date . "23:59:59");
            $map.=" and UNIX_TIMESTAMP(create_time)<= " . $end_date;
        }
        //城市-------------------------------start
        $citymod = new CityModel();
        #区
        $qu = $_GET['qu'];
        if ($this->getqx($_SESSION['my_info']['role']) == 0) {
            //非地区管理员
            $province = $_GET['province'];

            $city = $_GET['city'];
            if (!empty($province)) {
                $map.=" and p_id=" . $province;
            }
            if (!empty($city)) {
                $map.=" and c_id=" . $city;
            }
            $this->assign("province", $province);
            #市
            $this->assign("city", $city);
            $this->assign("cityname", $citymod->getname($city));

            $this->assign("qu_id", $qu);
            $this->assign("quname", $citymod->getname($qu));
            #省
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
        } else {
            $province = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            if (empty($province) || empty($c_id)) {
                $this->error("地区管理员没有选择省和市!");
                exit;
            }
            if (!empty($province))
                $map.=" and p_id=" . $province;
            if (!empty($c_id))
                $map.=" and c_id=" . $c_id;
            $this->assign("province", $province);
            $this->assign("city", $c_id);
            #区
            $qulist = $citymod->getcity($c_id);
            $this->assign("qulist", $qulist);
            $this->assign("qu_id", $qu);
        }




        $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));

        //城市------------------------end

        if (!empty($keys)) {
            $map.=" and (company like '%" . $keys . "%')";
        }
        if(!empty($qu)){
            $map.=" and q_id=".$qu;
        }

        $cou = $m->where($map)->order("a_id desc")->count();
       # echo $m->getLastSql();
        $page = new Page($cou, 12);
        $showpage = $page->show();
        $list = $m->where($map)->order("a_id desc")->select();

        $this->assign("list", $list);
        $this->assign("page", $showpage);


        $memmod = new MemberModel();
        $year = $memmod->getyear();
        $this->assign("year", $year);
        $month = $memmod->getmonth();
        $this->assign("month", $month);
        $days = $memmod->getday($year[0], $month[0]);
        $this->assign("day", $days);



        $this->assign("keys", $keys);


        $this->display();
    }

    /**
     * 添加普通会员
     */
    public function addpt() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $citymod = new CityModel();
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        if ($is_qx == 0) {
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
        } else {
            #地区管理员
            $cityid = $_SESSION['my_info']['cityid'];
            $proid = $_SESSION['my_info']['proid'];
            #区列表
            $qulist = $citymod->getqu($cityid);
            $this->assign("qulist", $qulist);
            $this->assign("proid", $proid);
            $this->assign("cityid", $cityid);
        }
        $this->assign("is_qx", $is_qx);
        #户型
        $hxmod = M("Hxcategory");
        $hxlist = $hxmod->where("1")->order(" addtime desc")->select();
        $this->assign("hxlist", $hxlist);

        #装修阶段
        $zxjdlist = include_once './Common/config2.php';
        $this->assign("zxjdlist", $zxjdlist['zxjd']);
        #喜欢风格
        $fgMod = M("Fgcategory");
        $fglist = $fgMod->where("1")->order(" addtime desc")->select();
        $this->assign("fglist", $fglist);
        #预算
        $ysMod = M("Yusuan");
        $yslist = $ysMod->where("1")->order(" ysid desc")->select();
        $this->assign("yslist", $yslist);


        if (IS_POST) {
            $a_name = trim($_POST['a_name']);
            $pwd = trim($_POST['pwd']);
            $confpwd = trim($_POST['confirm_pwd']);
            $returnarr = array();
            if (empty($a_name)) {
                $returnarr['status'] = 0;
                $returnarr['info'] = "登录名不能为空";
                $this->error($returnarr['info']);
                exit;
            }
            if ($pwd != $confpwd) {

                $returnarr['status'] = 0;
                $returnarr['info'] = "两次输入的密码必须一致";
                $this->error($returnarr['info']);
                exit;
            }
            //检查登录名是否已经在数据库中存在
            $memmod = new MemberModel();
            if ($memmod->check_name($a_name) > 0) {
                $returnarr['status'] = 0;
                $returnarr['info'] = "登录名已经存在，请重新命名";
                $this->error($returnarr['info']);
                exit;
            }
            $status = $_POST['status'];

            $maindata = array(
                "a_name" => $a_name,
                "a_pwd" => encrypt($pwd),
                "a_type" => 1,
                "status" => $status
            );
            $nickname = trim($_POST['dinfo']['nickname']);
            $truename = trim($_POST['dinfo']['truename']);
            $sex = trim($_POST['dinfo']['sex']);
            $email = trim($_POST['dinfo']['email']);
            $qq = trim($_POST['dinfo']['qq']);
            $movphone = trim($_POST['dinfo']['movphone']);
            $birthday = $_POST['dinfo']['birthday'];
            $citymod = new CityModel();
            if ($this->getqx($_SESSION['my_info']['role']) == 1) {
                //地区管理员
                $p_id = $_SESSION['my_info']['proid'];
                $c_id = $_SESSION['my_info']['cityid'];
                $p_id = $citymod->getprovinceid($cityid);
            } else {
                $c_id = $_POST['dinfo']['city_id'];
                $p_id = $_POST['dinfo']['province_id'];

                if (empty($p_id)) {
                    $returnarr['status'] = 0;
                    $returnarr['info'] = "请选择省份";
                    $this->error($returnarr['info']);
                    exit;
                }
                if (empty($c_id)) {
                    $returnarr['status'] = 0;
                    $returnarr['info'] = "请选择城市";
                    $this->error($returnarr['info']);
                    exit;
                }
            }
            $qu_id = $_POST['dinfo']['qu_id'];
            if (empty($qu_id)) {
                $returnarr['status'] = 0;
                $returnarr['info'] = "请选择城区";
                $this->error($returnarr['info']);
                exit;
            }
            $picName = $_POST['picName'];
            $address = trim($_POST['dinfo']['address']);
            $jifen = trim($_POST['dinfo']['jifen']);

            $telphone = trim($_POST['dinfo']['telphone']);
            $huxing = $_POST['dinfo']['huxing'];
            $mianji = trim($_POST['dinfo']['mianji']);
            $zxjd = $_POST['dinfo']['zxjd'];
            $fengge = $_POST['dinfo']['fengge'];
            $yusuan = $_POST['dinfo']['yusuan'];
            $zxtime = $_POST['dinfo']['zxtime'];

            $fjdata = array(
                "truename" => $truename,
                "logo" => $picName,
                "jifen" => $jifen,
                "sex" => $sex,
                "email" => $email,
                "qq" => $qq,
                "movphone" => $movphone, //移动电话
                "telphone" => $telphone,
                "birthday" => $birthday,
                "c_id" => $c_id,
                "p_id" => $p_id,
                "q_id" => $qu_id,
                "address" => $address,
                "huxing" => $huxing,
                "mianji" => $mianji,
                "zxjd" => $zxjd,
                "fengge" => $fengge,
                "yusuan" => $yusuan,
                "zxtime" => $zxtime
            );


            $res = $memmod->add_member($maindata, $fjdata);
            if ($res) {
                $this->success("操作成功！", U('Member/index'));
            } else {
                $this->error("操作失败！");
            }
            exit;
        }

        $this->display();
    }

    /**
     * 添加店铺管理员
     */
    public function adddianpu() {
        if (IS_POST) {
            $a_name = trim($_POST['a_name']);
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            $status = $_POST['status'];
            if (empty($a_name)) {
                $this->error("登录名称不能为空！");
                exit;
            }
            if (empty($pwd)) {
                $this->error("密码不能为空!");
                exit;
            }
            if (empty($confirm_pwd)) {
                $this->error("确认密码不能为空！");
                exit;
            }
            if ($pwd != $confirm_pwd) {
                $this->error("两次输入的密码必须一致");
                exit;
            }
            //登录名称是否存在
            $memmod = new MemberModel();
            if ($memmod->check_name($a_name) > 0) {
                $this->error("登录名已经存在，请重新命名");
                exit;
            }

            $maindata = array(
                "a_name" => $a_name,
                "a_pwd" => encrypt($pwd),
                "a_type" => 3,
                "status" => $status
            );

            $dinfo = $_POST['dinfo'];
            $dinfo['company'] = trim($dinfo['company']);
            $dinfo['lxrname'] = trim($dinfo['lxrname']);
            $dinfo['telphone'] = trim($dinfo['telphone']);
            $dinfo['movphone'] = trim($dinfo['movphone']);
            $dinfo['jifen'] = trim($dinfo['jifen']);
            $dinfo['email'] = trim($dinfo['email']);
            $dinfo['yingyetime'] = trim($dinfo['yingyetime']);
            $dinfo['address'] = trim($dinfo['address']);
            $dinfo['fwzz'] = trim($dinfo['fwzz']);
            $dinfo['ztmj'] = trim($dinfo['ztmj']);
            $dinfo['fwcn'] = trim($dinfo['fwcn']);
            $dinfo['comjianjie'] = trim($_POST['comjianjie']);
            $dinfo['comcontent'] = trim($_POST['comcontent']);
            $dinfo['fdnum'] = trim($dinfo['fdnum']);
            $dinfo['orders'] = trim($dinfo['orders']);
            $dinfo['click'] = trim($dinfo['click']);
            if (!empty($_FILES['dhimg']['name'])) {
                $dhinfo = $this->upload("./Uploads/shangjia/");
                if (!empty($dhinfo))
                    $dinfo['dhimg'] = $dhinfo[0]['savename'];
            }

            $dinfo['wxts'] = trim($_POST['wxts']);

            if (empty($dinfo['collect']))
                $dinfo['collect'] = 10;
            if (empty($dinfo['koubei']))
                $dinfo['koubei'] = 10;

            $dinfo['jibie'] = trim($dinfo[jibie]);
            $pro_id = trim($_POST['pro_id']);
            $city_id = trim($_POST['city_id']);
            $q_id = trim($_POST['q_id']);
            $dinfo[dizhi] = trim($dinfo[dizhi]);
            $logo = $_POST['picName'];
            if ($_SESSION['my_info']['role'] != 2) {
                if (empty($pro_id)) {
                    $this->error("请选择省份！");
                    exit;
                }
                if (empty($city_id)) {
                    $this->error("请选择市！");
                    exit;
                }
            } else {
                $mod = new CityModel();
                $city_id = $_SESSION['my_info']['cityid'];
                $pro_id = $mod->getprovinceid($city_id);
            }
            $jingdu = $_POST['jingdu'];
            $weidu = $_POST['weidu'];
            $fjdata = array(
                "logo" => $logo,
                "company" => $dinfo['company'],
                "yingyezhizhao" => $dinfo['yingyezhizhao'],
                "lxrname" => $dinfo['lxrname'],
                "telphone" => $dinfo['telphone'],
                "movphone" => $dinfo['movphone'],
                "jifen" => $dinfo['jifen'],
                "email" => $dinfo['email'],
                "yingyetime" => $dinfo['yingyetime'],
                "fwzz" => $dinfo[fwzz],
                "ztmj" => $dinfo['ztmj'],
                "comcontent" => $dinfo['comcontent'],
                "fwcn" => $dinfo['fwcn'],
                "comjianjie" => $dinfo['comjianjie'],
                "fdnum" => $dinfo['fdnum'],
                "orders" => $dinfo['orders'],
                "click" => $dinfo['click'],
                "is_tj" => $dinfo['is_tj'],
                "dhimg" => $dinfo['dhimg'],
                "wxts" => $dinfo['wxts'],
                "p_id" => $pro_id,
                "c_id" => $city_id,
                "q_id" => $q_id,
                "jingdu" => $jingdu,
                "weidu" => $weidu,
                "address" => $dinfo[address],
            );
            $res = $memmod->add_member($maindata, $fjdata);
            if ($res['status']) {
                $this->success($res['info'], U("Member/dianpu"));
            } else {
                $this->error($res['info']);
            }

            exit;
        }

        parent::_initalize();
        $systemConfig = $this->systemConfig;
        $this->assign("systemConfig", $systemConfig);
        //省份
        $citymod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            //地区管理员
            $p_id = $_SESSION['my_info']['proid']; //省
            $c_id = $_SESSION['my_info']['cityid']; //市
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
            #区
            $qulist = $citymod->getcity($c_id);
            $this->assign("qulist", $qulist);
        } else {
            //非地区管理员
            $pro_list = $citymod->getcity(1);
            $this->assign("pro_list", $pro_list);
        }
        $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));


        $this->display();
    }

    /**
     * 获取 店铺管理
     */
    public function getdianpu() {
        $aid = $_POST['aid'];
        $is_cl = $_POST['is_cl'];
        header('Content-Type:application/json; charset=utf-8');

        $mod = M("Dianpumember");
        $info = $mod->where("a_id=" . $aid)->find();
        if (!$is_cl) {
            $info['sex_1'] = ($info['sex'] == 1) ? "男" : "女";
        }
        echo json_encode($info);
    }

    /**
     * 编辑
     * 店铺管理
     */
    public function edit_dianpu() {
        if (IS_POST) {
            $aid = $_POST['aid'];
            $a_name = trim($_POST['a_name']);
            if (empty($a_name)) {
                $this->error("登录名称不能为空！");
                exit;
            }
            $mod = M("Member");
            $minfo = $mod->where("a_id=" . $aid)->find();
            $mdata = array();
            if ($minfo['a_name'] != $a_name) {
                $mdata['a_name'] = $a_name;
            }
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            if (!empty($pwd) && !empty($confirm_pwd) && $pwd == $confirm_pwd) {
                $pwd = encrypt($pwd);
                if ($pwd != $minfo['a_pwd']) {
                    $mdata['a_pwd'] = $pwd;
                }
            }
            if (!empty($mdata)) {
                $mod->where("a_id=" . $aid)->save($mdata);
            }
            $mod1=M("Dianpumember");
            $inf=$mod1->where("a_id=".$aid)->find();
            
            //编辑店铺附加表
            $dinfo = array();
            $dinfo['logo']=$_POST['picName'];
            if(!empty($dinfo['logo'])){
                unlink("./avatar/".$inf['logo']);
                unlink("./avatar/".$inf['logo']."_30.jpg");
                unlink("./avatar/".$inf['logo']."_60.jpg");
                unlink("./avatar/".$inf['logo']."_100.jpg");
            }
            $dinfo['company'] = trim($_POST['dinfo']['company']);//公司名称
            $dinfo['yingyezhizhao'] = trim($_POST['dinfo']['yingyezhizhao']);//营业执照
            if(!empty($dinfo['yingyezhizhao'])){
                unlink("./Uploads/product/".$inf['yingyezhizhao']);
            }
            $dinfo['lxrname'] = trim($_POST['dinfo']['lxrname']);//联系人姓名
            $dinfo['movphone'] = trim($_POST['dinfo']['movphone']);//手机号
            $dinfo['telphone'] = trim($_POST['dinfo']['telphone']);//联系人电话
            $dinfo['jifen'] = trim($_POST['dinfo']['jifen']);//积分
            $dinfo['email'] = trim($_POST['dinfo']['email']);//email
            
            $dinfo['yingyetime'] = trim($_POST['dinfo']['yingyetime']);//营业时间
            $dinfo['fwzz'] = trim($_POST['dinfo']['fwzz']);#服务宗旨
            $dinfo['ztmj'] = trim($_POST['dinfo']['ztmj']);#展厅面积
            $dinfo['comcontent'] = trim($_POST['comcontent']);#公司介绍
            $dinfo['fwcn'] = trim($_POST['dinfo']['fwcn']);#服务承诺
            $dinfo['comjianjie'] = trim($_POST['comjianjie']);#店铺简介
            $dinfo['fdnum'] = trim($_POST['dinfo']['fdnum']);#分店数目
            $dinfo['orders'] = trim($_POST['dinfo']['orders']);#排序
            $dinfo['click'] = trim($_POST['dinfo']['click']);#点击次数
            $dinfo['is_tj'] = trim($_POST['dinfo']['is_tj']);#是否推荐
            if(!empty($_FILES['dhimg']['name'])){
                $dhimginf=$this->upload("./Uploads/shangjia/");
                if(!empty($dhimginf[0]['savename'])){
                    unlink("./Uploads/shangjia/".$inf['dhimg']);
                    $dinfo['dhimg']=$dhimginf[0]['savename'];
                    
                }
            }
            $dinfo['wxts']=  trim($_POST['wxts']);#温馨提示
            
            $dinfo['p_id'] = trim($_POST['pro_id']);
            $dinfo['c_id'] = trim($_POST['city_id']);
            $dinfo['q_id'] = trim($_POST['q_id']);
            
            $dinfo['jingdu'] = trim($_POST['jingdu']);#经度
            $dinfo['weidu'] = trim($_POST['weidu']);#纬度
            $dinfo['address'] = trim($_POST['dinfo']['address']);
            
            $mod2 = M("Dianpu");
            $res2 = $mod2->where("a_id=" . $aid)->save($dinfo);
            $this->success("操作成功！", U("Member/dianpu"));

            exit;
        }


        parent::_initalize();
        $systemConfig = $this->systemConfig;
        $this->assign("systemConfig", $systemConfig);
        $aid = $_GET['aid'];
        $m = M("Dianpumember");
        $info = $m->where("a_id=" . $aid)->find();
        $this->assign("info", $info);
        $citymod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            #地区管理员
            $p_id=$_SESSION['my_info']['proid'];
            $c_id=$_SESSION['my_info']['cityid'];
            #区
            $qulist=$citymod->getcity($c_id);
            $this->assign("qulist",$qulist);
            $this->assign("p_id",$p_id);
            $this->assign("c_id",$c_id);
            
        } else {
            //省份
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
            
        }
        $this->assign("is_qx",$this->getqx($_SESSION['my_info']['role']));

        $this->display("adddianpu");
    }

    /**
     * 删除
     * 店铺管理员
     */
    public function del_dianpu() {
        $aid = $_GET['aid'];
        $mod1=M("Dianpumember");
        $info=$mod1->where("a_id=".$aid)->find();
        if(!empty($info['logo'])){
            unlink("./avatar/".$info['logo']);
            unlink("./avatar/".$info['logo']."_30.jpg");
            unlink("./avatar/".$info['logo']."_60.jpg");
            unlink("./avatar/".$info['logo']."_100.jpg");
        }
        if(!empty($info['dhimg'])){
            $i="./Uploads/shangjia/";
            unlink($i.$info['dhimg']);
        }
        if(!empty($info['yingyezhizhao'])){
            $i="./Uploads/product/";
            unlink($i.$info['yingyezhizhao']);
        }
        $mod = M("Member");
        $imod = M("Dianpu");
        $res = $mod->where(array("a_id" => $aid))->delete();
        $res2 = $imod->where(array("a_id" => $aid))->delete();
        if ($res && $res2) {
            $this->success("操作成功");
        } else {
            $this->error("操作失败");
        }
    }

    /**
     * 上传营业执照
     */
    public function scimg() {
        header('Content-Type:application/json; charset=utf-8');
        $img = $this->upload();
        echo $img[0]['savename'];
    }

    /**
     * 工长 type=2 ,
     * 材料商 type=3
     * 设计师 type=4
     * 
     * 通过申请
     * 1、向工长附加表中加入一条信息
     * 2、修改申请状态为1
     * 3、修改会员类型为普通2
     */
    public function dosh_shenqing() {
        $aid = $_GET['aid'];
        $m = M("Shenqingview");
        $info = $m->where("u_id=" . $aid)->find();
        $type = $_GET['type'];
        $type = empty($type) ? "2" : $type;
        if ($type == 2) {
            $data = array(
                "f_id" => $info['u_id'],
                "f_truename" => $info['truename'],
                "f_company" => $info['companyname'], //需要申请时候添加
                "f_telphone" => $info['companyphone'], //公司座机需要申请时候添加
                "f_phone" => $info['movphone'],
                "f_sex" => $info['sex'],
                "f_email" => $info['email'],
                "f_birthday" => $info['birthday'],
                "f_address" => $info['address'],
                "f_qq" => $info['qq'],
                "f_p_id" => $info['province_id'],
                "f_c_id" => $info['city_id']
            );
            $admod = M("ForemanInfo");
            $res = $admod->add($data);
        } elseif ($type == 3) {
            //材料商
            $data = array(
                "f_id" => $info['u_id'],
                "company" => $info['companyname'],
                "lxrname" => $info['truename'],
                "phone" => $info['movphone'],
                "kefu_phone" => $info['companyphone'],
                "pro_id" => $info['province_id'],
                "city_id" => $info['city_id']
            );
            $admod = M("Dianpu");
            $res = $admod->add($data);
        } elseif ($type == 4) {
            //设计
            $data = array(
                "f_id" => $info['u_id'],
                "comname" => $info['companyname'],
                "comphone" => $info['companyphone'],
                "truename" => $info['truename'],
                "phonenum" => $info['movphone'],
                "p_id" => $info['province_id'],
                "c_id" => $info['city_id']
            );
            $sjmod = M("Sheji");
            $res = $sjmod->add($data);
        }
        $res2 = $m->where("u_id=" . $aid)->save(array("status" => 1));
        $mmod = M("Member");
        $res3 = $mmod->where("a_id=" . $aid)->save(array("a_type" => $type));

        if ($res && $res2 && $res3) {
            $this->success("操作成功！");
        } else {
            $this->error("操作失败！");
        }
    }

    /**
     * 取消审核
     * 工长
     * 1、将工长附加表中的信息删除
     * 2、修改申请状态为0
     * 3、修改会员类型为普通1
     */
    public function qx_shenqing() {
        $aid = $_GET['aid'];
        $type = $_GET['type'];
        $type = empty($type) ? "2" : $type;
        $m2 = M("Shenqingview");
        $m3 = M("Member");
        if ($type == 2) {
            $m = M("ForemanInfo");
            $res1 = $m->where("f_id=" . $aid)->delete();
        } elseif ($type == 3) {
            $m = M("Dianpu");
            $res1 = $m->where("f_id=" . $aid)->delete();
        } elseif ($type == 4) {
            //设计师
            $m = M("Sheji");
            $res1 = $m->where("f_id=" . $aid)->delete();
        }
        $res2 = $m2->where("u_id=" . $aid)->save(array("status" => 0));
        $res3 = $m3->where("a_id=" . $aid)->save(array("a_type" => 1));
        if ($res1 && $res2 && $res3) {
            $this->success("操作成功！");
        } else {

            $this->success("操作失败！");
        }
    }

    /**
     * 设计师
     */
    public function shejishi() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        if ($_GET['is_sq'] == 1) {
            $m = M("Shenqingview");
            import("ORG.Util.Page");
            $status = $_GET['status'];
            $map = "status=" . $status . " and u_type=4";

            $keys = trim($_GET['keys']);

            $keys = ($keys == "请输入关键字") ? "" : $keys;

            $start_date = trim($_GET['start_date']);
            $this->assign("start_date", $start_date);

            $end_date = trim($_GET['end_date']);
            $this->assign("end_date", $end_date);

            if (!empty($start_date)) {
                $start_date = strtotime($start_date . "0:0:0");
                $map.=" and UNIX_TIMESTAMP(addtime)>= " . $start_date;
            }
            if (!empty($end_date)) {
                $end_date = strtotime($end_date . "23:59:59");
                $map.=" and UNIX_TIMESTAMP(addtime)<= " . $end_date;
            }
            if ($_SESSION['my_info']['role'] != 2) {
                //非地区管理员
                $province = $_GET['province'];
                $this->assign("province", $province);
                $city = $_GET['city'];
                if (!empty($province)) {
                    $map.=" and province_id=" . $province;
                }
                if (!empty($city)) {
                    $map.=" and city_id=" . $city;
                }
                $this->assign("city", $city);
            } else {
                if ($_SESSION['my_info']['cityid'])
                    $map.=" and city_id=" . $_SESSION['my_info']['cityid'];
                else {
                    $this->error("该管理员没有分配城市顾没有权限查看此页！", U('Index/index'));
                    exit;
                }
            }



            if (!empty($keys)) {
                $map.=" and (truename like '%" . $keys . "%' or nickname like '%" . $keys . "%')";
            }

            $cou = $m->where($map)->order("u_id desc")->count();
            $page = new Page($cou, 12);
            $showpage = $page->show();
            $list = $m->where($map)->order("u_id desc")->select();
            foreach ($list as $k => $v) {
                $list[$k]['sex_f'] = $this->get_sex($v['sex']);
                $list[$k]['status_f'] = $this->get_status($v['status']);
            }
            $this->assign("list", $list);

            $this->assign("page", $showpage);

            $citymod = new CityModel();
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);

            $this->assign("keys", $keys);

            $cityname = $citymod->getname($city);
            $this->assign("cityname", $cityname);

            $this->display("sjslist" . $status);
            exit;
        } else {
            $m = M("Shejiview");
            import("ORG.Util.Page");
            $map = "1";
            $keys = trim($_GET['keys']);

            $keys = ($keys == "请输入关键字") ? "" : $keys;

            $start_date = trim($_GET['start_date']);
            $this->assign("start_date", $start_date);
            $end_date = trim($_GET['end_date']);
            $this->assign("end_date", $end_date);

            if (!empty($start_date)) {
                $start_date = strtotime($start_date . "0:0:0");
                $map.=" and UNIX_TIMESTAMP(create_time)>= " . $start_date;
            }
            if (!empty($end_date)) {
                $end_date = strtotime($end_date . "23:59:59");
                $map.=" and UNIX_TIMESTAMP(create_time)<= " . $end_date;
            }
            //城市-------------------------------start
            if ($_SESSION['my_info']['role'] != 2) {
                //非地区管理员
                $province = $_GET['province'];
                $this->assign("province", $province);
                $city = $_GET['city'];
                if (!empty($province)) {
                    $map.=" and pro_id=" . $province;
                }
                if (!empty($city)) {
                    $map.=" and city_id=" . $city;
                }
                $this->assign("city", $city);
            } else {
                $map.=" and city_id=" . $_SESSION['my_info']['cityid'];
            }
            //城市------------------------end

            if (!empty($keys)) {
                $map.=" and (company like '%" . $keys . "%')";
            }


            $cou = $m->where($map)->order("a_id desc")->count();

            $page = new Page($cou, 12);
            $showpage = $page->show();
            $list = $m->where($map)->order("a_id desc")->select();

            $this->assign("list", $list);
            $this->assign("page", $showpage);


            $memmod = new MemberModel();
            $year = $memmod->getyear();
            $this->assign("year", $year);
            $month = $memmod->getmonth();
            $this->assign("month", $month);
            $days = $memmod->getday($year[0], $month[0]);
            $this->assign("day", $days);

            $citymod = new CityModel();
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);

            $this->assign("keys", $keys);

            $cityname = $citymod->getname($city);
            $this->assign("cityname", $cityname);

            $this->display();
        }
    }

    /**
     * 上传头像
     */
    public function uploadImg() {

        header('Content-Type:application/json; charset=utf-8');

        import('ORG.Net.UploadFile');
        $upload = new UploadFile();      // 实例化上传类
        $upload->maxSize = 1 * 1024 * 1024;     //设置上传图片的大小
        $upload->allowExts = array('jpg', 'png', 'gif'); //设置上传图片的后缀
        $upload->uploadReplace = true;     //同名则替换
        $upload->saveRule = 'uniqid';     //设置上传头像命名规则(临时图片),修改了UploadFile上传类
        //完整的头像路径
        $path = './avatar/';
        $upload->savePath = $path;
        if (!$upload->upload()) {      // 上传错误提示错误信息
            #$this->ajaxReturn('', $upload->getErrorMsg(), 0, 'json');
            #echo json_encode($upload->getErrorMsg());
        } else {           // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            $temp_size = getimagesize($path . $info['0']['savename']);
            if ($temp_size[0] < 100 || $temp_size[1] < 100) {//判断宽和高是否符合头像要求
                #$this->ajaxReturn(0, '图片宽或高不得小于100px！', 0, 'json');
                # echo json_encode("图片宽或高不得小于100px！");
            }
            $data['picName'] = $info['0']['savename'];
            $data['status'] = 1;
            $data['url'] = __ROOT__ . '/avatar/' . $data['picName'];
            $data['info'] = $info;
            #$this->ajaxReturn($data, 'json');
            echo json_encode($data);
        }
    }

    //裁剪并保存用户头像
    public function cropImg() {

        header('Content-Type:application/json; charset=utf-8');

        //图片裁剪数据
        $params = $this->_post();      //裁剪参数
        if (!isset($params) && empty($params)) {
            return;
        }

        //头像目录地址
        $path = './avatar/';
        //要保存的图片
        $real_path = $path . $params['picName'];
        //临时图片地址
        $pic_path = $path . $params['picName'];
        import('ORG.Util.Image.ThinkImage');
        $Think_img = new ThinkImage(THINKIMAGE_GD);
        //裁剪原图
        $Think_img->open($pic_path)->crop($params['w'], $params['h'], $params['x'], $params['y'])->save($real_path);
        //生成缩略图
        $Think_img->open($real_path)->thumb(100, 100, 1)->save($path . $params['picName'] . '_100.jpg');
        $Think_img->open($real_path)->thumb(60, 60, 1)->save($path . $params['picName'] . '_60.jpg');
        $Think_img->open($real_path)->thumb(30, 30, 1)->save($path . $params['picName'] . '_30.jpg');
        #$this->success('上传头像成功');
        $data['pic1'] = __ROOT__ . '/avatar/' . $params['picName'] . '_100.jpg';
        $data['pic2'] = __ROOT__ . '/avatar/' . $params['picName'] . '_60.jpg';
        $data['pic3'] = __ROOT__ . '/avatar/' . $params['picName'] . '_30.jpg';

        echo json_encode($data);
    }

    /**
     * 编辑普通会员
     */
    public function editpt() {

        $aid = $_GET['aid'];
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $m = M("kehuview");
        $info = $m->where("a_id=" . $aid)->find();

        $this->assign("info", $info);

        $citymod = new CityModel();
        $pro_list = $citymod->getprovince(1);
        $this->assign("pro_list", $pro_list);
        #户型
        $hxmod = M("Hxcategory");
        $hxlist = $hxmod->where("1")->order(" addtime desc")->select();
        $this->assign("hxlist", $hxlist);
        #装修阶段
        $zxjdlist = include_once './Common/config2.php';
        $this->assign("zxjdlist", $zxjdlist['zxjd']);
        #喜欢风格
        $fgMod = M("Fgcategory");
        $fglist = $fgMod->where("1")->order(" addtime desc")->select();
        $this->assign("fglist", $fglist);
        #预算
        $ysMod = M("Yusuan");
        $yslist = $ysMod->where("1")->order(" ysid desc")->select();
        $this->assign("yslist", $yslist);
        $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));
        if ($_POST) {
            $aid = $_POST['aid'];
            $pwd = trim($_POST['pwd']);
            $pwdconf = trim($_POST['confirm_pwd']);
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);

            $truename = trim($_POST['dinfo']['truename']); //姓名
            $picName = trim($_POST['picName']); #头像
            $sex = trim($_POST['dinfo']['sex']);
            $email = trim($_POST['dinfo']['email']);
            $qq = trim($_POST['dinfo']['qq']);
            $movphone = trim($_POST['dinfo']['movphone']); #手机号
            $birthday = trim($_POST['dinfo']['birthday']); #生日
            $province_id = trim($_POST['dinfo']['province_id']); //省
            $city_id = trim($_POST['dinfo']['city_id']); //市
            $qu_id = $_POST['dinfo']['qu_id']; //区
            $address = trim($_POST['dinfo']['address']); //地址
            $jifen = trim($_POST['dinfo']['jifen']); //积分
            $telphone = trim($_POST['dinfo']['telphone']); //座机
            $huxing = $_POST['dinfo']['huxing']; //户型
            $mianji = trim($_POST['dinfo']['mianji']); //面积
            $zxjd = $_POST['dinfo']['zxjd']; //装修阶段
            $fengge = $_POST['dinfo']['fengge']; //风格
            $yusuan = $_POST['dinfo']['yusuan']; //预算
            $zxtime = trim($_POST["dinfo"]["zxtime"]); //装修时间


            $data = array();
            if (!empty($pwd) && !empty($pwdconf)) {
                //修改密码
                if ($pwd != $pwdconf) {
                    $this->error("两次输入的密码不一致！");
                    exit;
                }
                $data['a_pwd'] = encrypt($pwd);
            }
            if ($info['a_name'] != $a_name)
                $data['a_name'] = $a_name;

            if ($info['status'] != $status)
                $data['status'] = $status;

            $m1 = M("Member");
            if (!empty($data))
                $res = $m1->where("a_id=" . $aid)->save($data);

            $datai = array();
            if ($truename != $info['truename'])
                $datai['truename'] = $truename;
            if ($picName != $info['logo']){
                $datai['logo'] = $picName;
                unlink("./avatar/".$info['logo']);
                unlink("./avatar/".$info['logo']."_30.jpg");
                unlink("./avatar/".$info['logo']."_60.jpg");
                unlink("./avatar/".$info['logo']."_100.jpg");
            }
            if ($sex != $info['sex'])
                $datai['sex'] = $sex;
            if ($email != $info['email'])
                $datai['email'] = $email;
            if ($qq != $info['qq'])
                $datai['qq'] = $qq;
            if ($movphone != $info['movphone'])
                $datai['movphone'] = $movphone;
            if ($birthday != $info['birthday'])
                $datai['birthday'] = $birthday;
            if ($province_id != $info['p_id'])
                $datai['p_id'] = $province_id;
            if ($city_id != $info['c_id'])
                $datai['c_id'] = $city_id;
            if ($qu_id != $info['q_id'])
                $datai['q_id'] = $qu_id;
            if ($address != $info['address'])
                $datai['address'] = $address;
            if ($jifen != $info['jifen'])
                $datai['jifen'] = $jifen;
            if ($telphone != $info['telphone'])
                $datai['telphone'] = $telphone;
            if ($huxing != $info['huxing'])
                $datai['huxing'] = $huxing;
            if ($mianji != $info['mianji'])
                $datai['mianji'] = $mianji;
            if ($zxjd != $info['zxjd'])
                $datai['zxjd'] = $zxjd;
            if ($fengge != $info['fengge'])
                $datai['fengge'] = $fengge;
            if ($yusuan != $info['yusuan'])
                $datai['yusuan'] = $yusuan;
            if ($zxtime != $info['zxtime'])
                $datai['zxtime'] = $zxtime;

            $m2 = M("Webmember");
            if (!empty($datai))
                $res2 = $m2->where("a_id=" . $aid)->save($datai);
            if (!$res && !$res2)
                $this->error("操作失败！");
            else
                $this->success("操作成功！", U("Member/index"));
            exit;
        }
        $this->display("addpt");
    }

    /**
     * 编辑明星工长
     */
    public function editgz() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $citymod = new CityModel();
        $pro_list = $citymod->getprovince(1);
        $this->assign("pro_list", $pro_list);
        $aid = $_GET['aid'];
        $m = M("Foremanview");
        $info = $m->where("a_id=" . $aid)->find();

        $this->assign("info", $info);
        if (IS_POST) {
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);
            $picName = trim($_POST['picName']);
            $truename = trim($_POST['dinfo']['truename']);
            $company = trim($_POST['dinfo']['company']);
            $sex = trim($_POST['dinfo']['sex']);
            $email = trim($_POST['dinfo']['email']);
            $qq = trim($_POST['dinfo']['qq']);
            $phone = trim($_POST['dinfo']['phone']);
            $telphone = trim($_POST['dinfo']['telphone']);
            $birthday = trim($_POST['dinfo']['birthday']);
            $p_id = trim($_POST['dinfo']['p_id']);
            $c_id = trim($_POST['dinfo']['c_id']);
            $address = trim($_POST['dinfo']['address']);
            $collect = trim($_POST['dinfo']['collect']);
            $koubei = trim($_POST['dinfo']['koubei']);

            $data = array();
            $datafj = array();
            $m1 = M("Member");
            $m2 = M("ForemanInfo");
            if (!empty($pwd) && !empty($confirm_pwd)) {
                //修改密码
                if ($pwd != $confirm_pwd) {
                    $this->error("两次输入的密码必须一致!");
                    exit;
                }
                $data['a_pwd'] = encrypt($pwd);
            }
            if (empty($a_name)) {
                $this->error("登录名称不能为空！");
                exit;
            }
            if ($a_name != $info['a_name']) {
                $data['a_name'] = $a_name;
            }
            if ($status != $info['status']) {
                $data['status'] = $status;
            }
            if ($picName != $info['f_logo']) {
                $datafj['f_logo'] = $picName;
            }
            if ($truename != $info['f_truename']) {
                $datafj['f_truename'] = $truename;
            }
            if ($company != $info['f_company']) {
                $datafj['f_company'] = $company;
            }
            if ($sex != $info['f_sex']) {
                $datafj['f_sex'] = $sex;
            }
            if ($email != $info['f_email']) {
                $datafj['f_email'] = $email;
            }
            if ($qq != $info['f_qq']) {
                $datafj['f_qq'] = $qq;
            }
            if ($phone != $info['f_phone']) {
                $datafj['f_phone'] = $phone;
            }
            if ($telphone != $info['f_telphone']) {
                $datafj['f_telphone'] = $telphone;
            }

            if ($birthday != $info['f_birthday']) {
                $datafj['f_birthday'] = $birthday;
            }
            if ($_SESSION['my_info']['role'] != 2) {
                //非地区管理员
                if ($p_id != $info['f_p_id']) {
                    $datafj['f_p_id'] = $p_id;
                }
                if ($c_id != $info['f_c_id']) {
                    $datafj['f_c_id'] = $c_id;
                }
            }
            if ($address != $info['f_address']) {
                $datafj['f_address'] = $address;
            }
            if ($collect != $info['f_collect']) {
                $datafj['f_collect'] = $collect;
            }
            if ($koubei != $info['f_koubei']) {
                $datafj['f_koubei'] = $koubei;
            }
            if (!empty($data))
                $res1 = $m1->where("a_id=" . $aid)->save($data);
            if (!empty($datafj))
                $res2 = $m2->where("f_id=" . $aid)->save($datafj);
            if (!$res1 && !$res2) {
                $this->error("操作失败！");
            } else {
                $this->success("操作成功！", U("Member/foreman"));
            }
            exit;
        }



        $this->display("addgz");
    }

    /**
     * 添加设计师
     */
    public function addsjs() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $citymod = new CityModel();
        $pro_list = $citymod->getprovince(1);
        $this->assign("pro_list", $pro_list);
        if (IS_POST) {
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            $picName = trim($_POST['picName']);
            $truename = $_POST['dinfo']['truename'];
            $nickname = $_POST['dinfo']['nickname'];
            $sex = $_POST['dinfo']['sex'];
            $comname = $_POST['dinfo']['comname'];
            $comphone = $_POST['dinfo']['comphone'];
            $phonenum = $_POST['dinfo']['phonenum'];
            $email = $_POST['dinfo']['email'];
            $qq = $_POST['dinfo']['qq'];
            $birthday = $_POST['dinfo']['birthday'];
            $p_id = $_POST['dinfo']['p_id'];
            $c_id = $_POST['dinfo']['c_id'];
            $address = $_POST['dinfo']['address'];
            $collect = $_POST['dinfo']['collect'];
            $koubei = $_POST['dinfo']['koubei'];
            if (empty($a_name)) {
                $this->error("登录名不能为空！");
                exit;
            }
            if (empty($pwd)) {
                $this->error("密码不能为空！");
                exit;
            }

            if (empty($confirm_pwd)) {
                $this->error("确认密码不能为空！");
                exit;
            }
            if (empty($confirm_pwd)) {
                $this->error("确认密码不能为空！");
                exit;
            }
            if (empty($truename)) {
                $this->error("真实姓名不能为空！");
                exit;
            }
            if ($pwd != $confirm_pwd) {
                $this->error("两次输入的密码不能为空！");
                exit;
            }
            //登录名称是否存在
            $memmod = new MemberModel();
            if ($memmod->check_name($a_name) > 0) {
                $this->error("登录名已经存在，请重新命名");
                exit;
            }
            if ($_SESSION['my_info']['role'] != 2) {
                if (empty($p_id)) {
                    $this->error("请选择省份！");
                    exit;
                }
                if (empty($c_id)) {
                    $this->error("请选择市！");
                    exit;
                }
            } else {
                $mod = new CityModel();
                $c_id = $_SESSION['my_info']['cityid'];
                $p_id = $mod->getprovinceid($c_id);
            }

            $maindata = array(
                "a_name" => $a_name,
                "a_pwd" => encrypt($pwd),
                "a_type" => 4,
                "status" => $status
            );
            $fjdata = array();
            $fjdata['nickname'] = $nickname;
            $fjdata['logo'] = $picName;
            $fjdata['truename'] = $truename;
            $fjdata['sex'] = $sex;
            $fjdata['comname'] = $comname;
            $fjdata['comphone'] = $comphone;
            $fjdata['phonenum'] = $phonenum;
            $fjdata['collect'] = $collect;
            $fjdata['koubei'] = $koubei;
            $fjdata['email'] = $email;
            $fjdata['birthday'] = $birthday;
            $fjdata['address'] = $address;
            $fjdata['qq'] = $qq;
            $fjdata['p_id'] = $p_id;
            $fjdata['c_id'] = $c_id;

            $res = $memmod->add_member($maindata, $fjdata);
            if ($res['status']) {
                $this->success($res['info']);
            } else {
                $this->error($res['info']);
            }
            exit;
        }
        $this->display();
    }

    /**
     * ajax
     * 获取设计师
     */
    public function getsjs() {
        $aid = $_POST['aid'];
        $is_cl = $_POST['is_cl'];
        header('Content-Type:application/json; charset=utf-8');

        $mod = M("Shejiview");
        $info = $mod->where("a_id=" . $aid)->find();
        if (!$is_cl) {
            $info['sex_1'] = ($info['sex'] == 1) ? "男" : "女";
        }
        echo json_encode($info);
    }

    /**
     * 编辑设计师
     */
    public function editsjs() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $aid = $_GET['aid'];
        $m = M("Shejiview");
        $info = $m->where("a_id=" . $aid)->find();
        $this->assign("info", $info);
        $citymod = new CityModel();
        $pro_list = $citymod->getprovince(1);
        $this->assign("pro_list", $pro_list);
        if (IS_POST) {
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            $picName = trim($_POST['picName']);
            $truename = trim($_POST['truename']);
            $nickname = trim($_POST['nickname']);
            $sex = trim($_POST['sex']);
            $comname = trim($_POST['comname']);
            $comphone = trim($_POST['comphone']);
            $phonenum = trim($_POST['phonenum']);
            $email = trim($_POST['email']);
            $qq = trim($_POST['qq']);
            $birthday = trim($_POST['birthday']);
            $address = trim($_POST['address']);
            $collect = trim($_POST['collect']);
            $koubei = trim($_POST['koubei']);
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $aid = $_POST['aid'];
            if (empty($a_name)) {
                $this->error("登录名称不能为空！");
                exit;
            }
            $data = array();
            $m = M("Member");
            if (!empty($pwd) && !empty($confirm_pwd)) {
                if ($pwd != $confirm_pwd) {
                    $this->error("两次输入的密码必须一致！");
                    exit;
                }
                $data['a_pwd'] = encrypt($pwd);
            }
            if ($a_name != $info['a_name']) {
                $data['a_name'] = $a_name;
            }
            if ($status != $info['status'])
                $data['status'] = $status;
            if (!empty($data))
                $res1 = $m->where("a_id=" . $aid)->save($data);
            $datafj = array();
            if ($nickname != $info['nickname'])
                $datafj['nickname'] = $nickname;
            if ($picName != $info['logo'])
                $datafj['logo'] = $picName;
            if ($truename != $info['truename'])
                $datafj['truename'] = $truename;
            if ($sex != $info['sex'])
                $datafj['sex'] = $sex;
            if ($comname != $info['comname'])
                $datafj['comname'] = $comname;

            if ($comphone != $info['comphone'])
                $datafj['comphone'] = $comphone;
            if ($phonenum != $info['phonenum'])
                $datafj['phonenum'] = $phonenum;
            if ($collect != $info['collect'])
                $datafj['collect'] = $collect;

            if ($koubei != $info['koubei'])
                $datafj['koubei'] = $koubei;
            if ($email != $info['email'])
                $datafj['email'] = $email;

            if ($birthday != $info['birthday'])
                $datafj['birthday'] = $birthday;

            if ($address != $info['address'])
                $datafj['address'] = $address;

            if ($qq != $info['qq'])
                $datafj['qq'] = $qq;
            if ($_SESSION['my_info']['role'] != 2) {
                if ($p_id != $info['p_id'])
                    $datafj['p_id'] = $p_id;
                if ($c_id != $info['c_id'])
                    $datafj['c_id'] = $c_id;
            }else {
                $datafj['c_id'] = $_SESSION['my_info']['cityid'];
                $citymod = new CityModel();
                $datafj['p_id'] = $citymod->getprovinceid($datafj['c_id']);
            }

            $fjMod = M("Sheji");
            if (!empty($datafj))
                $res2 = $fjMod->where("f_id=" . $aid)->save($datafj);
            if (!$res1 && !$res2) {
                $this->error("修改失败！");
            } else {
                $this->success("操作成功!", U("Member/shejishi"));
            }
            exit;
        }
        $this->display("addsjs");
    }

}

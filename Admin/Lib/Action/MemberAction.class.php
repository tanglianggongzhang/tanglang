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
        $this->assign("quname", $citymod->getname($qu));

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
     * 删除
     */
    public function del() {
        $aid = $_GET['aid'];
        $mod1 = M("Kehuview");
        $info = $mod1->where("a_id=" . $aid)->find();
        if (!empty($info['logo'])) {
            unlink("./avatar/" . $info['logo']);
            unlink("./avatar/" . $info['logo'] . "_30.jpg");
            unlink("./avatar/" . $info['logo'] . "_60.jpg");
            unlink("./avatar/" . $info['logo'] . "_100.jpg");
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
            $map.=" and UNIX_TIMESTAMP(ruzhitime)>= " . $start_date;
        }
        if (!empty($end_date)) {
            $end_date = strtotime($end_date . "23:59:59");
            $map.=" and UNIX_TIMESTAMP(ruzhitime)<= " . $end_date;
        }
        $is_qx = $this->getqx($_SESSION['my_info']['role']);

        $citymod = new CityModel();
        $qu = $_GET['qu'];
        if ($is_qx == 0) {
            //非地区管理员
            $province = $_GET['province'];
            $city = $_GET['city'];

            $this->assign("province", $province);
            $this->assign("city", $city);


            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
            $cityname = $citymod->getname($city);
            $this->assign("cityname", $cityname);
            $qname = $citymod->getname($qu);
            $this->assign("qname", $qname);
        } else {
            $province = $_SESSION['my_info']['proid'];
            $city = $_SESSION['my_info']['cityid'];
            $qulist = $citymod->getcity($city);
            $this->assign("qulist", $qulist);

            if (!empty($city) && !empty($province)) {
                $this->assign("province", $province);
                $this->assign("city", $city);
            } else {
                $this->error("该管理员没有分配城市顾没有权限查看此页！", U('Index/index'));
                exit;
            }
        }
        $this->assign("is_qx", $is_qx);
        if (!empty($province)) {
            $map.=" and p_id=" . $province;
        }
        if (!empty($city)) {
            $map.=" and c_id=" . $city;
        }

        if (!empty($keys)) {
            $map.=" and (company like '%" . $keys . "%' or truename like '%" . $keys . "%')";
        }

        if (!empty($qu))
            $map.=" and q_id=" . $qu;
        $this->assign("qu", $qu);

        $cou = $m->where($map)->order("a_id desc")->count();


        $page = new Page($cou, 12);
        $showpage = $page->show();
        $list = $m->where($map)->order("a_id desc")->select();
        foreach ($list as $k => $v) {
            $list[$k]['sexf'] = $v['sex'] == 1 ? "男" : "女";
        }
        $this->assign("list", $list);
        $this->assign("page", $showpage);



        $this->assign("keys", $keys);



        $this->display();
    }

    /**
     * 添加工长
     */
    public function addgz() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $citymod = new CityModel();
        if ($this->getqx($_SESSION['my_info']['role']) == 1) {
            #地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qulist = $citymod->getcity($c_id);
            $this->assign("qulist", $qulist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
        } else {
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
        }
        $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));

        if (IS_POST) {
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
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
            #检测用户名是否存在
            $memMod = M("Member");
            $res = $memMod->where("a_type=2 and a_name='" . $a_name . "'")->count();
            if ($res > 0) {
                $this->error("登录名已经存在！");
                exit;
            }
            #----------------------附加表-----------------------start
            $fjdata1 = array();
            $fjdata1['logo'] = $_POST['picName']; #logo
            $fjdata1['truename'] = trim($_POST['truename']);
            $fjdata1['ruzhitime'] = trim($_POST['ruzhitime']);
            $fjdata1['paiming'] = trim($_POST['paiming']);
            $fjdata1['company'] = trim($_POST['company']);
            $fjdata1['koubei'] = trim($_POST['koubei']);
            $fjdata1['comments'] = trim($_POST['comments']);
            $fjdata1['peoples'] = trim($_POST['peoples']);
            $fjdata1['cases'] = trim($_POST['cases']);

            $fjdata1['yuyues'] = trim($_POST['yuyues']);
            $fjdata1['fwyzrs'] = trim($_POST['fwyzrs']);
            $fuwu = trim($_POST['fuwu']);
            $fuwu = str_replace("\n", ",", $fuwu);
            $fjdata1['fuwu'] = $fuwu;

            /* $zuopin = $_POST['zuopin'];
              $zuopin_str = implode(",", $zuopin); */

            $fjdata1['zuopin'] = $zuopin_str;
            $fjdata1['is_sfz'] = $_POST['is_sfz'];
            $fjdata1['is_qy'] = $_POST['is_qy'];
            $fjdata1['qytime'] = $_POST['qytime'];
            $fjdata1['is_bzj'] = $_POST['is_bzj'];

            $sfz_img = $_FILES['sfz_img']['name']; #身份证
            $dhimg = $_FILES['dhimg']['name']; #导航
            $path = "/Uploads/shangjia/";
            if (!empty($sfz_img) && !empty($dhimg)) {
                $sfzimg = $this->upload("." . $path);
                if (!empty($sfzimg[0]['savename']))
                    $sfz = $path . $sfzimg[0]['savename'];
                if (!empty($sfzimg[1]['savename']))
                    $dhimg2 = $path . $sfzimg[1]['savename'];
            }elseif (!empty($sfz_img) && empty($dhimg)) {
                $sfzimg = $this->upload("." . $path);
                if (!empty($sfzimg[0]['savename']))
                    $sfzimg = $this->upload("." . $path);
                $sfz = $path . $sfzimg[0]['savename'];
            }elseif (empty($sfz_img) && !empty($dhimg)) {
                $sfzimg = $this->upload("." . $path);
                if (!empty($sfzimg[0]['savename']))
                    $sfzimg = $this->upload("." . $path);
                $dhimg2 = $path . $sfzimg[0]['savename'];
            }

            $fjdata1['sfz_img'] = $sfz;
            $fjdata1['content'] = trim($_POST['content']);
            $fjdata1['telphone'] = trim($_POST['telphone']);
            $fjdata1['movphone'] = trim($_POST['movphone']);
            $fjdata1['sex'] = trim($_POST['sex']);
            $fjdata1['email'] = trim($_POST['email']);
            $fjdata1['gongling'] = trim($_POST['gongling']);
            $fjdata1['is_tj'] = trim($_POST['is_tj']);

            $fjdata1['dhimg'] = $dhimg2;
            $fjdata1['qq'] = trim($_POST['qq']);
            $fjdata1['p_id'] = $_POST['p_id'];
            $fjdata1['c_id'] = $_POST['c_id'];
            $fjdata1['q_id'] = $_POST['q_id'];
            $fjdata1['address'] = trim($_POST['address']);
            $fjdata1['jingdu'] = $_POST['jingdu'];
            $fjdata1['weidu'] = $_POST['weidu'];

            #-------------------附加表-----------------------end

            if (empty($fjdata1['truename'])) {
                $this->error("真实姓名不能为空！");
                exit;
            }
            if (empty($fjdata1['company'])) {
                $this->error("公司名称不能为空！");
                exit;
            }

            if (empty($fjdata1['p_id'])) {
                $this->error("请选择省份!");
                exit;
            }
            if (empty($fjdata1['c_id'])) {
                $this->error("请选择城市!");
                exit;
            }

            $maindata = array(
                "a_name" => $a_name,
                "a_pwd" => encrypt($pwd),
                "a_type" => 2,
                "status" => $status
            );

            $memmod = new MemberModel();
            $res1 = $memmod->add_member($maindata, $fjdata1);
            if ($res1) {
                $this->success("操作成功！", U("Member/foreman"));
                exit;
            } else {
                $this->error("操作失败！");
                exit;
            }
        }
        #代表作品 案例
        $this->assign("addgz", 1);

        $this->display();
    }

    /**
     * 删除
     * 明星工厂
     */
    public function del_foreman() {
        $aid = $_GET['aid'];
        $mod = M("Member");
        $imod = M("ForemanInfo");
        $info = $imod->where("a_id=" . $aid)->field("a_id")->find();
        if (!empty($info['logo'])) {
            unlink("./avatar/" . $info['logo']);
            unlink("./avatar/" . $info['logo'] . "_30.jpg");
            unlink("./avatar/" . $info['logo'] . "_60.jpg");
            unlink("./avatar/" . $info['logo'] . "_100.jpg");
        }
        if (!empty($info['sfz_img'])) {
            unlink("." . $info['sfz_img']);
        }
        if (!empty($info['dhimg'])) {
            unlink("." . $info['dhimg']);
        }

        $res = $mod->where(array("a_id" => $aid))->delete();
        $res2 = $imod->where(array("a_id" => $aid))->delete();
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
        $info = $imod->where("a_id=" . $aid)->find();
        if (!empty($info['dhimg'])) {
            unlink("./" . $info['dhimg']);
        }
        if (!empty($info['logo'])) {
            $path = "./avatar/";
            unlink($path . $info['logo']);
        }

        $res = $mod->where(array("a_id" => $aid))->delete();
        $res2 = $imod->where(array("a_id" => $aid))->delete();
        if ($res && $res2) {
            $this->success("操作成功");
        } else {
            $this->error("操作失败");
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
        if (!empty($qu)) {
            $map.=" and q_id=" . $qu;
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
            $mod1 = M("Dianpumember");
            $inf = $mod1->where("a_id=" . $aid)->find();

            //编辑店铺附加表
            $dinfo = array();
            $dinfo['logo'] = $_POST['picName'];
            if (!empty($dinfo['logo'])) {
                unlink("./avatar/" . $inf['logo']);
                unlink("./avatar/" . $inf['logo'] . "_30.jpg");
                unlink("./avatar/" . $inf['logo'] . "_60.jpg");
                unlink("./avatar/" . $inf['logo'] . "_100.jpg");
            }
            $dinfo['company'] = trim($_POST['dinfo']['company']); //公司名称
            $dinfo['yingyezhizhao'] = trim($_POST['dinfo']['yingyezhizhao']); //营业执照
            if (!empty($dinfo['yingyezhizhao'])) {
                unlink("./Uploads/product/" . $inf['yingyezhizhao']);
            }
            $dinfo['lxrname'] = trim($_POST['dinfo']['lxrname']); //联系人姓名
            $dinfo['movphone'] = trim($_POST['dinfo']['movphone']); //手机号
            $dinfo['telphone'] = trim($_POST['dinfo']['telphone']); //联系人电话
            $dinfo['jifen'] = trim($_POST['dinfo']['jifen']); //积分
            $dinfo['email'] = trim($_POST['dinfo']['email']); //email

            $dinfo['yingyetime'] = trim($_POST['dinfo']['yingyetime']); //营业时间
            $dinfo['fwzz'] = trim($_POST['dinfo']['fwzz']); #服务宗旨
            $dinfo['ztmj'] = trim($_POST['dinfo']['ztmj']); #展厅面积
            $dinfo['comcontent'] = trim($_POST['comcontent']); #公司介绍
            $dinfo['fwcn'] = trim($_POST['dinfo']['fwcn']); #服务承诺
            $dinfo['comjianjie'] = trim($_POST['comjianjie']); #店铺简介
            $dinfo['fdnum'] = trim($_POST['dinfo']['fdnum']); #分店数目
            $dinfo['orders'] = trim($_POST['dinfo']['orders']); #排序
            $dinfo['click'] = trim($_POST['dinfo']['click']); #点击次数
            $dinfo['is_tj'] = trim($_POST['dinfo']['is_tj']); #是否推荐
            if (!empty($_FILES['dhimg']['name'])) {
                $dhimginf = $this->upload("./Uploads/shangjia/");
                if (!empty($dhimginf[0]['savename'])) {
                    unlink("./Uploads/shangjia/" . $inf['dhimg']);
                    $dinfo['dhimg'] = $dhimginf[0]['savename'];
                }
            }
            $dinfo['wxts'] = trim($_POST['wxts']); #温馨提示

            $dinfo['p_id'] = trim($_POST['pro_id']);
            $dinfo['c_id'] = trim($_POST['city_id']);
            $dinfo['q_id'] = trim($_POST['q_id']);

            $dinfo['jingdu'] = trim($_POST['jingdu']); #经度
            $dinfo['weidu'] = trim($_POST['weidu']); #纬度
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
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            #区
            $qulist = $citymod->getcity($c_id);
            $this->assign("qulist", $qulist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
        } else {
            //省份
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
        }
        $this->assign("is_qx", $this->getqx($_SESSION['my_info']['role']));

        $this->display("adddianpu");
    }

    /**
     * 删除
     * 店铺管理员
     */
    public function del_dianpu() {
        $aid = $_GET['aid'];
        $mod1 = M("Dianpumember");
        $info = $mod1->where("a_id=" . $aid)->find();
        if (!empty($info['logo'])) {
            unlink("./avatar/" . $info['logo']);
            unlink("./avatar/" . $info['logo'] . "_30.jpg");
            unlink("./avatar/" . $info['logo'] . "_60.jpg");
            unlink("./avatar/" . $info['logo'] . "_100.jpg");
        }
        if (!empty($info['dhimg'])) {
            $i = "./Uploads/shangjia/";
            unlink($i . $info['dhimg']);
        }
        if (!empty($info['yingyezhizhao'])) {
            $i = "./Uploads/product/";
            unlink($i . $info['yingyezhizhao']);
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
     * 设计师
     */
    public function shejishi() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);

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
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $citymod = new CityModel();

        if ($is_qx == 0) {
            //非地区管理员
            $p_id = $_GET['province'];
            $c_id = $_GET['city'];
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            $qulist = $citymod->getcity($c_id);
            $this->assign("qulist", $qulist);
        }
        if (!empty($p_id)) {
            $map.=" and p_id=" . $p_id;
        }
        if (!empty($c_id)) {
            $map.=" and c_id=" . $c_id;
        }
        $this->assign("province", $p_id);
        $this->assign("city", $c_id);
        $this->assign("is_qx", $is_qx);
        $qu = $_GET['qu'];
        $this->assign("qu", $qu);
        $cityname = $citymod->getname($c_id);
        $this->assign("cityname", $cityname);
        $quname = $citymod->getname($qu);
        $this->assign("quname", $quname);

        if (!empty($qu))
            $map.=" and q_id=" . $qu;

        //城市------------------------end

        if (!empty($keys)) {
            $map.=" and (truename like '%" . $keys . "%')";
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



        $this->assign("keys", $keys);



        $this->display();
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
            if ($picName != $info['logo']) {
                $datai['logo'] = $picName;
                unlink("./avatar/" . $info['logo']);
                unlink("./avatar/" . $info['logo'] . "_30.jpg");
                unlink("./avatar/" . $info['logo'] . "_60.jpg");
                unlink("./avatar/" . $info['logo'] . "_100.jpg");
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
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        if ($is_qx == 0) {
            //非地区管理员
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
        } else {
            //地区管理员
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            #区列表
            $qulist = $citymod->getcity($c_id);
            $this->assign("qulist", $qulist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
        }
        $this->assign("is_qx", $is_qx);

        $aid = $_GET['aid'];
        $m = M("Foremanview");
        $info = $m->where("a_id=" . $aid)->find();
        $this->assign("info", $info);
        #代表作品-案例
        $casemod = new CaseModel();
        $caselist = $casemod->getcaselist(1, $aid);
        $this->assign("caselist", $caselist);

        if (IS_POST) {
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);

            //附加----------------------------start
            $picName = trim($_POST['picName']);
            $truename = trim($_POST['truename']);
            $ruzhitime = trim($_POST['ruzhitime']);
            $paiming = trim($_POST['paiming']);
            $company = trim($_POST['company']);
            $koubei = trim($_POST['koubei']);
            $comments = trim($_POST['comments']);
            $peoples = trim($_POST['peoples']);
            $cases = trim($_POST['cases']);
            $yuyues = trim($_POST['yuyues']);
            $fwyzrs = trim($_POST['fwyzrs']);
            $fuwu = trim($_POST['fuwu']); //服务范围
            $fuwu = str_replace("\n", ",", $fuwu);
            $zuopin = $_POST['zuopin'];
            $zuopin = implode(",", $zuopin);
            $is_sfz = $_POST['is_sfz'];
            $is_qy = $_POST['is_qy'];
            $qytime = $_POST['qytime'];
            $is_bzj = $_POST['is_bzj'];
            $content = $_POST['content'];
            $telphone = trim($_POST['telphone']);
            $movphone = trim($_POST['movphone']);
            $sex = $_POST['sex'];
            $email = trim($_POST['email']);
            $gongling = trim($_POST['gongling']);
            $is_tj = $_POST['is_tj'];
            $qq = trim($_POST['qq']);
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $address = $_POST['address'];
            $jingdu = $_POST['jingdu'];
            $weidu = $_POST['weidu'];
            $aid = $_POST['aid'];
            //附加-------------------------------end
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

            if ($truename != $info['truename']) {
                $datafj['truename'] = $truename;
            }
            if ($ruzhitime != $info['ruzhitime']) {
                $datafj['ruzhitime'] = $ruzhitime;
            }
            if ($picName != $info['logo'] && $picName != '') {
                unlink("./avatar/" . $info['logo']);
                unlink("./avatar/" . $info['logo'] . "_30.jpg");
                unlink("./avatar/" . $info['logo'] . "_60.jpg");
                unlink("./avatar/" . $info['logo'] . "_100.jpg");

                $datafj['logo'] = $picName;
            }
            if ($paiming != $info['paiming']) {
                $datafj['paiming'] = $paiming;
            }
            if ($company != $info['company']) {
                $datafj['company'] = $company;
            }
            if ($koubei != $info['koubei']) {
                $datafj['koubei'] = $koubei;
            }
            if ($comments != $info['comments']) {
                $datafj['comments'] = $comments;
            }
            if ($peoples != $info['peoples'])
                $datafj['peoples'] = $peoples;
            if ($cases != $info['cases'])
                $datafj['cases'] = $cases;
            if ($yuyues != $info['yuyues'])
                $datafj['yuyues'] = $yuyues;
            if ($fwyzrs != $info['fwyzrs'])
                $datafj['fwyzrs'] = $fwyzrs;

            if ($fuwu != $info['fuwu'])
                $datafj['fuwu'] = $fuwu;
            if ($zuopin != $info['zuopin'])
                $datafj['zuopin'] = $zuopin;
            if ($is_sfz != $info['is_sfz'])
                $datafj['is_sfz'] = $is_sfz;
            if ($is_qy != $info['is_qy'])
                $datafj['is_qy'] = $is_qy;
            if ($qytime != $info['qytime'])
                $datafj['qytime'] = $qytime;
            if ($is_bzj != $info['is_bzj'])
                $datafj['is_bzj'] = $is_bzj;
            #身份证 #导航
            $path = "/Uploads/shangjia/";
            if (!empty($_FILES['dhimg']['name']) && !empty($_FILES['sfz_img']['name'])) {

                $imginf = $this->upload("." . $path);
                if (!empty($imginf[0]['savename'])) {
                    unlink("." . $info['dhimg']);
                    $datafj['dhimg'] = $path . $imginf[0]['savename'];
                }
                if (!empty($imginf[1]['savename'])) {
                    unlink("." . $info['sfz_img']);
                    $datafj['sfz_img'] = $path . $imginf[1]['savename'];
                }
            } elseif (!empty($_FILES['dhimg']['name']) && empty($_FILES['sfz_img']['name'])) {
                $imginf = $this->upload("." . $path);
                if (!empty($imginf[0]['savename'])) {
                    unlink("." . $info['dhimg']);
                    $datafj['dhimg'] = $path . $imginf[0]['savename'];
                }
            } elseif (empty($_FILES['dhimg']['name']) && !empty($_FILES['sfz_img']['name'])) {
                $imginf = $this->upload("." . $path);
                if (!empty($imginf[0]['savename'])) {
                    unlink("." . $info['sfz_img']);
                    $datafj['sfz_img'] = $path . $imginf[0]['savename'];
                }
            }
            if ($content != $info['content'])
                $datafj['content'] = $content;
            if ($telphone != $info['telphone'])
                $datafj['telphone'] = $telphone;
            if ($movphone != $info['movphone'])
                $datafj['movphone'] = $movphone;
            if ($sex != $info['sex'])
                $datafj['sex'] = $sex;
            if ($email != $info['email'])
                $datafj['email'] = $email;
            if ($gongling != $info['gongling'])
                $datafj['gongling'] = $gongling;
            if ($is_tj != $info['is_tj'])
                $datafj['is_tj'] = $is_tj;

            if ($address != $info['address'])
                $datafj['address'] = $address;
            if ($qq != $info['qq'])
                $datafj['qq'] = $qq;
            if ($p_id != $info['p_id'])
                $datafj['p_id'] = $p_id;
            if ($c_id != $info['c_id'])
                $datafj['c_id'] = $c_id;
            if ($q_id != $info['q_id'])
                $datafj['q_id'] = $q_id;
            if ($jingdu != $info['jingdu'])
                $datafj['jingdu'] = $jingdu;
            if ($weidu != $info['weidu'])
                $datafj['weidu'] = $weidu;

            if (!empty($data))
                $res1 = $m1->where("a_id=" . $aid)->save($data);
            if (!empty($datafj))
                $res2 = $m2->where("a_id=" . $aid)->save($datafj);
            if (!$res1 && !$res2) {
                $this->error("操作失败！");
            } else {
                $this->success("操作成功！", U("Member/foreman"));
            }
            exit;
        }

        $zparr = explode(",", $info['zuopin']);
        $fuwuarr = str_replace(",", "\n", $info['fuwu']);
        $this->assign("fuwuarr", $fuwuarr);
        $this->assign("zparr", $zparr);
        $this->display("addgz");
    }

    /**
     * 添加设计师
     */
    public function addsjs() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);

        $citymod = new CityModel();
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        if ($is_qx == 0) {
            #省
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
        } else {
            $p_id = $_SESSION['my_info']['proid'];
            $c_id = $_SESSION['my_info']['cityid'];
            #区
            $qulist = $citymod->getcity($c_id);
            $this->assign("qulist", $qulist);
            $this->assign("p_id", $p_id);
            $this->assign("c_id", $c_id);
        }
        $this->assign("is_qx", $is_qx);
        $fgmod = M("Fgcategory");
        $fglist = $fgmod->where("1")->select();
        $this->assign("fglist", $fglist);
        $this->assign("is_add", 1);

        if (IS_POST) {
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
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



            $picName = trim($_POST['picName']);
            $truename = trim($_POST['truename']);
            if (empty($truename)) {
                $this->error("真实姓名不能为空！");
                exit;
            }

            $sex = $_POST['sex'];
            $mvophone = trim($_POST['mvophone']);
            $koubei = trim($_POST['koubei']);
            $email = trim($_POST['email']);
            $gongling = trim($_POST['gongling']);
            $qq = trim($_POST['qq']);
            $jiguan = trim($_POST['jiguan']);
            $scfg = $_POST['scfg'];
            $zhicheng = trim($_POST['zhicheng']);
            $sjln = trim($_POST['sjln']);
            $is_tj = $_POST['is_tj'];
            $dhimg = $_FILES['dhimg']['name'];
            if (!empty($dhimg)) {
                $path = "/Uploads/shangjia/";
                $imginf = $this->upload("." . $path);
                if (!empty($imginf[0]['savename']))
                    $dhimg_str = $path . $imginf[0]['savename'];
            }
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $address = trim($_POST['address']);
            $jingdu = trim($_POST['jingdu']);
            $weidu = trim($_POST['weidu']);



            $maindata = array(
                "a_name" => $a_name,
                "a_pwd" => encrypt($pwd),
                "a_type" => 4,
                "status" => $status
            );
            $fjdata = array();
            $fjdata['logo'] = $picName;
            $fjdata['truename'] = $truename;
            $fjdata['sex'] = $sex;
            $fjdata['movphone'] = $mvophone;
            $fjdata['koubei'] = $koubei;
            $fjdata['email'] = $email;
            $fjdata['gongling'] = $gongling;
            $fjdata['qq'] = $qq;
            $fjdata['jiguan'] = $jiguan;
            $fjdata['scfg'] = $scfg;
            $fjdata['zhicheng'] = $zhicheng;
            $fjdata['sjln'] = $sjln;
            $fjdata['is_tj'] = $is_tj;
            $fjdata['dhimg'] = $dhimg_str;
            $fjdata['p_id'] = $p_id;
            $fjdata['c_id'] = $c_id;
            $fjdata['q_id'] = $q_id;
            $fjdata['address'] = $address;
            $fjdata['jingdu'] = $jingdu;
            $fjdata['weidu'] = $weidu;

            $res = $memmod->add_member($maindata, $fjdata);
            if ($res['status']) {
                $this->success($res['info'], U('Member/shejishi'));
            } else {
                $this->error($res['info']);
            }
            exit;
        }
        $this->display();
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
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        if ($is_qx == 0) {
            $pro_list = $citymod->getprovince(1);
            $this->assign("pro_list", $pro_list);
        } else {
            #地区管理员
        }
        #擅长风格
        $fgmod = M("Fgcategory");
        $fglist = $fgmod->where(1)->select();
        $this->assign("fglist", $fglist);
        #作品 案例
        $casemod = new CaseModel();
        $caselist = $casemod->getcaselist(2, $aid);
        $this->assign("caselist", $caselist);

        if (IS_POST) {
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            if (empty($a_name)) {
                $this->error("登录名称不能为空！");
                exit;
            }


            $picName = trim($_POST['picName']);
            $truename = trim($_POST['truename']);
            $sex = trim($_POST['sex']);
            $movphone = trim($_POST['mvophone']);
            $koubei = trim($_POST['koubei']);
            $email = trim($_POST['email']);
            $gongling = trim($_POST['gongling']);
            $qq = trim($_POST['qq']);
            $jiguan = trim($_POST['jiguan']);
            $scfg = trim($_POST['scfg']);
            $zhicheng = trim($_POST['zhicheng']);
            $sjln = trim($_POST['sjln']);
            $is_tj = trim($_POST['is_tj']);
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $aid = $_POST['aid'];
            $dbzp = $_POST['dbzp'];
            $dbzp_str = implode(",", $dbzp);

            $address = trim($_POST['address']);

            $jingdu = trim($_POST['jingdu']);
            $weidu = trim($_POST['weidu']);


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

            if ($picName != $info['logo'])
                $datafj['logo'] = $picName;

            if ($truename != $info['truename'])
                $datafj['truename'] = $truename;

            if ($sex != $info['sex'])
                $datafj['sex'] = $sex;
            //------------导航图片修改---start
            if (!empty($_FILES['dhimg']['name'])) {
                $path = "/Uploads/shangjia/";

                $imginf = $this->upload("." . $path);
                if (!empty($imginf[0]['savename'])) {
                    unlink("." . $path . $info['dhimg']);
                    $datafj['dhimg'] = $path . $imginf[0]['savename'];
                }
            }
            //=------------导航图片修改------end
            if ($movphone != $info['movphone'])
                $datafj['movphone'] = $movphone;
            if ($koubei != $info['koubei'])
                $datafj['koubei'] = $koubei;
            if ($email != $info['email'])
                $datafj['email'] = $email;
            if ($gongling != $info['gongling'])
                $datafj['gongling'] = $gongling;
            if ($qq != $info['qq'])
                $datafj['qq'] = $qq;
            if ($jiguan != $info['jiguan'])
                $datafj['jiguan'] = $jiguan;
            if ($dbzp_str != $info['dbzp'])
                $datafj['dbzp'] = $dbzp_str;
            if ($scfg != $info['scfg'])
                $datafj['scfg'] = $scfg;
            if ($zhicheng != $info['zhicheng'])
                $datafj['zhicheng'] = $zhicheng;
            if ($sjln != $info['sjln'])
                $datafj['sjln'] = $sjln;
            if ($is_tj != $info['is_tj'])
                $datafj['is_tj'] = $is_tj;
            if ($p_id != $info['p_id'])
                $datafj['p_id'] = $p_id;
            if ($c_id != $info['c_id'])
                $datafj['c_id'] = $c_id;
            if ($q_id != $info['q_id'])
                $datafj['q_id'] = $q_id;
            if ($address != $info['address'])
                $datafj['address'] = $address;
            if ($jingdu != $info['jingdu'])
                $datafj['jingdu'] = $jingdu;
            if ($weidu != $info['weidu'])
                $datafj['weidu'] = $weidu;

            //-------------------------end
            $fjMod = M("Sheji");
            if (!empty($datafj))
                $res2 = $fjMod->where("a_id=" . $aid)->save($datafj);
            if (!$res1 && !$res2) {
                $this->error("修改失败！");
            } else {
                $this->success("操作成功!", U("Member/shejishi"));
            }
            exit;
        }
        $this->display("addsjs");
    }

    /**
     * 添加工人
     */
    public function addgr() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $cmod = new CityModel();
        if ($is_qx == 1) {
            #地区管理员
            $c_id = $_SESSION['my_info']['cityid'];
            $p_id = $_SESSION['my_info']['proid'];

            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);
            $this->assign("c_id", $c_id);
            $this->assign("p_id", $p_id);
        } else {
            #省
            $plist = $cmod->getprovince(1);
            $this->assign("plist", $plist);
        }
        $this->assign("is_qx", $is_qx);

        if (IS_POST) {
            $a_name = trim($_POST['a_name']); //用户登录信息
            $status = $_POST['status']; //状态
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
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
            #检测用户名是否存在
            $memMod = M("Member");
            $res = $memMod->where("a_type=2 and a_name='" . $a_name . "'")->count();
            if ($res > 0) {
                $this->error("登录名已经存在！");
                exit;
            }
            //登录信息接受结束

            $picName = trim($_POST['picName']); //logo
            $truename = trim($_POST['truename']);
            $ruzhitime = trim($_POST['ruzhitime']);
            #身份证
            if (!empty($_FILES['sfz_img']['name'])) {
                $path = "/Uploads/shangjia/";
                $sfzimg = $this->upload("." . $path);
                if (!empty($sfzimg[0]['savename']))
                    $sfz_img = $path . $sfzimg[0]['savename'];
            }
            $content = trim($_POST['content']);
            $movphone = trim($_POST['movphone']);
            $sex = $_POST['sex'];
            $email = trim($_POST['email']);
            $gongling = trim($_POST['gongling']);
            $qq = trim($_POST['qq']);
            $orders = trim($_POST['orders']);
            $is_tj = $_POST['is_tj'];
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $address = trim($_POST['address']);
            $jingdu = $_POST['jingdu'];
            $weidu = $_POST['weidu'];
            $aid = $_POST['aid'];


            $maindata = array(
                "a_name" => $a_name,
                "a_pwd" => encrypt($pwd),
                "a_type" => 5,
                "status" => $status
            );
            $fjdata1 = array(
                "truename" => $truename,
                "ruzhitime" => $ruzhitime,
                "logo" => $picName,
                "sfz_img" => $sfz_img,
                "content" => $content,
                "movphone" => $movphone,
                "sex" => $sex,
                "email" => $email,
                "gongling" => $gongling,
                "address" => $address,
                "qq" => $qq,
                "orders" => $orders,
                "is_tj" => $is_tj,
                "p_id" => $p_id,
                "c_id" => $c_id,
                "q_id" => $q_id,
                "jingdu" => $jingdu,
                "weidu" => $weidu
            );
            $memmod = new MemberModel();
            $res1 = $memmod->add_member($maindata, $fjdata1);
            if ($res1) {
                $this->success("操作成功！", U("Member/grlist"));
                exit;
            } else {
                $this->error("操作失败！");
                exit;
            }
        }
        $this->display();
    }

    /**
     * 工人列表
     */
    public function grlist() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        $cmod = new CityModel();
        $q_id = $_GET['qu'];
        if ($is_qx == 1) {
            #地区管理员
            $c_id = $_SESSION['my_info']['cityid'];
            $p_id = $_SESSION['my_info']['proid'];

            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);

            $this->assign("c_id", $c_id);
            $this->assign("p_id", $p_id);
        } else {
            #省
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            $plist = $cmod->getprovince(1);
            $this->assign("plist", $plist);

            $quname = $cmod->getname($q_id);
            $this->assign("quname", $quname);
            $cityname = $cmod->getname($c_id);
            $this->assign("cityname", $cityname);
            $this->assign("province", $p_id);
        }

        $this->assign("qu", $q_id);

        $this->assign("is_qx", $is_qx);
        @import("ORG.Util.Page");
        $where = "1";
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        if (!empty($start_date)) {
            $start_date = strtotime($start_date . "0:0:0");
            $where.=" and UNIX_TIMESTAMP(ruzhitime)>= " . $start_date;
        }
        if (!empty($end_date)) {
            $end_date = strtotime($end_date . "23:59:59");
            $where.=" and UNIX_TIMESTAMP(ruzhitime)<= " . $end_date;
        }

        if (!empty($p_id)) {
            $where.=" and p_id=" . $p_id;
        }
        if (!empty($c_id)) {
            $where.=" and c_id=" . $c_id;
        }
        if (!empty($q_id)) {
            $where.=" and q_id=" . $q_id;
        }
        $keys = $_GET['keys'] == "请输入关键字" ? "" : $keys;
        if (!empty($keys))
            $where.=" and truename like '%" . $keys . "%'";

        $m = M("Gongrenview");
        $totalRows = $m->where($where)->count();
        $page = new Page($totalRows, 10);
        $list = $m->where($where)->limit($page->firstRow . "," . $page->listRows)->order("create_time desc")->select();
        $sex_arr = array("1" => "男", "2" => "女");
        foreach ($list as $k => $v) {
            $list[$k]['sexf'] = $sex_arr[$v['sex']];
        }

        $this->assign("list", $list);
        $this->assign("page", $page->show());

        $this->display();
    }

    /**
     * 删除工人
     */
    public function delgr() {
        $a_id = $_GET['aid'];
        $M=M("Gongren");
        $info=$M->where("a_id=".$a_id)->find();
        if(!empty($info['logo'])){
            $path="/avatar/";
            unlink(".".$path.$info['logo']);
            unlink(".".$path.$info['logo']."_30.jpg");
            unlink(".".$path.$info['logo']."_60.jpg");
            unlink(".".$path.$info['logo']."_100.jpg");
        }
        if(!empty($info['sfz_img'])){
            unlink(".".$info['sfz_img']);
        }
        $m1=M("Member");
        $res1=$m1->where("a_id=".$a_id)->delete();
        $res=$M->where("a_id=".$a_id)->delete();
        
        if($res1&&$res){
            $this->success("操作成功！");
        }else{
            $this->error("操作失败!");
        }
        
    }

    /**
     * 修改工人
     */
    public function editgr() {
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $aid = $_GET['aid'];
        $m = M("Gongrenview");
        $info = $m->where("a_id=" . $aid)->find();
        $this->assign("info", $info);
        $cmod = new CityModel();
        $is_qx = $this->getqx($_SESSION['my_info']['role']);
        if ($is_qx == 1) {
            #地区管理员
            $c_id = $_SESSION['my_info']['cityid'];
            $p_id = $_SESSION['my_info']['proid'];

            $qlist = $cmod->getcity($c_id);
            $this->assign("qlist", $qlist);

            $this->assign("c_id", $c_id);
            $this->assign("p_id", $p_id);
        } else {
            #省
            $p_id = $_GET['p_id'];
            $c_id = $_GET['c_id'];
            $plist = $cmod->getprovince(1);
            $this->assign("plist", $plist);


            $this->assign("province", $p_id);
        }
        $this->assign("is_qx",$is_qx);
        
        if (IS_POST) {
            $pwd = trim($_POST['pwd']);
            $confirm_pwd = trim($_POST['confirm_pwd']);
            $a_name = trim($_POST['a_name']);
            $status = trim($_POST['status']);
            //附加表------------start
            $picName = $_POST['picName']; //头像修改
            $truename = trim($_POST['truename']);
            $ruzhitime = trim($_POST['ruzhitime']);
            $sfz_img = $_FILES['sfz_img']['name']; //身份证
            $content = trim($_POST['content']);
            $movphone = trim($_POST['movphone']);
            $sex = $_POST['sex'];
            $email = trim($_POST['email']);
            $gongling = trim($_POST['gongling']);
            $qq = trim($_POST['qq']);
            $orders = trim($_POST['orders']);
            $is_tj = trim($_POST['is_tj']);
            $p_id = $_POST['p_id'];
            $c_id = $_POST['c_id'];
            $q_id = $_POST['q_id'];
            $address = $_POST['address'];
            $jingdu = $_POST['jingdu'];
            $weidu = $_POST['weidu'];

            //附加表-------------end
            $data = array();
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
            if (!empty($picName)) {
                unlink("./avatar/" . $info['logo']);
            }
            if (!empty($sfz_img)) {
                $path = "/Uploads/shangjia/";
                $imginfo = $this->upload("." . $path);
                if (!empty($imginfo[0]['savename'])){
                    $sfzimg = $imginfo[0]['savename'];
                    unlink(".".$path.$info['sfz_img']);
                }
            }
            $datafj=array();
            if(!empty($sfzimg))
                $datafj['sfz_img']=$sfzimg;
            if($truename!=$info['truename'])
                $datafj['truename']=$truename;
            if($ruzhitime!=$info['ruzhitime'])
                $datafj['ruzhitime']=$ruzhitime;
            if(!empty($picName))
                $datafj['logo']=$picName;
            if($content!=$info['content'])
                $datafj['content']=$content;
            if($movphone!=$info['movphone'])
                $datafj['movphone']=$movphone;
            if($sex!=$info['sex'])
                $datafj['sex']=$sex;
            if($email!=$info['email'])
                $datafj['email']=$email;
            if($gongling!=$info['gongling'])
                $datafj['gongling']=$gongling;
            if($address!=$info['address'])
                $datafj['address']=$address;
            if($qq!=$info['qq'])
                $datafj['qq']=$qq;
            if($orders!=$info['orders'])
                $datafj['orders']=$orders;
            if($is_tj!=$info['is_tj'])
                $datafj['is_tj']=$is_tj;
            if($p_id!=$info['p_id'])
                $datafj['p_id']=$p_id;
            if($c_id!=$info['c_id'])
                $datafj['c_id']=$c_id;
            if($q_id!=$info['q_id'])
                $datafj['q_id']=$q_id;
            if($jingdu!=$info['jingdu'])
                $datafj['jingdu']=$jingdu;
            if($weidu!=$info['weidu'])
                $datafj['weidu']=$weidu;
            
            $m1=M("Member");
            $m2=M("Gongren");
            if (!empty($data))
                $res1 = $m1->where("a_id=" . $aid)->save($data);
            else
                $res1=1;
            if (!empty($datafj)){
                $res2 = $m2->where("a_id=" . $aid)->save($datafj);
            }  else {
                $res2=1;
            }
            if (!$res1 && !$res2) {
                $this->error("操作失败！");
            } else {
                $this->success("操作成功！", U("Member/grlist"));
            }
            
            exit;
        }
        $this->display("addgr");
    }
    /**
     * 添加好友
     */
    function addfirendgx(){
       
    }
    //--------------ajax----------------------------------------------
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
     * 上传营业执照
     */
    public function scimg() {
        header('Content-Type:application/json; charset=utf-8');
        $img = $this->upload();
        echo $img[0]['savename'];
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
}

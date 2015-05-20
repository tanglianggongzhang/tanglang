<?php

/**
 * @author 李雪莲 <lixuelianlk@163.com>
 * 后台 登录
 * 2015-02-07 15:14
 */
class IndexAction extends CommonAction {

    public function index() {
        #var_dump($_SESSION[C('USER_AUTH_KEY')]);exit;
        parent::_initalize();

        $systemConfig = $this->systemConfig;
        $this->assign("systemConfig", $systemConfig);
        #var_dump($systemConfig);
        //服务器信息
        if (function_exists('gd_info')) {
            $gd = gd_info();
            $gd = $gd['GD Version'];
        } else {
            $gd = "不支持";
        }
        $info = array(
            '操作系统' => PHP_OS,
            '主机名IP端口' => $_SERVER['SERVER_NAME'] . ' (' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'] . ')',
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            '程序目录' => WEB_ROOT,
            'MYSQL版本' => function_exists("mysql_close") ? mysql_get_client_info() : '不支持',
            'GD库版本' => $gd,
//            'MYSQL版本' => mysql_get_server_info(),
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time') . "秒",
            '剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
            '服务器时间' => date("Y年n月j日 H:i:s"),
            '北京时间' => gmdate("Y年n月j日 H:i:s", time() + 8 * 3600),
            '采集函数检测' => ini_get('allow_url_fopen') ? '支持' : '不支持',
            'register_globals' => get_cfg_var("register_globals") == "1" ? "ON" : "OFF",
            'magic_quotes_gpc' => (1 === get_magic_quotes_gpc()) ? 'YES' : 'NO',
            'magic_quotes_runtime' => (1 === get_magic_quotes_runtime()) ? 'YES' : 'NO',
        );
        $this->assign('server_info', $info);

        $this->display();
    }

    public function login() {
        $systemConfig = include WEB_ROOT . 'Common/systemConfig.php';
        if (IS_POST) {
            $pubmod = new PublicModel();
            $returnLoginInfo = $pubmod->auth();
            
            if ($returnLoginInfo['status'] == 1) {
                $map = array();
                // 支持使用绑定帐号登录
                $map['a_name'] = $this->_post('name');
                import('ORG.Util.RBAC');
                $authInfo = RBAC::authenticate($map);

                $_SESSION[C('USER_AUTH_KEY')] = $authInfo['a_id'];
                #var_dump($_SESSION[C('USER_AUTH_KEY')]);exit;
                $_SESSION['a_name'] = $authInfo['a_name'];
                if ($authInfo['a_name'] == C('ADMIN_AUTH_KEY')) {//是否是管理员登录
                    $_SESSION[C('ADMIN_AUTH_KEY')] = true;
                }

                // 缓存访问权限
                RBAC::saveAccessList();
                $_SESSION['username'] = $authInfo['a_name'];



                //记录管理员log
                $data = array(
                    "a_id" => $authInfo['a_id'],
                    "l_content" => "管理员[" . $authInfo['a_name'] . "]于[" . date("Y-m-d H:i:s") . "]登录了[唐亮工长俱乐部]后台管理系统！"
                );
                M("Log")->add($data);

                $this->success("登录成功", U("Index/index"));
                exit;
            }
            else{
                $this->error($returnLoginInfo['info']);exit;
            }
        }
        $this->assign("systemConfig", $systemConfig);
        $this->display();
    }

    /**
     * 验证码
     */
    public function verify_code() {
        $w = isset($_GET['w']) ? (int) $_GET['w'] : 50;
        $h = isset($_GET['h']) ? (int) $_GET['h'] : 30;
        import("ORG.Util.Image");
        Image::buildImageVerify(4, 1, 'png', $w, $h);
    }

    /**
     * 验证验证码是否正确
     */
    public function check_code() {
        header("Content-Type:text/html; charset=utf-8");
        header('Content-Type:application/json; charset=utf-8');
        $verify = session("verify");
        $post_val = trim($_POST['post_val']);
        $post_val = md5($post_val);
        if ($verify == $post_val) {
            $arr = array("msg" => "输入正确", "code" => 1);
        } else {
            $arr = array("msg" => "输入错误", "code" => 0);
        }
        echo json_encode($arr);
    }

    /**
     * 退出
     */
    public function logout() {
        //记录管理员log
        $data = array(
            "a_id" => $_SESSION[C('USER_AUTH_KEY')],
            "l_content" => "管理员[" . $_SESSION['username'] . "]于[" . date("Y-m-d H:i:s") . "]登录了[唐亮工长俱乐部]后台管理系统！"
        );
        M("Log")->add($data);

        setcookie("$this->loginMarked", NULL, -3600, "/");
        unset($_SESSION["$this->loginMarked"], $_COOKIE["$this->loginMarked"]);
        if (isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION['username']);
            unset($_SESSION);
            session_destroy();
        }
        $this->redirect("Index/login");
    }

    /**
     * 城市管理
     */
    public function city() {
        parent::_initalize();
        $systemConfig = $this->systemConfig;
        $this->assign("systemConfig", $systemConfig);
        $id = $_GET['id'];

        $cmod = new CityModel();
        $list = $cmod->citylist($id);
        $this->assign("list", $list);
        $sjid = $cmod->getprovinceid($id);
        if (!empty($sjid))
            $this->assign("shilieb", "<a href='" . U('Index/city', array('id' => $sjid)) . "' class='a1'>返回上级</a>");
        $this->assign("c_id", $id);
        $this->display();
    }

    /**
     * 设置地区
     */
    public function setcitydq() {
        if (IS_POST) {
            $id = $_POST['id']; //省id
            $diqu = $_POST['diqu'];
            //获取市的id
            $m = new CityModel();
            $pidstr = $m->getsqid($id);
            #修改地区
            $res = $m->updatedq($pidstr, $diqu);
            if ($res) {
                $this->success("操作成功", U("Index/city"));
            } else {
                $this->error("操作失败");
            }
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $this->assign("id", $id);
        $cof = include WEB_ROOT . 'Common/config2.php';
        $this->assign("diqu", $cof['diqu']);

        $this->display();
    }

    /**
     * 启用城市
     */
    public function opNodeStatus() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        $status = $status == 0 ? 1 : 0;
        $res = M("Area")->where(array("region_id" => $id))->save(array("agency_id" => $status));
        header("Content-Type:text/html; charset=utf-8");
        header('Content-Type:application/json; charset=utf-8');
        if ($res)
            $msg = array("status" => 1, "info" => "操作成功!");
        else
            $msg = array("status" => 0, "info" => "操作失败!");
        echo json_encode($msg);
    }

    /**
     * 推荐城市
     */
    public function opTjStatus() {
        $id = $_GET['id'];
        $status = $_GET['status'];
        $status = $status == 0 ? 1 : 0;
        $res = M("Area")->where(array("region_id" => $id))->save(array("is_tj" => $status));
        header("Content-Type:text/html; charset=utf-8");
        header('Content-Type:application/json; charset=utf-8');
        if ($res)
            $msg = array("status" => 1, "info" => "操作成功!");
        else
            $msg = array("status" => 0, "info" => "操作失败!");
        echo json_encode($msg);
    }

    /**
     * 设置经纬度
     */
    public function setjw() {
        $m = M("Area");
        if (IS_POST) {
            $id = $_POST['id'];
            $pid = $_POST['pid'];
            $jingdu = trim($_POST['jingdu']);
            $weidu = trim($_POST['weidu']);
            $res = $m->where("region_id=" . $id)->save(array("jingdu" => $jingdu, "weidu" => $weidu));

            if ($res)
                $this->success("操作成功!", U("Index/city", array("id" => $pid)));
            else
                $this->error("操作失败!");
            exit;
        }
        parent::_initalize();
        $this->assign("systemConfig", $this->systemConfig);
        $id = $_GET['id'];
        $pid = $_GET['pid'];
        $this->assign("pid", $pid);
        $this->assign("id", $id);

        $info = $m->where("region_id=" . $id)->find();
        $this->assign("info", $info);
        $this->display();
    }

    /**
     * 获取经纬度
     */
    public function getjwd() {
        header('Content-Type:application/json; charset=utf-8');
        $id = $_POST['cid'];
        $m=M("Area");
        $info = $m->where("region_id=" . $id)->find();
        if(!$info){
            $info['jingdu']="116.404";
            $info['weidu']="39.915";
        }
        echo json_encode($info);
    }

}

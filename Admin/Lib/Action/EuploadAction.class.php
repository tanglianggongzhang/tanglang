<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EuploadAction
 *
 * @author 李雪莲 <lixuelianlk@163.com>
 */
class EuploadAction extends CommonAction {

    /**
     * 上传图片
     */
    public function shangchuan() {
        parent::_initalize();
        $info = $this->upload("./Uploads/edit/");

        $filename = $this->systemConfig["SITE_INFO"]['url'] . "Uploads/edit/" . $info[0]['savename'];
        $callback = $_GET['CKEditorFuncNum'];

        echo "<scripttype=\"text/javascript\">";

        echo "window.parent.CKEDITOR.tools.callFunction(" . $callback . ",'" . $filename ."','')";

        echo "</script>";
    }

    /**
     * 预览图片
     */
    public function yulan() {
        
    }

}

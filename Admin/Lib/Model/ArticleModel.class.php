<?php

class ArticleModel extends Model {
    public function check_art($title){
        $cou=$this->where("title='".$title."'")->count();
        if($cou>0)
            return true;
        else
            return false;
    }
}

?>

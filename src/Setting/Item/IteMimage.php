<?php
namespace LizusVitara\Setting\Item;


/**
 * IteMimage
 * 上传图片
 */
class IteMimage extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $id=$item['id'];
        $value=strval($item['value']);
        $html='<input type="text" value="'.$value.'" name="'.$id.'" class="input image_input" id="'.$id.'_input"/> <a id="'.$id.'" class="upload_image btn btn-primary" href="#">'.'选择图片'.'<i class="icon-image"></i></a>';
        \wp_enqueue_media(); //在设置页面需要加载媒体中心
        if ($echo) echo $html;
        return $html;
    }
}
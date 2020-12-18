<?php
namespace LizusVitara\Setting\Item;


/**
 * IteMimages
 * 多图上传
 */
class IteMimages extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $id=$item['id'];
        $value=strval($item['value']);
        $value=preg_replace('/\s/','',$value);
        $value=preg_replace('/^\|/','',$value);
        $value=preg_replace('/\|$/','',$value);
        $html=' <a id="'.$id.'" class="upload_images btn btn-primary" href="#">'.'选择图片'.'<i class="icon-images"></i></a><br><textarea name="'.$id.'" data-id="'.$id.'" class="input textarea images_textarea" rows=10 id="'.$id.'_textarea">'.$value.'</textarea>';
        \wp_enqueue_media(); //在设置页面需要加载媒体中心
        if ($echo) echo $html;
        return $html;
    }
}
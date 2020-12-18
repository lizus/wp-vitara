<?php
namespace LizusVitara\Setting\Item;


class IteMeditor extends Item {

    protected function init($item){
        parent::init($item);
        $this->output='echo';
    }
    
    protected function content($echo=false){
        $item=$this->data;
        $value=stripslashes((string)$item['value']);
        $args=array(
            'quicktags'=>1,
            'tinymce'=>1,
            'media_buttons'=>0,
            'textarea_rows'=>10,
        );
        if (\current_user_can('upload_files')) {
            $args['media_buttons']=1;
        }
        \wp_editor(str_replace(']]>', ']]&gt;', \apply_filters('the_content',$value )),$item['id'],$args);
    }
}
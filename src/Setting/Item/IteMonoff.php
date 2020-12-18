<?php
namespace LizusVitara\Setting\Item;


/**
 * IteMonoff
 * 开关按钮
 */
class IteMonoff extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $value=strval($item['value']);
        $cls='toggle-off';
        if ($value=='on') $cls='toggle-on';
        $html='<div class="toggle '.$cls.'"></div>';
        $html.='<input type="hidden" class="form_value" name="'.$item['id'].'" value="'.$value.'">';
        if ($echo) echo $html;
        return $html;
    }
}
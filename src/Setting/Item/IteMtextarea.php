<?php
namespace LizusVitara\Setting\Item;


class IteMtextarea extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $id=$item['id'];
        $value=stripslashes(strval($item['value']));
        $html='<textarea name="'.$id.'" id="'.$id.'" rows=10 class="input textarea">'.$value.'</textarea>';
        if ($echo) echo $html;
        return $html;
    }
}
<?php
namespace LizusVitara\Setting\Item;


class IteMselect extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $value=(string)$item['value'];
        $html='<div class="row selects">';
        $items=$this->get_source();
        $name=$item['id'];
        $html.='<select name="'.$name.'">';
        foreach ($items as $key => $label) {
            $checked='';
            if ($key==$value) $checked='selected="selected"';
            $html.='<option value="'.$key.'" '.$checked.'>'.$label.'</option>';
        }
        $html.='</select>';
        $html.='</div>';
        if ($echo) echo $html;
        return $html;
    }
}
<?php
namespace LizusVitara\Setting\Item;


class IteMcheckbox extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $value=(array)$item['value'];
        $html='<div class="row selects">';
        $items=$this->get_source();
        $name=$item['id'];
        foreach ($items as $key => $label) {
            $checked='';
            if (in_array($key,$value)) $checked='checked="checked"';
            $html.='<label class="checkbox col-md-3 col-lg-2 col-sm-4 col-xs-12"><input type="checkbox" '.$checked.' value="'.$key.'" name="'.$name.'[]" class="checkbox_input"><span title="'.$label.'">'.$label.'<i class="icon-checked"></i></span></label>';
        }
        $html.='</div>';
        
        if ($echo) echo $html;
        return $html;
    }
}
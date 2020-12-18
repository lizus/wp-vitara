<?php
namespace LizusVitara\Setting\Item;


class IteMradio extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $value=(string)$item['value'];
        $html='<div class="row selects">';
        $items=$this->get_source();
        $name=$item['id'];
        foreach ($items as $key => $label) {
            $checked='';
            if ($key==$value) $checked='checked="checked"';
            $html.='<label class="radio col-md-3 col-lg-2 col-sm-4 col-xs-12"><input type="radio" '.$checked.' value="'.$key.'" name="'.$name.'" class="radio_input"><span title="'.$label.'">'.$label.'<i class="icon-checked"></i></span></label>';
        }
        $html.='</div>';
        if ($echo) echo $html;
        return $html;
    }
}
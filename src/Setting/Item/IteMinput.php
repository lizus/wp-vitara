<?php
namespace LizusVitara\Setting\Item;


/**
 * IteMinput
 * 输入框
 */
class IteMinput extends Item {
    
    protected function content($echo=false){
        $item=$this->data;
        $id=$item['id'];
        $param='';
        $value=strval($item['value']);
        $type=isset($item['input_type']) ? strval($item['input_type']) : 'text';
        if ($type=='password') $param=' autocomplete="new-password" ';
        if ($type == 'date') $value=date('Y-m-d',$value);
        $html='<input type="'.$type.'" name="'.$id.'" id="'.$id.'" class="input" value="'.$value.'" '.$param.'>';
        if ($echo) echo $html;
        return $html;
    }
}
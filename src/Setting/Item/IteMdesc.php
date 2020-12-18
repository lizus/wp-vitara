<?php
namespace LizusVitara\Setting\Item;


/**
 * IteMdesc
 * 仅输出desc
 */
class IteMdesc extends Item {
    
    protected function content($echo=false){
        $html='';
        if ($echo) echo $html;
        return $html;
    }
}
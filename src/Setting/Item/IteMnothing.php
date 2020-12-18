<?php
namespace LizusVitara\Setting\Item;


/**
 * IteMnothing
 * 设置项不存在的时候使用
 */
class IteMnothing extends Item {
    protected function echo(){
        echo '';
    }
    protected function get(){
        return '';
    }
    protected function content($echo=false){
        return '';
    }
}
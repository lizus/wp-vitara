<?php

namespace LizusVitara\Setting\Item;


class IteMdragsort extends Item
{

    protected function content($echo = false)
    {
        $ObjDragsort = \LizusVitara\Singleton\Dragsort::getInstance();
        $vitara_dragsort = $ObjDragsort->dragsorts();
        $item = $this->data;
        $value = stripslashes(strval($item['value']));
        $obj = json_decode($value, true);
        $id = $item['id'];
        $dragsort_data = array();
        $dragsort = $item['dragsort'];
        $html = '<div class="dragsort_setting ' . $id . ' ' . $dragsort . '">';
        $html .= '<textarea name="' . $id . '" id="' . $id . '" rows=10 class="input textarea dragsort_textarea hide-if-js">' . $value . '</textarea>';
        if (isset($item['import_export']) && $item['import_export'] == 'yes') {
            $html .= '<label class="button btn-export">导出</label> | ';
            $html .= '<label class="btn-import button">导入<input type="file" name="import"></label><br><br>';
        }
        $html .= '<div class="row">';
        $html .= '<div class="col-lg-12 form-item">';
        $html .= '<textarea name="dragsort_items" data-id="' . $id . '_dragsort_items" data-name="dragsort_items" data-target="' . $id . '" class="input textarea hide-if-js" rows=10 id="' . $id . '_textarea">' . ($obj['dragsort_items'] ?? '') . '</textarea>';
        $len = $obj['length'] ?? 0;
        $len = intval($len);
        $html .= '<span class="btn btn-primary add_dragsort" data-dragsort="' . $dragsort . '">添加' . $item['title'] . '</span>';
        $html .= '<ul class="dragsort_items_ul ' . $dragsort . '_ul">';
        for ($i = 0; $i < $len; $i++) {
            $arr = array();
            foreach ($vitara_dragsort[$dragsort] as $key => $value) {
                $arr[$key] = $obj[$key . '_' . $i] ?? '';
            }
            $dragsort_data[$i] = $arr;
            $html .= $ObjDragsort->dragsort_item($dragsort_data[$i], $dragsort);
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>'; //row
        $html .= '</div>'; //hunter_setting
        wp_enqueue_media(); //在设置页面需要加载媒体中心

        if ($echo) echo $html;
        return $html;
    }
}

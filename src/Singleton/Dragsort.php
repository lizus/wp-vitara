<?php

namespace LizusVitara\Singleton;

class Dragsort
{
    private $dragsorts = [];
    private static $_instance = null;
    private function __construct()
    {
        add_action('wp_ajax_dragsort_item', [&$this, 'dragsort_item']);
        add_action('wp_ajax_nopriv_dragsort_item', [&$this, 'dragsort_item']);
    }
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    private function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * decode
     * 将dragsort存储的json数据字符串解析成键值对数组
     * 后台存储的dragsort值需要先stripslashes一次才行
     * @param  string $str
     * @return array
     */
    public static function decode($str)
    {
        $opt = array();
        if (empty($str) || !\is_string($str)) return $opt;
        $data = json_decode($str, true);
        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                if (preg_match('/item_([-_\w]+)_(\d+)/', $key, $match)) {
                    if (empty($opt[$match[2]]) || !is_array($opt[$match[2]])) $opt[$match[2]] = array();
                    $opt[$match[2]][$match[1]] = rawurldecode($value);
                }
            }
        }
        return $opt;
    }

    /**
     * encode
     * 将key=>value键值对数组打包成dragsort存储使用的json字符串
     * @param  array $data
     * @return string
     */
    public static function encode($data = [])
    {
        $tmp = [];
        if (is_array($data) && count($data) > 0) {
            $tmp['length'] = count($data);
            $i = 0;
            foreach ($data as $item) {
                foreach ($item as $key => $value) {
                    $key = preg_replace('/^item_/', '', $key);
                    $tmp['item_' . $key . '_' . $i] = $value;
                }
                $i++;
            }
        }
        return json_encode($tmp, JSON_UNESCAPED_UNICODE); //使用JSON_UNESCAPED_UNICODE保留中文不编码
    }

    public function add($tag = '', $arr = [])
    {
        $this->dragsorts[$tag] = $arr;
    }

    public function dragsorts()
    {
        return $this->dragsorts;
    }

    //用于js中ajax取dragsort设置项及后台显示处理
    public function dragsort_item($data = [], $dragsort = null)
    {
        if (empty($data)) $data = [];
        $vitara_dragsort = $this->dragsorts;
        $i = rand(1, 30000);
        if (isset($_POST['dragsort'])) {
            $dragsort = trim(strip_tags($_POST['dragsort']));
        }
        $setting_items = array();
        if (!empty($dragsort) && is_string($dragsort) && array_key_exists($dragsort, $vitara_dragsort)) {
            $setting_items = $vitara_dragsort[$dragsort];
        }
        $opt = '';
        $cls_add = 'padding-left-25';
        if (!empty($setting_items)) {
            $opt = '<li class="row">';
            $opt .= '<i class="icon icon-close btn btn-close" title="如误删,请不要点保存,直接刷新页面"></i>';
            $count = 0;
            foreach ($setting_items as $key => $arr) {
                if ($count > 0) $cls_add = '';
                if ($count == count($setting_items) - 1) $cls_add .= ' padding-right-40';
                $opt .= '<div class="col-md-' . @$arr['width'] . ' ' . $cls_add . '">';
                if ($count < 1) {
                    $opt .= '<em title="点此拖动排序"> ≡ </em>';
                }
                switch ($arr['type']) {
                    case 'image':
                        $opt .= '<a id="item_image_' . $i . '" class="upload_image btn btn-primary" href="#">' . @$arr['placeholder'] . '<i class="icon-image"></i></a><input name="' . $key . '" data-id="' . $key . '_' . $i . '" data-name="dragsort_item" class="input image_input hide-if-js" value="' . @$data[$key] . '"/>';
                        break;
                    case 'textarea':
                        $opt .= '<textarea name="' . $key . '" data-name="dragsort_item" placeholder="' . @$arr['placeholder'] . '" class="input" rows="5">' . @$data[$key] . '</textarea>';
                        break;
                    case 'images':
                        $opt .= '<a id="' . $key . '_' . $i . '" class="upload_images btn btn-primary" href="#">' . @$arr['placeholder'] . '<i class="icon-images"></i></a><br><textarea name="' . $key . '" data-id="' . $key . '_' . $i . '" data-name="dragsort_item" class="input textarea images_textarea" rows=10 >' . @$data[$key] . '</textarea>';
                        break;
                    case 'crop':
                        $opt .= '<a id="' . $key . '_' . $i . '" class="btn btn-primary" href="#" data-component="image-crop" data-ie9img="' . \LizusFunction\v_url(\get_bloginfo('url') . '/ajax.php', 'action=ie9img') . '" data-url="' . \LizusFunction\v_url(\get_bloginfo('url') . '/ajax.php', 'action=crop_upload') . '" data-width="' . $arr['crop_width'] . '" data-height="' . $arr['crop_height'] . '" data-size=' . (3.5 * 1024 * 1024) . '>' . @$arr['placeholder'] . '<i class="icon-images"></i></a><textarea name="' . $key . '" data-id="' . $key . '_' . $i . '" data-name="dragsort_item" class="input textarea crop_textarea" rows=10 >' . @$data[$key] . '</textarea>';
                        break;
                    default:
                        $opt .= '<input type="text" name="' . $key . '" data-name="dragsort_item" placeholder="' . @$arr['placeholder'] . '" class="input" value="' . @$data[$key] . '" title="' . @$data[$key] . '">';
                        break;
                }
                $opt .= '</div>';
                $count++;
            }
            $opt .= '</li>';
        }
        if (empty($data)) {
            echo $opt;
            die();
        } else {
            return $opt;
        }
    }
}

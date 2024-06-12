<?php

namespace LizusVitara\Setting\Item;

abstract class Item
{

    protected $data = []; //传入的设置数组
    protected $echo = 'div'; //使用div或者tr，默认div，tr适合用户资料页，类目编辑页等
    protected $output = 'echo'; //输出方式，echo,get

    function __construct($item = [])
    {
        if (is_array($item)) {
            if (isset($item['id']) && isset($item['type'])) {
                $this->init($item);
            }
        }
    }

    public function output()
    {
        if ($this->output == 'get') return $this->get();
        $this->echo();
        return true;
    }

    /**
     * init
     * 数据初始化
     * @return void
     */
    protected function init($item)
    {
        $prefix = $item['prefix'] ?? '';
        $this->data = $item;
        $this->data['id'] = \LizusFunction\v_key($item['id'], $prefix);
        $this->data['value'] = $item['default'] ?? null;
        $this->data['value'] = $item['value'] ?? $this->data['value'];
        $this->data['desc'] = $item['description'] ?? $item['desc'] ?? '';
        if (isset($item['echo']) && $item['echo'] == 'tr') $this->echo = 'tr';
        if (isset($item['output']) && $item['output'] == 'get') $this->output = 'get';
    }

    protected function echo()
    {
        echo $this->prev_content();
        $this->content(true);
        echo $this->after_content();
    }

    protected function get()
    {
        $html = $this->prev_content();
        $html .= $this->content();
        $html .= $this->after_content();
        return $html;
    }

    abstract protected function content($echo = false);

    protected function prev_content()
    {
        $item = $this->data;
        if (empty($item)) return '';
        if (isset($item['cap']) && !\current_user_can($item['cap'])) return;
        $type = $item['type'];
        $id = $item['id'];
        if ($this->echo == 'tr') {
            $html = '<tr class="form-field vitara-tr-' . $id . '">';
            if (isset($item['title'])) $html .= '<th scope="row"><label class="label" for="' . $id . '">' . $item['title'] . '</label></th>';
            $html .= '<td>';
        } else {
            $html = '<div class="set_item set_item_' . $type . ' row">';
            if ($type == 'ad') {
                $html .= '<div class=" col-md-6">';
            } else {
                $html .= '<div class=" col-md-3 col-lg-2">';
            }
            if (isset($item['title'])) $html .= '<label class="label" for="' . $id . '">' . $item['title'] . '</label>';
            if ($type == 'ad') {
                $html .= '<div class="reviews">
                <div class="review">
                ' . stripslashes((string)$item['value']) . '
                </div>
                </div>';
                $html .= '<p class="vitara-desc">' . ($item['desc'] ?? '') . '</p>';
            }
            $html .= '</div>';
            if ($type == 'ad') {
                $html .= '<div class="col-md-6"><div class="row">';
            } else {
                $html .= '<div class="col-md-9 col-lg-10"><div class="row">';
            }
        }
        $html .= '<div class="vitara-form-item col-xs-12 ' . ($item['class'] ?? '') . '">';
        return $html;
    }
    protected function after_content()
    {
        $item = $this->data;
        $type = $item['type'];
        $html = '</div>'; //form-item
        if ($type != 'ad') {
            $html .= '<p class="vitara-desc col-xs-12">' . ($item['desc'] ?? '') . '</p>';
        }
        if ($this->echo == 'tr') {
            $html .= '</td></tr>';
        } else {
            $html .= '</div></div>'; //col-
            $html .= '</div>'; //set_item
        }
        return $html;
    }
    //获取checkbox,radio的设置项
    protected function get_source()
    {
        $item = $this->data;
        $arr = array();
        $source = $item['source'];
        switch ($source['type']) {
            case 'custom':
                $arr = $source['custom'];
                break;
            case 'taxonomy':
                $rs = $this->get_taxs();
                foreach ($rs as $key => $value) {
                    $arr[$key] = __($value->label);
                }
                break;
            case 'term':
                $taxs = $this->get_taxs();
                $taxonomies = array();
                foreach ($taxs as $key => $value) {
                    $taxonomies[] = $key;
                }
                $args = array(
                    'taxonomy' => $taxonomies,
                    'hide_empty' => false,
                );
                $args = \wp_parse_args($source['query'], $args);
                $terms = \get_terms($args);
                foreach ($terms as $term) {
                    $arr[$term->taxonomy . '_' . $term->term_id] = $term->name;
                }
                break;
            case 'post_type':
                $rs = $this->get_post_types();
                foreach ($rs as $key => $value) {
                    $arr[$key] = __($value->label);
                }
                break;
            case 'post':
                $rs = new \WP_Query();
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'any',
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                );
                $args = \wp_parse_args($source['query'], $args);
                $rs->query($args);
                if ($rs->have_posts()) {
                    while ($rs->have_posts()) {
                        $rs->the_post();
                        $arr[\get_the_ID()] = \get_the_title();
                    }
                }
                \wp_reset_postdata();
                break;
            default:
                //
                break;
        }
        $arr['all_setting_empty'] = '全部不选';
        return $arr;
    }
    protected function get_taxs()
    {
        return \LizusFunction\get_taxs();
    }
    protected function get_post_types()
    {
        return \LizusFunction\get_post_types();
    }
}

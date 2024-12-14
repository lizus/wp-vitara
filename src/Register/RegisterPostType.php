<?php

namespace LizusVitara\Register;

/**
 * RegisterPostType
 * 用于简易注册自定义文章类型，添加新类型后，记得使用update=rewrite
 * https://developer.wordpress.org/reference/functions/register_post_type/
 */
class RegisterPostType
{

    private $args = [];

    /**
     * __construct
     * 建议在主题中添加register/post_type文件夹，每个文件生成一个文章类型
     * @param  array $args
     * @return void
     */
    public function __construct($args = [])
    {
        $this->args = $args;
        \add_action('init', [&$this, 'init'], 1);
        \add_filter('post_updated_messages', [&$this, 'updated_messages']);
    }
    public function init()
    {
        $pt_name = $this->args['name'];
        $pt_label = $this->args['label'];
        $pt_support = $this->args['supports'];
        $labels = array(
            'name' => $pt_name,
            'singular_name' => $pt_name,
            'add_new' => '添加新的' . $pt_name,
            'add_new_item' => '添加新的' . $pt_name,
            'edit_item' => '编辑' . $pt_name,
            'new_item' => '新的' . $pt_name,
            'all_items' => '所有的' . $pt_name,
            'view_item' => '查看' . $pt_name,
            'search_items' => '搜索' . $pt_name,
            'not_found' =>  '无法找到' . $pt_name,
            'not_found_in_trash' => '回收站中没有发现' . $pt_name,
            'parent_item_colon' => '',
            'menu_name' => $pt_name
        );
        $args = array(
            'labels' => $labels,
            'public' => $this->args['public'] ?? true,
            'rewrite' => array(
                'enabled' => true,
                'slug' => $pt_label,
                'with_front' => true,
                'pages' => true,
            ),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'taxonomies' => $this->args['taxonomies'] ?? [],
            'menu_position' => $this->args['position'] ?? null,
            'supports' => $pt_support
        );
        if (isset($this->args['exclude_from_search'])) $args['exclude_from_search'] = $this->args['exclude_from_search'];
        \register_post_type($pt_label, $args);
    }
    public function updated_messages($messages)
    {
        global $post, $post_ID;
        $pt_name = $this->args['name'];
        $pt_label = $this->args['label'];
        $messages[$pt_label] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf('%1$s已更新. <a href="%2$s">查看%1$s</a>', $pt_name, \esc_url(\get_permalink($post_ID))),
            2 => 'Custom field updated.',
            3 => 'Custom field deleted.',
            4 => sprintf('%s已更新.', $pt_name),
            // translators: %s: date and time of the revision
            5 => isset($_GET['revision']) ? sprintf('%1$s restored to revision from %2$s', $pt_name, \wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6 => sprintf('%1$s已发布. <a href="%2$s">查看%1$s</a>', $pt_name, \esc_url(\get_permalink($post_ID))),
            7 => sprintf('%s已保存.', $pt_name),
            8 => sprintf('%1$s已提交 <a target="_blank" href="%2$s">预览%1$s</a>', $pt_name, \esc_url(\add_query_arg('preview', 'true', \get_permalink($post_ID)))),
            9 => sprintf(
                '%1$s将发布于: <strong>%2$s</strong>. <a target="_blank" href="%3$s">预览%1$s</a>',
                $pt_name,
                // translators: Publish box date format, see http://php.net/date
                \date_i18n('M j, Y @ G:i', strtotime($post->post_date)),
                \esc_url(\get_permalink($post_ID))
            ),
            10 => sprintf('%1$s草稿已更新. <a target="_blank" href="%2$s">预览%1$s</a>', $pt_name, \esc_url(\add_query_arg('preview', 'true', \get_permalink($post_ID)))),
        );
        return $messages;
    }
}

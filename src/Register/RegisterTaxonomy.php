<?php
namespace LizusVitara\Register;

/**
* 用于简易注册分类类型
* https://developer.wordpress.org/reference/functions/register_taxonomy/
* label:类型slug
* name:类型名称
* type:类型挂载的文章类型数组
* depth:true为和category一样,false则和post_tag一样
*/
class RegisterTaxonomy {
    
    private $args=[];
    /**
    * __construct
    * 建议在主题中添加register/taxonomy文件夹，每个文件生成一个分类类型
    * @param  array $args
    * @return void
    */
    public function __construct($args=[]){
        $this->args=$args;
        \add_action( 'init', [&$this,'init'],2 );
    }
    
    public function init(){
        $t_name=$this->args['name'];
        $t_label=$this->args['label'];
        $t_type=$this->args['type'];
        $t_depth=$this->args['depth'];
        //----注册分类
        \register_taxonomy($t_label,$t_type,array(
            'hierarchical' => $t_depth,
            'show_tagcloud' => true,
            'show_admin_column'=>true,
            'rewrite' => array(
                'enabled' => true,
                'slug' => $t_label,
                'with_front' => true,
                'hierarchical' => true
            ),
            'labels' => array(
                'name' =>$t_name,
                'singular_name' => $t_name,
                'search_items' => sprintf('搜索%s',$t_name),
                'popular_items' => sprintf('热门%s',$t_name),
                'all_items' => sprintf('所有%s',$t_name),
                'parent_item' => sprintf('上级%s',$t_name),
                'parent_item_colon' => sprintf('上级%s：',$t_name),
                'edit_item' => sprintf('编辑%s',$t_name),
                'update_item' => sprintf('更新%s',$t_name),
                'add_new_item' => sprintf('添加新的%s',$t_name),
                'new_item_name' => sprintf('新的%s名称',$t_name),
                'separate_items_with_commas' => sprintf('用逗号分隔%s',$t_name),
                'add_or_remove_items' => sprintf('添加或删除%s',$t_name),
                'choose_from_most_used' => sprintf('从使用最多的%s中选择',$t_name),
                'menu_name' => $t_name,
            ),
        ));
    }
}
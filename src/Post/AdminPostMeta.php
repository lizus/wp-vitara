<?php
namespace LizusVitara\Post;

class AdminPostMeta{
	private $customFields =	array(),
	$id = '',
	$name = '',
	$page=array(),
	$context='',//位置 normal,side
	$pos='',//优先级 high,low
	$post__in=array(),//仅对某些文章生成meta_box
	$cap='edit_post';//用户权限
	
	function __construct($args) {
		$this->id = isset($args['id']) ? $args['id'] : 'vitara-custom-fields';
		$this->name = isset($args['name']) ? $args['name'] : __('设置','vitara');
		$this->page = isset($args['page']) ? (array)$args['page'] : array('post');
		$this->context = isset($args['context']) ? $args['context'] : 'normal';
		$this->pos = isset($args['pos']) ? $args['pos'] : 'core';
		$this->cap = isset($args['cap']) ? $args['cap'] : 'edit_post';
		$this->post__in = isset($args['post__in']) ? $args['post__in'] : array();
		$this->customFields = isset($args['customFields']) ? $args['customFields'] : array();
		\add_action( 'add_meta_boxes', array( &$this, 'createCustomFields' ) );
		\add_action( 'save_post', array( &$this, 'saveCustomFields' ), 1, 2 );
		\add_filter('manage_posts_columns', array(&$this,'add_column'));
		\add_action('manage_posts_custom_column', array(&$this,'add_column_content'));
	}
	
	function createCustomFields() {
		$pid=isset($_GET['post']) ? $_GET['post'] : 0;
		if (!empty($this->post__in) && !in_array($pid,$this->post__in)) return;
		if ( \current_user_can( $this->cap, $pid ) ) {
			if ( function_exists( 'add_meta_box' ) ) {
				if (is_array($this->page)) {
					foreach ($this->page as $p) {
						\add_meta_box( $this->id, $this->name, array( &$this, 'displayCustomFields' ), $p, $this->context, $this->pos );
					}
				}
			}
		}
	}
	
	function displayCustomFields() {
		global $post,$vitara;
		if (!empty($this->post__in) && !in_array($post->ID,(array)$this->post__in)) return;
		echo '<div class="vitara_panel">';
		foreach ( $this->customFields as $customField ) {
			if (isset($customField['cap']) && !\current_user_can( $customField['cap'], $post->ID ) ) continue;
			if(!isset($customField['id'])) continue;
			$item=array(
				'id'=>$customField['id'],
				'title'=>$customField['title'] ?? '',
				'desc'=>$customField['desc'] ?? '',
				'type'=>$customField['type'] ?? '',
				'input_type'=>$customField['input_type'] ?? '',
				'class'=>$customField['class'] ?? '',
				'source'=>$customField['source'] ?? [],
				'prefix'=>'post',
				'value'=>\get_post_meta($post->ID,\LizusFunction\v_key($customField['id'],'post'),true),
				'default'=>$customField['default'] ?? '',
				'dragsort'=>$customField['dragsort'] ?? '',
			);
			$setItem=new \LizusVitara\Setting\SettingItem($item);
			$setItem->output();
		}
		echo '</div>';
	}
	
	function saveCustomFields( $post_id, $post ) {
        if($post->post_type == 'nav_menu_item') return;
		if ( !\current_user_can( $this->cap, $post_id ) ) return ;
		foreach ( $this->customFields as $customField ) {
			$cid=$customField['id'];
			$id=\LizusFunction\v_key($cid,'post');
			$val=$_POST[ $id ] ?? null;
			if (\is_null($val)) continue;
			$default_val=\get_post_meta($post_id,$id,true);
			if ( !empty($val) && $default_val != $val ) {
				if ($val=='all_setting_empty' || (\is_array($val) && \in_array('all_setting_empty',$val))) {
					\delete_post_meta($post_id,$id);
				}else{
					if (isset($customField['input_type']) && $customField['input_type'] == 'date') $val=strtotime($val);
					\update_post_meta( $post_id, $id, $val );
					\do_action('vitara_postmeta',$id,$val,$post);
				}
			}else {
				if (empty($val) && $cid !="views") {
					\delete_post_meta($post_id,$id);
				}
			}
		}
	}
	
	function add_column($defaults) {
		$pt=\get_query_var('post_type');
		$pages=$this->page;
		if (!\in_array($pt,$pages)) return $defaults;
		foreach ( $this->customFields as $customField ) {
			if (isset($customField['show_in_column']) && $customField['show_in_column']===true) {
				$defaults[$customField['id']] = $customField['title'];
			}
		}
		return $defaults;
	}
	
	function add_column_content($column) {
		global $post;
		$pt=\get_query_var('post_type');
		$pages=$this->page;
		if (!\in_array($pt,$pages)) return ;
		foreach ($this->customFields as $item) {
			if (isset($item['show_in_column']) && $item['show_in_column']===true && $column==$item['id']) {
				$value=\get_post_meta($post->ID,\LizusFunction\v_key($item['id'],'post'),true);
				if (!empty($value)) {
					switch ($item['type']) {
						case 'image':
							$value='<a href="'.$value.'" class="colorbox" rel="group"><img src="'.$value.'" alt="thumb"></a>';
						break;
						default:
					break;
				}
				echo '<div class="vitara_meta">'.$value.'</div>';
			}
		}
	}
}
} // End Class

<?php
/**
 * This file followings the WordPress naming conventions, using
 * class-{classname}.php
 */

namespace Helper;

require_once('/var/www/wordpress/wp-load.php');

class NewPostType
{
	private $data;
	/**
	 * construct
	 */
	public function __construct($array)
	{
		$this->data=$array;
		add_action('init', array($this, 'registerNewPostType'));
	}

	/**
	 * register a custom post type called (Создает новый тип записи или изменяет имеющийся.)
	 *
	 *
	 * @return void
	 */
	public function registerNewPostType()
	{
		if($this->data){

			//$key - slug (в wordpress путь)
			//$postType["name"] - название объекта
			//$postType["singular_name"] - название единичного объекта
			//$postType["show_ui"] - //(логический) Определяет нужно ли создавать логику управления типом записи из админ-панели.
			//$postType["show_in_menu"] -(строка/логический) //Показывать ли тип записи в администраторском меню и где именно показывать
			//	                                           управление типом записи. Аргумент show_ui должен быть включен!
			foreach ($this->data as $key =>$postType){

				if(strlen($key)>3 && strlen($postType["name"])>0 && strlen($postType["singular_name"])>0){

					$supports="";
					if($postType["supports"]){
						$supports=$postType["supports"];
					}
					//это нужно для меню в админке
					$slugMenu=$postType["show_in_menu"];
					if($slugMenu = get_option($key . '_in_main_menu_select')){

					}

					$labels = array(
						'name' => __($postType["name"]),
						'singular_name' => __($postType["singular_name"]),
						'add_new' => __('Новый '),
						'add_new_item' => __('Добавить новый'),
						'edit_item' => __('Редактировать'),
						'new_item' => __('Новый'),
						'view_item' => __('Просмотр'),
						'search_items' => __('Поиск'),
						'not_found' => __('Не найден'),
						'not_found_in_trash' => __('В корзине не найдена эта категория'),
					);
					$args = array(
						'labels' => $labels,
						'has_archive' => $postType["has_archive"],
						'public' => true,
						'hierarchical' => false,
						'supports' => $supports,
						'show_ui' => $postType["show_ui"],
						'show_in_menu' => $slugMenu,
						'taxonomies' => array('post_tag', 'category'),
					);
					register_post_type($key, $args);
				}
			}
		}
	}

}
<?php
/**
 * This file followings the WordPress naming conventions, using
 * class-{classname}.php
 */

namespace Helper;

require_once($_SERVER["DOCUMENT_ROOT"].'/wp-load.php');

class ListCategorys{

	private $property;
	private $classExtra;
	/**
	 * construct
	 */
	public function __construct()
	{
		//Вспомогательный класс
		$this->classExtra = new \Helper\Extra();

		$this->property = 'ListCategorys';
	}

	/**
	 * @return string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * обираем категории в массив + сортировка
	 *
	 * @return mixed|void|null
	 */
	public function getArrayCategorys(){
		if($arrNewPostType=$this->arrayCategorys()){
			foreach ($arrNewPostType as $key =>&$postType){
				$postType["sort"]=get_option($key.'_in_main_menu_position');
				$postType["slug"]=$key;
			}

			return $this->classExtra->customSort($arrNewPostType, "sort", SORT_ASC);
		}
	}

	/**
	 * Для показа в поиске (почти все категории используются как свойства)
	 *
	 * @return array[]|void
	 */
    public function getArrayCategorysForSearch(){
        if($arrNewPostType=$this->arrayCategorys()){
            foreach ($arrNewPostType as $key =>&$postType){
                if(!$postType["show_in_search_filter"]){
                    unset($arrNewPostType[$key]);
                }

            }

            return $arrNewPostType;
        }
    }

	/**
	 * Кастомные категории
	 *
	 * @return array[]
	 */
	public function arrayCategorys(){

		//какие поля показывать по умолчанию
		$supports = array(
			'title',
			'editor',
			'excerpt',
			'custom-fields',
			'thumbnail',
			'page-attributes'
		);
		//Массив типов постов
		//ключ это post-type
		return array(
			'banner'=>[
				'name'=>"Банеры на главной",
				'singular_name'=>"Баннер",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],
			'events_calendar'=>[
				'name'=>"Мероприятия",
				'singular_name'=>"Мероприятия",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'tenders'=>[
				'name'=>"Тендеры",
				'singular_name'=>"Тендеры",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],
			/*'holding'=>[
				'name'=>"Холдинг Borwood",
				'singular_name'=>"Холдинг Borwood",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				"supports"=>$supports,
			],*/
			'investment_projects'=>[
				'name'=>"Инвестиционные проекты",
				'singular_name'=>"Инвестиционные проекты",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			/*'services'=>[
				'name'=>"Услуги агенства",
				'singular_name'=>"Услуга",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				"supports"=>$supports,
			],*/
			'projects'=>[
				'name'=>"Наши проекты",
				'singular_name'=>"Проект",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'news'=>[
				'name'=>"Новости",
				'singular_name'=>"Новость",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],
			'vacancies'=>[
				'name'=>"Вакансии",
				'singular_name'=>"Вакансия",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'slider_footer'=>[
				'name'=>"Слайдер в footer",
				'singular_name'=>"Слайдер",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_content",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],

			'city_port'=>[
				'name'=>"Города и Порты",
				'singular_name'=>"Город или Порт",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],

			'business_directory'=>[
				'name'=>"Каталог предприятий",
				'singular_name'=>"Предприятие",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'export_ratings'=>[
				'name'=>"Рейтинги",
				'singular_name'=>"Рейтинг",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'direction'=>[
				'name'=>"Сотрудники",
				'singular_name'=>"Сотрудник",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'socials'=>[
				'name'=>"Социальные сети",
				'singular_name'=>"Соц. сеть",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],
			'directing'=>[
				'name'=>"Направления",
				'singular_name'=>"Направление",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'services_category'=>[
				'name'=>"Категории для Услуг",
				'singular_name'=>"Категория для услуг",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'company_logo'=>[
				'name'=>"Лого компаний",
				'singular_name'=>"Логотип",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'stone_items'=>[
				'name'=>"Блоки с текстом",
				'singular_name'=>"Блоки с текстом",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'nice_imgs'=>[
				'name'=>"Картинки",
				'singular_name'=>"Картинки",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				'supports' => array(
					'title',
					'thumbnail',
				)
			],
			'external_investment'=>[
				'name'=>"Иностранные инвестиции",
				'singular_name'=>"Иностранные инвестиции",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports" => array(
					'title',
					'excerpt',
					'thumbnail',
				),
			],
            'subsidiaries'=>[
                'name'=>"Дочерние предприятия",
                'singular_name'=>"Дочерние предприятия",
                'show_ui'=>true,
                'show_in_menu'=>"my_site_directory",
                'sort'=>1,
                'has_archive' => true,
				'show_in_search_filter'=>true,
                "supports"=>$supports,
            ],
			'belarusian_companies'=>[
				'name'=>"Проекты Белорусских компаний",
				'singular_name'=>"Проекты Белорусских компаний",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>true,
				"supports"=>$supports,
			],
			'banner_menu'=>[
				'name'=>"Баннеры в меню",
				'singular_name'=>"Баннеры в меню",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],
			'ved_forms_posts'=>[
				'name'=>"Письма",
				'singular_name'=>"Письма",
				'show_ui'=>true,
				'show_in_menu'=>"ved_forms",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],
			/*'ved_subscription'=>[
				'name'=>"Подписчики на рассылку",
				'singular_name'=>"Подписчики на рассылку",
				'show_ui'=>true,
				'show_in_menu'=>"ved_forms",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],*/
			'ved_departments'=>[
				'name'=>"Ведомства",
				'singular_name'=>"Ведомства",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],
			'ved_chars'=>[
				'name'=>"Графики",
				'singular_name'=>"Графики",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],

			'ved_search_request'=>[
				'name'=>"Поисковые запросы",
				'singular_name'=>"Поисковые запросы",
				'show_ui'=>true,
				'show_in_menu'=>"my_site_directory",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],
			'ved_phone_mask'=>[
				'name'=>"Маски телефонов",
				'singular_name'=>"Маски телефонов",
				'show_ui'=>true,
				'show_in_menu'=>"ved_phone_mask",
				'sort'=>1,
				'has_archive' => true,
				'show_in_search_filter'=>false,
				"supports"=>$supports,
			],

		);
	}
}
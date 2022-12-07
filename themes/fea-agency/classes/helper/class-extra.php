<?php
/**
 * This file followings the WordPress naming conventions, using
 * class-{classname}.php
 */

namespace Helper;
require_once($_SERVER["DOCUMENT_ROOT"] . '/wp-load.php');

class Extra
{

	private $classListForms;

	private $property;
	//id страницы настроек с переводами
	private $trPageId;
	//ID страницы тендеры
	private $tendersPageId;
	//переводы
	private $clasVedLangTrans;
	//Параметры
	private $clasVedParametrs;

	/**
	 * construct
	 */
	public function __construct()
	{
		$this->property = 'Extra';
		//формы
		$this->classListForms = new \Helper\ListForms();

		$this->trPageId = \Helper\VedParametrs::LANGUDGE_TRANSLATE_PAGE_ID;

		$this->tendersPageId = \Helper\VedParametrs::TENDERS_PAGE_ID;

		$this->clasVedLangTrans = new \Helper\VedLanguagesTranslate();

		$this->clasVedParametrs = new \Helper\VedParametrs();

	}

	/**
	 * @return string
	 */
	public function getProperty()
	{
		return $this->property;
	}


	/**
	 *
	 * Забираем массив нужного меню
	 *
	 * @param $menu_name
	 * @return array|false
	 */
	public function getArrayMenu($menu_name)
	{
		if (!empty($menu_name)) {
			/*
			 * get_nav_menu_locations Получает массив зарегистрированных областей меню (расположений меню) и
			 * ID меню прикрепленных к каждой области.
			 */
			$locations = get_nav_menu_locations();

			if ($locations && isset($locations[$menu_name])) {
				/*
				 * Получает элементы меню навигации в виде массива, который затем можно обработать.
				 */
				$menu_items = wp_get_nav_menu_items($locations[$menu_name]);




				return $menu_items;
			}
		}

		return false;

	}

	/**
	 * Преобразуем массив меню для vue
	 *
	 * @param $menuItems
	 * @param $titleCode
	 * @return array
	 */
	public function getArrayTransformMenu($menuItems = [], $titleCode = "text")
	{
		$result = [];
		$count = 0; //счетчик для первого уровня для vue иначе нормально не работает меню при сжатии
		$count2 = 0;//счетчик для второго уровня для vue иначе нормально не работает меню при сжатии
		foreach ($menuItems as $item) {
			$id = $item->ID;
			if ($item->menu_item_parent == 0) {

				$linkLang = ($item->url ? $item->url : "#" . apply_filters('the_title', $item->post_title));
				//проверяем язык
				$linkLang = $this->checkLangUrlInMenu($linkLang);

				$active=false;
				if($linkLang==$this->getCurrentUrl()){
					$active=true;
				}

				$result[$count] = [
					"link" => $linkLang,
					$titleCode => $this->formatStringLang($item->title) ,
					"active" => $active,
				];

				$parentId = $id;
				$parentIdTemp = $count;
				$count++;
			}
			if ($item->menu_item_parent > 0) {
				if ($parentId == $item->menu_item_parent) {

					$linkLang = ($item->url ? $item->url : "#" . apply_filters('the_title', $item->post_title));
					//проверяем язык
					$linkLang = $this->checkLangUrlInMenu($linkLang);

					$formatTitle=$item->post_title?$item->post_title: $item->title;

					$result[$parentIdTemp]
					["items"]
					[$count2] = [
						"link" => $linkLang,
						$titleCode => $this->formatStringLang($formatTitle),
						"active" => false,
					];
					//если есть дочерние страницы то показывать баннер
					$result[$parentIdTemp]["show-banner"] = true;

					$parent2Id = $id;
					$parent2IdTemp = $count2;
					$parent2Name =$this->formatStringLang($formatTitle);
					$parent2URL = $item->url;
					$count2++;
				} else {

					$linkLang = ($item->url ? $item->url : "#" . apply_filters('the_title', $item->post_title));
					//проверяем язык
					$linkLang = $this->checkLangUrlInMenu($linkLang);

					$formatTitle=$item->post_title?$item->post_title: $item->title;

					$result[$parentIdTemp]["items"][$parent2IdTemp]["items"][] = [

						"link" => $linkLang,
						$titleCode => $this->formatStringLang($formatTitle),
						"active" => false,

					];
				}

			}
		}

		return $result;
	}

	/**
	 * Проверяем установленный язык и добавляем нужный раздел в URL
	 * Так сделано потому что плагин не работает с кастомными ссылками (WPGLOB)
	 *
	 * @param $url
	 * @return array|mixed|string|string[]
	 */
	public function checkLangUrlInMenu($url)
	{

		$siteUrlRoot = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' .
			$_SERVER['HTTP_HOST'];

		//язык из плагина
		$lang = \WPGlobus::Config()->language;

		if ($lang != "ru") {
			$prefLink = "/" . $lang . "/";

			if (strpos($url, $prefLink) !== false) {

			} else {
				$url = str_replace($siteUrlRoot, $siteUrlRoot . mb_substr($prefLink, 0, -1), $url);
			}
		}

		return $url;
	}

	/**
	 * Преобразование массива для bottоm типов меню
	 *
	 * @param $menuItems
	 * @return array
	 */
	public function getArrayTransformMenuBottom($menuItems = [])
	{
		$result = [];
		foreach ($menuItems as $key => $item) {
			if ($key == 0) {
				$result["link"] = $item["link"];
				$result["text"] = $item["text"];
			} else {
				$result["items"][] = [
					"link" => $item["link"],
					"text" => $item["text"]
				];
			}
		}

		return $result;
	}

	/**
	 * Получает массив данных указанной картинки: URL, ширина, высота
	 * картинки-вложения. custom-logo - wordpress
	 *
	 * @return false|mixed
	 */
	public function getCustomlogo()
	{
		$logo = get_custom_logo();
		if ($logo) {
			// получаем ссылку на логотип
			$custom_logo__url = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full');

			return $custom_logo__url[0];
		}

		return false;
	}

	/**
	 * после добавлени языковой версии лого хрнаится в поле (плагина ACF)
	 *
	 * @return false|mixed
	 */
	public function getLangLogo()
	{
		//язык (из плагина)
		$lang = $this->clasVedParametrs->getLang();
		//Прикрепленные файлы
		if ($arrLogo = get_field("logos", $this->clasVedParametrs::SETTINGS_PAGE_ID)) {
			if (isset($arrLogo[$lang]) && !empty($arrLogo[$lang])) {
				return $arrLogo[$lang];
			} else {
				return $arrLogo["ru"];
			}
		}

		return false;
	}


	/**
	 * Сортировка по значению поля
	 *
	 * @return mixed|null
	 */
	public function customSort()
	{
		$args = func_get_args();
		$data = array_shift($args);

		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row) {
					$tmp[$key] = $row[$field];
				}
				$args[$n] = $tmp;
			}
		}
		$args[] = &$data;

		call_user_func_array('array_multisort', $args);

		return array_pop($args);
	}

	/**
	 * Метод сортирует по дате
	 *
	 * @param $a_new
	 * @param $b_new
	 * @return int
	 */
	public static function customSortDate($a_new, $b_new)
	{

		$a_new = strtotime($a_new["sortdate"]);
		$b_new = strtotime($b_new["sortdate"]);

		return $b_new - $a_new;

	}


	/**
	 * Метод должен отдать название записи по url
	 *
	 * @return false|string
	 */
	public function getNamePostInUrl()
	{
		$url = $this->getCurrentUrl();
		$id = url_to_postid($url);
		if ($id > 0) {
			return get_the_title($id);
		}

		return false;

	}

	/**
	 * Текущий url страницы
	 *
	 * @return string
	 */
	public function getCurrentUrl()
	{
		return ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' .
			$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}


	/**
	 * навигационная цепочка от wordpress доработанная (не полностью) под vue
	 *
	 * @return array
	 */
	public function getBreadcrumbs()
	{
		/* === ОПЦИИ === */
		$text['home'] = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['navigation'], "first");

		$strTxt = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['navigation'], "search");
		$text['search'] = 'Результаты поиска по запросу "%s"'; // текст для страницы с результатами поиска
		if (!empty($strTxt)) {
			if ($insertResult = $this->insertFindValueInString($strTxt, "{{TEXT}}", '"%s"')) {
				$text['search'] = $insertResult;
			}
		}

		// текст
		// ссылки "Главная"
		$text['category'] = '%s'; // текст для страницы рубрики
		$text['tag'] = 'Записи с тегом "%s"'; // текст для страницы тега
		$text['author'] = 'Статьи автора %s'; // текст для страницы автора
		$text['404'] = 'Ошибка 4042'; // текст для страницы 404
		$text['page'] = 'Страница %s'; // текст 'Страница N'
		$text['cpage'] = 'Страница комментариев %s'; // текст 'Страница комментариев N'


		$show_on_home = 0; // 1 - показывать "хлебные крошки" на главной странице, 0 - не показывать
		$show_home_link = 1; // 1 - показывать ссылку "Главная", 0 - не показывать
		$show_current = 1; // 1 - показывать название текущей страницы, 0 - не показывать
		$show_last_sep = 1; // 1 - показывать последний разделитель, когда название текущей страницы не отображается, 0 - не показывать
		/* === КОНЕЦ ОПЦИЙ === */

		global $post;
		$home_url = home_url('/');
		$link = '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
		$link .= '<a class="breadcrumbs__link" href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>';
		$link .= '<meta itemprop="position" content="%3$s" />';
		$link .= '</span>';
		$parent_id = ($post) ? $post->post_parent : '';
		$home_link = sprintf($link, $home_url, $text['home'], 1);

		$items = [];

		if (is_home() || is_front_page()) {

			//echo 100;
			if ($show_on_home) {
				$items[] = [
					"link" => $home_url,
					"text" => $text['home']
				];
			}

		} else {
			//echo 200;
			$position = 0;

			if ($show_home_link) {
				$position += 1;
				$items[] = [
					"link" => $home_url,
					"text" => $text['home']
				];

				//echo 300;
			}

			if (is_category()) {
				//	echo 400;
				$parents = get_ancestors(get_query_var('cat'), 'category');
				foreach (array_reverse($parents) as $cat) {
					$position += 1;
					//	if ($position > 1) echo $sep;
					//	echo sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
				}
				if (get_query_var('paged')) {
					$position += 1;
					$cat = get_query_var('cat');
					//	echo $sep . sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
					//	echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
				} else {


					if ($show_current) {
						if ($position >= 1) {
							$items[] = [
								"link" => "",
								"text" => strip_tags(single_cat_title('', false))
							];
						}
					}

				}

			} elseif (is_search()) {


				//echo 500;
				if (get_query_var('paged')) {
					$position += 1;
					//	if ($show_home_link) echo $sep;
					//	echo sprintf($link, $home_url . '?s=' . get_search_query(), sprintf($text['search'],
					//		get_search_query()), $position);
					//	echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
				} else {
					if ($show_current) {
						$items[] = [
							"link" => "",
							"text" => sprintf($text['search'], get_search_query())
						];
					}
				}

			} elseif (is_year()) {
				/*	echo 600;
					if ($show_home_link && $show_current) echo $sep;
					if ($show_current) echo $before . get_the_time('Y') . $after;
					elseif ($show_home_link && $show_last_sep) echo $sep;*/

			} elseif (is_month()) {
				/*echo 700;
				if ($show_home_link) echo $sep;
				$position += 1;
				echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'), $position);
				if ($show_current) echo $sep . $before . get_the_time('F') . $after;
				elseif ($show_last_sep) echo $sep;*/

			} elseif (is_day()) {
				/*	echo 800;
					if ($show_home_link) echo $sep;
					$position += 1;
					echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'), $position) . $sep;
					$position += 1;
					echo sprintf($link, get_month_link(get_the_time('Y'), get_the_time('m')), get_the_time('F'), $position);
					if ($show_current) echo $sep . $before . get_the_time('d') . $after;
					elseif ($show_last_sep) echo $sep;*/

			} elseif (is_single() && !is_attachment()) {
				//echo 900;
				if (get_post_type() != 'post') {
					//echo 901;
					$position += 1;
					$post_type = get_post_type_object(get_post_type());

					//Наши проекты
					if ($post_type->name == "projects") {
						$text = apply_filters('the_title', get_the_title($this->clasVedParametrs::PROJECTS_PAGE_ID));
						//новости
					} elseif ($post_type->name == "news") {
						$text = apply_filters('the_title', get_the_title($this->clasVedParametrs::NEWS_PAGE_ID));
						//тендеры
					} elseif ($post_type->name == "tenders") {
						$text = apply_filters('the_title', get_the_title($this->clasVedParametrs::TENDERS_PAGE_ID));
					}

					if ($position > 1) {
						$items[] = [
							"link" => get_post_type_archive_link($post_type->name),
							"text" => strip_tags($this->convertHtmlInCharacters($text)),

						];
					}

					if ($show_current) {
						$items[] = [
							"link" => "#",
							"text" => strip_tags($this->convertHtmlInCharacters(get_the_title()))
						];
					}


				} else {

					//echo 902;
					$cat = get_the_category();
					$catID = $cat[0]->cat_ID;
					$catSlug = $cat[0]->slug;
					$parents = get_ancestors($catID, 'category');
					$parents = array_reverse($parents);
					$parents[] = $catID;
					foreach ($parents as $cat) {

						$position += 1;
						if ($position > 1) {
							$items[] = [
								"link" => "/" . $catSlug,
								"text" => strip_tags($this->convertHtmlInCharacters(get_cat_name($cat)))
							];
						}

					}
					if (get_query_var('cpage')) {
						/*$position += 1;
						echo $sep . sprintf($link, get_permalink(), get_the_title(), $position);
						echo $sep . $before . sprintf($text['cpage'], get_query_var('cpage')) . $after;*/
					} else {

						if ($show_current) {
							$items[] = [
								"link" => "#",
								"text" => strip_tags($this->convertHtmlInCharacters(get_the_title()))
							];
						}
					}
				}

			} elseif (is_post_type_archive()) {
				//echo 1000;
				$post_type = get_post_type_object(get_post_type());


				if (get_query_var('paged')) {
					//	echo 1001;
					$position += 1;
					//	if ($position > 1) echo $sep;
					//echo sprintf($link, get_post_type_archive_link($post_type->name), $post_type->label, $position);
					//	echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;

					$items[] = [
						"link" => "",//get_post_type_archive_link($post_type->name),
						"text" => ""//$post_type->label
					];

				} else {

					$text = $post_type->label;
					//Наши проекты
					if ($post_type->name == "projects") {
						$text = apply_filters('the_title', get_the_title($this->clasVedParametrs::PROJECTS_PAGE_ID));
						//новости
					} elseif ($post_type->name == "news") {
						$text = apply_filters('the_title', get_the_title($this->clasVedParametrs::NEWS_PAGE_ID));
						//тендеры
					} elseif ($post_type->name == "tenders") {
						$text = apply_filters('the_title', get_the_title($this->clasVedParametrs::TENDERS_PAGE_ID));
					}

					$items[] = [
						"link" => get_post_type_archive_link($post_type->name),
						"text" => $text,
					];

				}

			} elseif (is_attachment()) {
				/*echo 1100;
				$parent = get_post($parent_id);
				$cat = get_the_category($parent->ID);
				$catID = $cat[0]->cat_ID;
				$parents = get_ancestors($catID, 'category');
				$parents = array_reverse($parents);
				$parents[] = $catID;
				foreach ($parents as $cat) {
					$position += 1;
					if ($position > 1) echo $sep;
					echo sprintf($link, get_category_link($cat), get_cat_name($cat), $position);
				}
				$position += 1;
				echo $sep . sprintf($link, get_permalink($parent), $parent->post_title, $position);
				if ($show_current) echo $sep . $before . get_the_title() . $after;
				elseif ($show_last_sep) echo $sep;*/

			} elseif (is_page() && !$parent_id) {
				//echo 1200;

				$items[] = [
					"link" => get_permalink(),
					"text" => $this->convertHtmlInCharacters(get_the_title())
				];


			} elseif (is_page() && $parent_id) {
				//	echo 1300;
				$parents = get_post_ancestors(get_the_ID());
				foreach (array_reverse($parents) as $pageID) {
					//смотрим доп поле
					$strNavField = $this->getCustomField($pageID, 'navigation_title_meta_key');
					$text = $this->convertHtmlInCharacters(get_the_title($pageID));
					$items[] = [
						"link" => get_page_link($pageID),
						"text" => $this->convertHtmlInCharacters($text)
					];
				}
				unset($strNavField);
				if ($show_current) {

					$text = $this->convertHtmlInCharacters(get_the_title());
					$items[] = [
						"link" => "",
						"text" => $this->convertHtmlInCharacters($text)
					];

				}


			} elseif (is_tag()) {
				/*echo 1400;
				if (get_query_var('paged')) {
					$position += 1;
					$tagID = get_query_var('tag_id');
					echo $sep . sprintf($link, get_tag_link($tagID), single_tag_title('', false), $position);
					echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
				} else {
					if ($show_home_link && $show_current) echo $sep;
					if ($show_current) echo $before . sprintf($text['tag'], single_tag_title('', false)) . $after;
					elseif ($show_home_link && $show_last_sep) echo $sep;
				}*/

			} elseif (is_author()) {
				/*echo 1500;
				$author = get_userdata(get_query_var('author'));
				if (get_query_var('paged')) {
					$position += 1;
					echo $sep . sprintf($link, get_author_posts_url($author->ID), sprintf($text['author'], $author->display_name), $position);
					echo $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
				} else {
					if ($show_home_link && $show_current) echo $sep;
					if ($show_current) echo $before . sprintf($text['author'], $author->display_name) . $after;
					elseif ($show_home_link && $show_last_sep) echo $sep;
				}*/

			} elseif (is_404()) {
				/*echo 1600;
				if ($show_home_link && $show_current) echo $sep;
				if ($show_current) echo $before . $text['404'] . $after;
				elseif ($show_last_sep) echo $sep;*/

			} elseif (has_post_format() && !is_singular()) {
				/*echo 1700;
				if ($show_home_link && $show_current) echo $sep;
				echo get_post_format_string(get_post_format());*/
			}

		}


		return $items;
	}


	/**
	 * WP_Query
	 *
	 * @param $args
	 * @return int[]|\WP_Post[]
	 */
	public function customWPQuery($args)
	{
		$query = new \WP_Query();
		$my_posts = $query->query($args);

		return $my_posts;
	}

	/**
	 * WP_Query  with params
	 *
	 * @param $args
	 * @return array
	 */
	public function customWPQueryWithTotal($args)
	{
		$query = new \WP_Query();

		$my_posts = [
			"posts" => $query->query($args),
			"total" => $query->found_posts
		];

		return $my_posts;
	}


	/**
	 * Кол-во постов одного типа записи
	 *
	 * @param $postType
	 * @return false|mixed
	 */
	public function getCountPosts($postType, $termID = "", $yers = "")
	{
		$args = array(
			'posts_per_page' => -1,
			'post_type' => $postType,
			'cat' => $termID,
			'year' => $yers,
			'post_status' => 'publish',


		);

		if ($objResult = $this->customWPQueryWithTotal($args)) {
			return $objResult["total"];
		}

		return false;
	}

	/**
	 *
	 * Метод делает выборку новостей / проектов
	 *
	 *
	 * @param $postType
	 * @param $paged
	 * @param $limit
	 * @param $offset
	 * @param $slug
	 * @param $dateLabel
	 * @param $flagDate //убрать фильтры и дату
	 * @return array|false
	 */
	public function responsePosts($postType, $paged, $limit, $offset, $slug, $dateLabel = "", $flagDate)
	{
		//$postType = 'news';
		$arrResult = [];
		//Даты для фильтрации
		$arrFiltersYears = $this->getYearsForFilterNews($postType, $slug);
		$filterPostDate = "";
		$key = array_search($dateLabel, array_column($arrFiltersYears, 'value'));
		if (!empty($key))
			$filterPostDate = $arrFiltersYears[$key]["year"];

		//id категории (рубрики) для фильтрации
		$termID = 0;
		//Кол-во записей рубрики, если есть id категории (рубрики) для фильтрации
		$allCountCategory = 0;
		if (!empty($slug) && $slug != "all") {
			$termID = get_category_by_slug($slug)->term_id;
			$allCountCategory = $this->getCountPosts($postType, $termID, $filterPostDate);
		} else {
			if (!empty($filterPostDate)) {
				$allCountCategory = $this->getCountPosts($postType, $termID, $filterPostDate);
			}
		}

		//Категории и даты для фильтрации
		$arrFilters = $this->getCategoryForFilterNews($postType, $filterPostDate);

		$args = array(
			'posts_per_page' => $limit,
			'post_type' => $postType,
			'orderby' => 'date',
			'order' => 'DESC',
			'paged' => $paged,
			'cat' => $termID,
			'year' => $filterPostDate,
			'post_status' => 'publish',
		);
		//достаем записи по фильтру
		if ($objResult = $this->customWPQueryWithTotal($args)) {
			//страница запроса
			$arrResult["remote"] = $this->setUrlLang("/wp-json/vedroute/v1/news/");

			$arrResult["page"] = [
				"offset" => $offset,
				"limit" => $limit,
				"count" => $limit >= $objResult["total"] ? 0 : $objResult["total"],
				'post-type' => $postType,
				'show-date' => $flagDate,
				//for test
				"filterPostDate" => $filterPostDate,
				'date' => $dateLabel,
				'allCountCategory' => $allCountCategory,
				"paged" => $paged,
				"slug" => $slug,
				//-//
				"labels" => [
					"back" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['buttons'], "back"),
					"forward" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['buttons'], "forward")
				]
			];

			if ($flagDate) {
				//всего записей
				$allCount = $this->getCountPosts($postType, $filterPostDate);
				//Фильтр по категориям
				$arrResult["filters"][0] = [
					"id" => "CATEGORIES",
					"name" => "CATEGORIES",
					"type" => "tabs",
					"value" => $slug,
				];
				//Фильтр по дате
				$arrResult["filters"][1] = [
					"id" => "PERIOD",
					"name" => "PERIOD",
					"label" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['news'], "filter-period-title"),
					"type" => "select",
					"value" => $dateLabel ? $dateLabel : "period1",
				];
			}

			foreach ($objResult["posts"] as $item) {
				$id = $item->ID;

				//дата поста
				$strCustomDate = get_the_date('j,m,Y', $id);

				$cDate = $this->getDateLang($strCustomDate);

				//В зависимсоти от рубрики закрашевается лейбл превью
				if (get_the_category($id)) {
					$background = get_field("rubric-color", get_the_category($id)[0]) ? get_field("rubric-color",
						get_the_category($id)[0]) : "#094F8E";

					//Результат выборки
					$arrResult["items"][] = [
						"name" => strip_tags($this->convertHtmlInCharacters(get_the_title($id))),
						"link" => get_permalink($id),
						"image" => get_the_post_thumbnail_url($id, 'full'),
						"date" => $flagDate ? $cDate : "",
						"category" => [
							"text" => get_the_category($id)[0]->name,
							"background" => $background

						]
					];
				} else {
					//Результат выборки без категорий
					$arrResult["items"][] = [
						"name" => strip_tags($this->convertHtmlInCharacters(get_the_title($id))),
						"link" => get_permalink($id),
						"image" => get_the_post_thumbnail_url($id, 'full'),
						"date" => $flagDate ? $cDate : "",

					];
				}
			}
			//собираем все (фильтры и элементы)
			if (!empty($arrFilters["category"]) && $flagDate)
				$arrResult["filters"][0]["options"] = $arrFilters["category"];

			if (!empty($arrFiltersYears) && $flagDate)
				$arrResult["filters"][1]["options"] = $arrFiltersYears;

			if (!empty($arrResult["items"])) {
				//$arrResult["page"]["count"]=count($arrResult["items"]);
				return $arrResult;
			}
			return $arrResult;
		}

		return false;
	}


	/**
	 * Преобразует все HTML-сущности в соответствующие символы
	 *
	 * @param $string
	 * @return string
	 */
	public function convertHtmlInCharacters($string)
	{
		return html_entity_decode($string, ENT_QUOTES, "utf-8");
	}

	/**
	 * Собираем для фильтра уникальные даты
	 *
	 * @param $postType
	 * @return array|false
	 */
	public function getYearsForFilterNews($postType, $slug)
	{

		$arrResult = [];

		$cat = "";
		if (!empty($slug) && $slug != "all") {
			$cat = get_category_by_slug($slug)->term_id;
		}

		$args = array(
			'posts_per_page' => -1,  //все записи
			'post_type' => $postType,
			'orderby' => 'date',
			'order' => 'DESC',
			'post_status' => 'publish',
			'cat' => $cat,
		);

		if ($objResult = $this->customWPQueryWithTotal($args)) {
			foreach ($objResult["posts"] as $item) {
				$arrResult[] = [
					"post_date_year" => date('Y', strtotime($item->post_date)),
				];
			}

			if (!empty($arrResult)) {
				//Уникальные даты
				$arr = [];
				$arrDateTemp[] = [
					"value" => "period1",
					"text" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['news'], "filter-all-time")
				];

				foreach ($arrResult as $element) {
					if (!in_array($element["post_date_year"], $arrDateTemp)) {
						$arrDateTemp[] = $element["post_date_year"];
					}
				}

				foreach ($arrDateTemp as $key => $v) {

					if ($key > 0) {
						$arr[$key] = [
							"value" => "period" . ($key + 1),
							"text" => $v . "-" . ((int)$v - 1),
							"year" => $v
						];
					} else {
						$arr[$key] = $v;
					}
				}

				return $arr;
			}
		}

		return false;
	}

	/**
	 * Собираем уникальные категории для фильтра
	 *
	 * @return array|false
	 */
	public function getCategoryForFilterNews($postType, $year = "")
	{
		$arrResult = [];

		$args = array(
			'posts_per_page' => -1,  //все записи
			'post_type' => $postType,
			'orderby' => 'date',
			'order' => 'DESC',
			'year' => $year,
			'post_status' => 'publish',
		);

		if ($objResult = $this->customWPQueryWithTotal($args)) {

			foreach ($objResult["posts"] as $item) {

				$id = $item->ID;
				//если есть категория
				if (!empty(get_the_category($id)[0]) && !empty(get_the_category($id)[0]->slug)) {
					$objCategory = get_the_category($id)[0];
					$color = get_field("rubric-color", $objCategory) ? get_field("rubric-color", $objCategory) : "#094F8E";
					$sort = get_field("sort", $objCategory) ? get_field("sort", $objCategory) : 1000;
					$arrResult[] = [
						"value" => $objCategory->slug,
						"text" => $objCategory->name,
						"post_date_year" => date('Y', strtotime($item->post_date)),
						"background" => "linear-gradient(99.89deg, " . $color . " 19.17%, " . $color . " 120.07%)",
						"counter" => "",
						"counterColor" => $color,
						"sort" => $sort

					];
				} else {
					//если у элемента нет стикеров собираем их в одну категорию
					$arrResult[] = [
						"value" => "empty",
						"text" => "",
						"post_date_year" => date('Y', strtotime($item->post_date)),
						"background" => "linear-gradient(99.89deg, rgb(77, 78, 82) 19.17%, rgb(77, 78, 82) 120.07%)",
						"counter" => "",
						"counterColor" => "#fff",
						"sort" => 1000
					];
				}
			}


			if (!empty($arrResult)) {
				//Массив кол-ва постов каждой категории
				$arrTempCount = [];
				//Уникальные категории
				$arr["category"] = [];

				foreach ($arrResult as $item) {
					if (array_search($item["value"], array_column($arr["category"], 'value')) !== false) {
						if (isset($arrTempCount[$item["value"]]) && $arrTempCount[$item["value"]] > 0) {
							$arrTempCount[$item["value"]]++;
						} else {
							$arrTempCount[$item["value"]] = 1;
						}
					} else {
						$arr["category"][] = $item;
						if (isset($arrTempCount[$item["value"]]) && $arrTempCount[$item["value"]] > 0) {
							$arrTempCount[$item["value"]]++;
						} else {
							$arrTempCount[$item["value"]] = 1;
						}
					}
				}

				if (!empty($arr["category"])) {
					$countAll = 0;
					foreach ($arr["category"] as &$category) {
						$category["counter"] = $arrTempCount[$category["value"]];
						$countAll = $countAll + $arrTempCount[$category["value"]];
					}
					//заменяем первый элемент
					$first = [
						"value" => "all",
						"text" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['news'], "filter-all"),
						"background" => "linear-gradient(99.89deg, #000000 19.17%, #000000 120.07%)",
						"counter" => $countAll,
						"counterColor" => "#000000",
						"sort" => 0
					];
					array_unshift($arr["category"], $first);

					//Нужно удалить из табов тот раздел, у которых элементы не имеют стикеров
					$keyEmpty = array_search("empty", array_column($arr["category"], 'value'));
					if ($keyEmpty > 0) {
						unset($arr["category"][$keyEmpty]);
					}
					//сортируем для нужного отображения табов (вкладок)
					$arr["category"] = $this->customSort($arr["category"], "sort", SORT_ASC);

					return $arr;
				}
			}
		}

		return false;
	}

	/**
	 * Удаляет из массива дубли по нужному ключу
	 *
	 *
	 * @param array $array
	 * @param key $key
	 *
	 * @return array
	 */
	public function array_unique_key($array, $key)
	{
		$tmp = $key_array = array();
		$i = 0;

		foreach ($array as $val) {
			if (!in_array($val[$key], $key_array)) {
				$key_array[$i] = $val[$key];
				$tmp[$i] = $val;
			}
			$i++;
		}
		return $tmp;
	}


	/**
	 * Удаляет из объекта дубли по нужному ключу
	 *
	 * @param $object
	 * @param $key
	 * @return array
	 */
	public function object_unique_key($object, $key)
	{
		$tmp = $key_array = array();
		$i = 0;

		foreach ($object as $val) {
			if (!in_array($val->$key, $key_array)) {
				$key_array[$i] = $val->$key;
				$tmp[$i] = $val;
			}
			$i++;
		}
		return $tmp;
	}


	/**
	 * Собираем соц. сети через id
	 *
	 * @param $arrIds
	 * @param $imgCode
	 * @return array|void
	 */
	public function getArraySocialsHaveID($arrIds, $imgCode = "image")
	{
		$arrResult = [];
		foreach ($arrIds as $id) {
			if ($pathImg = get_the_post_thumbnail_url($id, 'full')) {
				$arrResult[] = [
					"label" => get_field("socials_labels", $id),
					"link" => get_field("socials_link", $id) ? get_field("socials_link", $id) : "#",
					$imgCode => $pathImg
				];
			}
		}

		if ($arrResult)
			return $arrResult;
		else
			false;
	}


	/**
	 * Преобразование для vue Контакты
	 *
	 * @param $postId
	 * @param $nameField
	 * @param $type
	 * @return array|false
	 */
	public function getArrUFPhoneEmailTransForVue($postId, $nameField, $type)
	{
		if ($type == "phone") {
			if ($phone = get_field($nameField, $postId)) {
				return [
					"link" => "tel:" . preg_replace('/\s+/', '', $phone),
					"icon" => "phone",
					"text" => $phone
				];
			}
		}
		if ($type == "mail") {
			if ($mail = get_field($nameField, $postId)) {
				return [
					"link" => "mailto:" . $mail,
					"icon" => "mail",
					"text" => $mail

				];
			}
		}

		return false;
	}

	/**
	 * Преобразование для vue Контакты (если поля в массиве по ключу)
	 *
	 * @param $id
	 * @param $nameField
	 * @param $type
	 * @return array|false
	 */
	public function getDirectionInfo($id, $nameField, $type)
	{

		if ($arrFields = get_field($nameField, $id)) {

			foreach ($arrFields as $key => $item) {

				if ($item) {
					if ($type == $key && $type == "phone") {
						return [
							"link" => "tel:" . preg_replace('/\s+/', '', $item),
							"icon" => "phone",
							"text" => $item
						];
					}
					if ($type == $key && $type == "mail") {

						return [
							"link" => "mailto:" . $item,
							"icon" => "mail",
							"text" => $item

						];
					}
					if ($type == $key && $type == "post") {
						return $item;
					}
				}

			}
		}

		return false;
	}

	public function getDirectionInfoInArray($arrFields, $type)
	{
		foreach ($arrFields as $key => $item) {

			if ($item) {
				if ($type == $key && $type == "phone") {
					return [
						"link" => "tel:" . preg_replace('/\s+/', '', $item),
						"icon" => "phone",
						"text" => $item
					];
				}
				if ($type == $key && $type == "mail") {

					return [
						"link" => "mailto:" . $item,
						"icon" => "mail",
						"text" => $item

					];
				}
				if ($type == $key && $type == "post") {
					return $item;
				}
			}
		}

		return false;
	}

	/**
	 * Преобразование для vue Файлов прикрепленных Файлов
	 *
	 * @param $arrFiles
	 * @return array
	 */
	public function getArrUFDocsTransForVue($arrFiles, $name = "")
	{


		foreach ($arrFiles as $file) {
			if ($file) {


				//для проверки с последующим разрешением просмотра
				$type = "";
				if (strpos($file["name"], 'zip') !== false) {

					$flagType = $this->getFileSubType("zip");
					$type = $this->getFileSubType("zip", true);

				} else {
					$flagType = $this->getFileSubType($file["subtype"]);
					$type = $this->getFileSubType($file["subtype"], true);
				}

				$flagVideoType=false;
				$arrVideoFormats=$this->clasVedParametrs->videoFormatShowPopup();
				if(in_array($type,$arrVideoFormats)){
					$flagVideoType=true;
				}

				//если тип не входит в массив даем скачать
				if (!empty($type) && in_array($type, $this->getАllowedFilesTypeForShow())) {

					$areResult[] = [
                        "type"=>$flagVideoType?"video":"",
						"title" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['download-file'], "title"),
						"openLink" => $file["url"],
						"link" => $file["url"],
						"icon" => $flagType,
						"text" => !empty($name) ? $name : $file["filename"],
						"size" => $this->formatFileSize($file["filesize"]),
						"downloadText" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['download-file'], "button-download"),
						"showText" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['download-file'], "button-show")
					];
				} else {
					$areResult[] = [
						"type"=>$flagVideoType?"video":"",
						"title" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['download-file'], "title"),
						"link" => $file["url"],
						"icon" => $flagType,
						"text" => !empty($name) ? $name : $file["filename"],
						"size" => $this->formatFileSize($file["filesize"]),
						"downloadText" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['download-file'], "button-download"),

					];
				}
			}

		}
		if (!empty($areResult))
			return $areResult;
		else
			false;

	}

	/**
	 * массив с разрешенными типами (нужно для vue что бы скрывать кнопку просмотр)
	 *
	 * @return string[]
	 */
	public function getАllowedFilesTypeForShow()
	{
		return [
			"pdf",
			"txt",
			"mp4",
		];
	}


	/**
	 * /**
	 * Возвращает url картинки или типа
	 *
	 * @param $subtype
	 * @param $flagkey
	 * @return false|string|string[]
	 */
	public function getFileSubType($subtype, $flagkey = false)
	{
		if (!empty($subtype)) {
			$type = "doc";
			if ($key = $this->classListForms->getExtensionsFile($subtype)) {
				$type = $key;
			}

			if ($flagkey) {
				return $type;
			} else {
				return "/local/assets/images/" . $type . ".svg";
			}

		}
		return false;


	}

	/**
	 * Размер файла
	 *
	 * @param $size
	 * @return string
	 */
	public function formatFileSize($size)
	{
		$a = array("B", "KB", "MB", "GB", "TB", "PB");
		$pos = 0;
		while ($size >= 1024) {
			$size /= 1024;
			$pos++;
		}
		return round($size, 2) . " " . $a[$pos];
	}


	/**
	 * Преобразование для vue блока с ссылками на странице Политики безопасности
	 *
	 * @param $postId
	 * @param $userFieldsCode
	 * @param $count
	 * @return array|void
	 */
	public function getArrUFBlockLinkTransForVue($postId, $userFieldsCode, $count)
	{
		$areResult = [];
		for ($i = 1; $i <= $count; $i++) {
			$name = get_field($userFieldsCode . $i, $postId);
			if (!empty($name)) {
				$areResult[] = [
					"name" => $name,
					"text" => get_field($userFieldsCode . $i . $i, $postId),
					"link" => [
						"text" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['buttons'], "detail"),
						"link" => get_field($userFieldsCode . $i . $i . $i, $postId)
					]
				];
			}
		}
		if (!empty($areResult))
			return $areResult;
		else
			false;
	}


	/**
	 * Для страницы карьера for vue
	 *
	 * @param $postId
	 * @param $userFieldsCode
	 * @param $count
	 * @return array|void
	 */
	public function getArrUFCareerTransForVue($postId, $userFieldsCode, $count)
	{
		$areResult = [];
		for ($i = 1; $i <= $count; $i++) {
			$name = get_field($userFieldsCode . $i, $postId);
			if (!empty($name)) {
				$areResult[] = [
					"title" => $name,
					"text" => get_field($userFieldsCode . $i . $i, $postId),
				];
			}
		}
		if (!empty($areResult))
			return $areResult;
		else
			false;
	}

	/**
	 * Получаем дочерние страницы
	 *
	 * @param $args
	 * @return int[]|void|\WP_Post[]
	 */
	public function getChildrenPage($args)
	{
		if ($childrens = get_children($args))
			return $childrens;
		else
			false;
	}

	/**
	 * Получаем произвольные поля по id post и ключу
	 *
	 * @param $pageId
	 * @param $metaKey
	 * @return mixed
	 */
	public function getCustomField($pageId, $metaKey)
	{
		return get_post_meta($pageId, $metaKey, true);
	}


	/**
	 * Если юзер админ
	 *
	 * @return bool
	 */
	public function isAdminEditor()
	{
		if (current_user_can('editor') || current_user_can('administrator')) {
			return true;
		}
		return false;
	}


	/**
	 * удалим все произвольные поля с ключом
	 * либо просто все одной записи
	 *
	 * @param $array
	 * @param $id
	 * @param $code
	 * @return void
	 */
	public function deletePostMeta($array, $id, $code = false)
	{

		foreach ($array as $key => $item) {
			if ($code) {
				if ($code == $key) {
					delete_post_meta($id, $code);
				}
			} else {
				delete_post_meta($id, $key);
			}
		}
	}

	/**
	 * Удаляем все поля которые не входят в наш список
	 * нужно для чистки при тестировании
	 *
	 * @param $array
	 * @param $id
	 */
	public function deleteOnePostMeta($array, $id)
	{
		//все разрешенные символьные имена
		if ($arrBlocksName = $this->getNameSiteBlock(false, [])) {

			foreach ($array as $key => $item) {
				$keymodify = preg_replace('/[0-9]+/', '', $key);
				if (!in_array($keymodify, $arrBlocksName)) {
					delete_post_meta($id, $key);
				}
			}
		}
	}

	/**
	 * Метод достает нужные символьные имена блоков
	 * Если указать ключ то вернет только этот ключ если он есть в списке
	 *
	 * @param false $key
	 * @param array $arrKeyWO
	 * @return false|mixed|string[]
	 */
	public function getNameSiteBlock($key = false, $arrKeyWO = [])
	{

		if ($array = $this->nameSiteBlock()) {

			if ($arrKeyWO) {
				$array = array_diff($array, $arrKeyWO);
			}

			if ($key) {
				if (in_array($key, $array)) {
					return $key;
				}
			} else {
				return $array;
			}
		}

		return false;
	}

	/**
	 * Убиарем из массива те блоки которых нет в списке
	 *
	 * @param $array
	 * @param false $key
	 * @param array $arrKeyWO
	 * @return false|mixed|null
	 */
	public function transformArraySiteBlockName($array, $key = false, $arrKeyWO = [], $flagSort = false)
	{
		// echo "<pre>";        print_r($array);        echo "</pre>";
		//все разрешенные блоки на этой странице
		if ($arrBlocksName = $this->getNameSiteBlock($key, $arrKeyWO)) {

			if ($key) {
				$array[$key]["position"] = $key;
				return $array[$key];
			}

			//удаляем поля которые находятся на верхнем уровне и не входят ни в какую группу
			$count = 1;
			foreach ($array as $key => &$item) {

				//если нет этого поля, то сортировка работать не будет
				if (isset($item["sort"]) || $flagSort) {
					//это нужно для того что бы была возможность размещать
					//одинкаовые блоки на странице
					$keymodify = preg_replace('/[0-9]+/', '', $key);

					if (in_array($keymodify, $arrBlocksName)) {

						$item["position"] = $key . "-" . $count;
					} else {
						unset($array[$key]);
					}


				} else {
					unset($array[$key]);
				}
				$count++;
			}


			if (is_array($array) && !$flagSort) {
				$array = $this->customSort($array, "sort", SORT_ASC);
			}

			return $array;
		}

		return false;
	}


	/**
	 * Рекурсивный метод, который ищет по ключю массив и вставляет нужный массив
	 *
	 * @param $array
	 * @param $searchfor
	 * @param $item
	 * @return array
	 */
	public function getRecursiveArrayIteration($array, $searchfor, $item)
	{
		$result = [];

		foreach ($array as $k => &$v) {

			if (is_numeric((int)$k)) {

				if ($k == $searchfor) {

					$v["items"][$item["id"]] = $item;

					$result[$k] = $v;

				} else if (is_array($array[$k])) {

					$ret = $this->getRecursiveArrayIteration($v, $searchfor, $item);
					if (count($ret)) {
						$result[$k] = $ret;
					}
				}
			}
		}
		return $result;
	}


	/**
	 * проходим рекурсивно по всему массиву и в массиве с ключом "items" заменяем ключи по порядку начиная с 0 (это
	 * надо для фронта vue)
	 *
	 * @param $array
	 * @return mixed
	 */
	public function getRecursiveArrayIterationChancgeKey($array)
	{

		foreach ($array as $key => &$items) {

			if (is_array($items)) {

				if (isset($items["items"]) && $items["items"]) {

					$items["items"] = array_values($items["items"]);
				}

				$array[$key] = $this->getRecursiveArrayIterationChancgeKey($array[$key]);
			}
		}

		return $array;
	}


	/**
	 * Данные на странице формы из самой страницы (что бы не таскать код по всем страницам)
	 *
	 * @param $pageId
	 * @return array
	 */
	public function getParamsAdditionalForms($pageId)
	{
		$arrResult = [];
		if ($pageId > 0) {
			//Все поля страницы
			$arrFeilds = get_fields($pageId);
			//контент
			$post = get_post($pageId);
			$arrResult["additional"]["htmlContent"] = apply_filters('the_content', $post->post_content);

			if (isset($arrFeilds["docs"]) && !empty($arrFeilds["docs"])) {
				//Прикрепленные файлы
				if ($arrFeilds["docs"]) {
					if ($arrDocs = $this->getArrUFDocsTransForVue($arrFeilds["docs"])) {
						$arrResult["additional"]["docs"] = $arrDocs;
					}
				}
			}

		}


		return $arrResult;
	}

	public function getGoogleKey()
	{
		return
			[
				"siteKey" => $this->clasVedParametrs->reCaptchaSiteKey(),
				"theme" => "light"
			];
	}


	/**
	 * Проверяем загруженный файл
	 *
	 * @param $formFielsFile
	 * @param $arrFile
	 * @return void
	 */
	public function checkFiles($formFielsFile)
	{
		$errorMessages["error"] = [];
		// массив, в котором будем формировать "правильный" $_FILES
		$arrayForFill = array();

		// первый уровень проходим без изменения
		foreach ($_FILES as $firstNameKey => $arFileDescriptions) {

			// а вот со второго уровня интерпритатор делает то,
			// что мне в большинстве случаев не подходит
			foreach ($arFileDescriptions as $fileDescriptionParam => $mixedValue) {
				$this->rRestructuringFilesArray($arrayForFill,
					$firstNameKey,
					$_FILES[$firstNameKey][$fileDescriptionParam],
					$fileDescriptionParam);
			}
		}

		// перегружаем $_FILES сформированным массивом
		$_FILES = $arrayForFill;

		unset($_FILES["state"]["form"]["files"]["undefined"]);

		//проверка на размер

		if(!empty($_FILES["state"]["form"]["files"])){
			$first = current($_FILES["state"]["form"]["files"]);


			if (isset($first["size"]) && $first["size"] > 0 && isset($formFielsFile["maxFileSize"]) && $formFielsFile["maxFileSize"] > 0) {
				if ($formFielsFile["maxFileSize"] > $first["size"]) {

				} else {
					array_push($errorMessages["error"], $formFielsFile["errorMessages"]["fileSizeError"]);
				}
			}

			//нужно достать полный тип
			$arrfileFormats=[];
			if($formFielsFile["allowedExtensions"]){
				foreach ($formFielsFile["allowedExtensions"] as $allowedExtension) {

					if($param=$this->classListForms->getExtensionsParam($allowedExtension))
					$arrfileFormats[$allowedExtension]=$param;
				}

			}

			//проверка на тип для формы
			$filetype = wp_check_filetype($first["name"], $arrfileFormats);

			if ($filetype['ext']) {

			} else {
				array_push($errorMessages["error"], $formFielsFile["errorMessages"]["fileExtensionError"]);
			}

			if (!empty($errorMessages["error"])) {
				return $errorMessages;


			} else {
				return $first;
			}
		}
		array_push($errorMessages["error"], $formFielsFile["errorMessages"]["fileSizeError"]);

		return $errorMessages;


	}

	/**
	 * Метод прикрепляет файл к посту
	 *
	 * @param $post_id
	 * @param $filename
	 * @return int|\WP_Error
	 */
	public function get_image_attach_id($post_id, $filename)
	{


		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$_FILES = array("upload_file" => $filename);

		$attachment_id = media_handle_upload("upload_file", 0);


		if( !is_wp_error( $attachment_id ) ){
			return $attachment_id;
		}else{
			return  false;
		}

	}


	/**
	 * Рекурсивная функция для реструктуризации массива
	 *
	 * @param array $arrayForFill Массив для заполнения.
	 *                                       Этот массив будет содержать "правильное"
	 *                                       представление $_FILES
	 * @param string $currentKey Ключ текущей позиции
	 * @param mixed $currentMixedValue Значение текущей позиции
	 * @param string $fileDescriptionParam Текущий параметр описания файла
	 *                                       (name, type, tmp_name, error или size)
	 * @return void
	 */
	function rRestructuringFilesArray(&$arrayForFill, $currentKey,
									  $currentMixedValue, $fileDescriptionParam)
	{
		if (is_array($currentMixedValue)) {
			foreach ($currentMixedValue as $nameKey => $mixedValue) {
				$this->rRestructuringFilesArray($arrayForFill[$currentKey],
					$nameKey,
					$mixedValue,
					$fileDescriptionParam);
			}
		} else {
			$arrayForFill[$currentKey][$fileDescriptionParam] = $currentMixedValue;
		}
	}

	/**
	 * Результат поиска
	 *
	 * @param $objResult
	 * @return array
	 */
	public function searchResponse($objResult)
	{

		$lang=\WPGlobus::Config()->language;

		$items = [];
		foreach ($objResult as $item) {
			$id = $item->ID;
			//категории поста (они же рубрики)
			$category = get_the_category($id);
			$categoryName = null;
			if (!empty($category)) {
				$categoryName = $category[0]->name;
			} else {
				$categoryName = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['search', "result"], "more");
			}
			//Тип поста
			$postType = get_post_type($id);
			//Навигация



			$arbreadcrumbs = [
				0 => [
					"link" => "/".$lang."/",
					"text" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['navigation'], "first")
				],
			];
			if ($postType != "page") {
				$arbreadcrumbs[] = [
					"link" =>  "/".$lang."/" . $postType,
					"text" => get_post_type_object($postType)->labels->name,
				];

			} elseif($id!=42) {
				$arbreadcrumbs[] = [
					"link" => get_permalink($id),
					"text" => get_the_title($id),
				];
			}

			//дата поста
			$strCustomDate = get_the_date('j,m,Y', $id);

			$cDate = $this->getDateLang($strCustomDate);

			//Результат
			$items[] = [
				"link" => get_permalink($id),
				"category" => $categoryName,
				"title" =>apply_filters( 'the_title', get_the_title($id)),
				"text" => apply_filters( 'the_title', get_post_field('post_excerpt',$id)),
				"date" =>$cDate,
				"breadcrumbs" => $arbreadcrumbs,
			];

		}
		return $items;
	}


	/**
	 * Тендеры
	 *
	 * @param $postType
	 * @param $paged
	 * @param $limit
	 * @param $offset
	 * @param $arrFilters
	 * @return array|false|void
	 */
	public function responseTenders($postType, $paged, $limit, $offset, $arrFilters = [])
	{
		//offset - это сдвиг, он для первой страницы отсутствует, поэтому равен 0, для второй страницы - он равен
		//limit-у, для третьей - limit*2

		$arrResult = [];


		if ($filters = $this->getFiltersTenders($postType, $arrFilters)) {
			$arrResult["filters"] = $filters;
		}


		$meta_query = [];
		$date_query = [];
		if ($arrFilters) {
			//выборка по странм
			if (isset($arrFilters["FIELD_COUNTRY"]) && !empty($arrFilters["FIELD_COUNTRY"])) {
				$meta_query[] = [
					'key' => 'country',
					'value' => $arrFilters["FIELD_COUNTRY"],
					'compare' => 'LIKE',
				];
			}
			//по отраслям
			if (isset($arrFilters["FIELD_INDUSTRY"]) && !empty($arrFilters["FIELD_INDUSTRY"])) {
				$meta_query[] = [
					'key' => 'category',
					'value' => $arrFilters["FIELD_INDUSTRY"],
				];
			}
			//по датам
			if (isset($arrFilters["EXPIRATION_DATE"]) && $arrFilters["EXPIRATION_DATE"] > 0) {

				$date = date("Y-m-d", $arrFilters["EXPIRATION_DATE"]);

				$meta_query[] = [
					'relation' => 'AND',
					array(
						'key' => 'date2',
						'value' => array($date),
						'compare' => '>=',
						'type' => 'date'
					),
					array(
						'key' => 'date1',
						'value' => array($date),
						'type' => 'date',
						'compare' => '<='
					),
				];
			}
		}


		$args = array(
			'posts_per_page' => $limit,
			'post_type' => $postType,
			'orderby' => 'date',
			'order' => 'DESC',
			'paged' => $paged,
			'post_status' => ['publish'],
			'meta_query' => $meta_query,
			's' => isset($arrFilters["FIELD_SEARCH"]) ? $arrFilters["FIELD_SEARCH"] : "",
		);

		//достаем записи по фильтру
		if ($objResult = $this->customWPQueryWithTotal($args)) {


			foreach ($objResult["posts"] as $item) {

				$id = $item->ID;
				//все поля страницы
				$arrFeilds = get_fields($id);
				//сылка на сторонний ресурс
				$link = [];
				if (isset($arrFeilds["link"]) && !empty($arrFeilds["link"])) {
					$link = [
						"link" => $arrFeilds["link"],
						"text" => apply_filters('the_title', $this->clasVedLangTrans->getFieldValue($this->trPageId,
							['buttons'], "detail"))
					];
				}


				$date1 = "";
				if (isset($arrFeilds["date1"])) {
					$date1 = $arrFeilds["date1"];
					$date1 = str_replace(".", ",", $date1);
					$date1 = $this->getDateLang($date1, ".", false);
				}
				$date2 = "";
				if (isset($arrFeilds["date2"])) {
					$date2 = $arrFeilds["date2"];
					$date2 = str_replace(".", ",", $date2);
					$date2 = $this->getDateLang($date2, ".", false);
				}


				$categoryName = isset($arrFeilds["category"]) ? $arrFeilds["category"] : "";

				$arrResult["items"][] = [
					"link" => $link,
					"category" => apply_filters('the_title', $categoryName),
					"name" => apply_filters('the_title', $item->post_title),
					"color" => isset($arrFeilds["color"]) ? $arrFeilds["color"] : "",
					"num" => isset($arrFeilds["number"]) ? $arrFeilds["number"] : "",
					"date" => $date1 . " - " . $date2,
					"priceText" => isset($arrFeilds["price"]) ? $arrFeilds["price"] : "",
					"location" => apply_filters('the_title', $item->post_excerpt),
				];


				unset($arrFeilds, $categoryName);
			}

			//	echo "<pre>";			print_r($arrResult);			echo "</pre>";

			$arrResult["title"] = get_the_title($this->tendersPageId);
			//страница запроса
			$arrResult["remote"] = $this->setUrlLang("/wp-json/vedroute/v1/tenders/");

			$arrResult["page"] = [
				"offset" => $offset,
				"limit" => $limit,
				"count" => $objResult["total"],
				'post-type' => $postType,
				//-//
				"labels" => [
					"back" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['buttons'], "back"),
					"forward" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['buttons'], "forward"),
				]
			];

			if ($arrResult) {
				return $arrResult;
			}

			return false;
		}
	}

	public function getFiltersTenders($postType, $arrFilters = [])
	{

		$filters = [];

		$args = array(
			'posts_per_page' => -1,
			'post_type' => $postType,
			'orderby' => 'date',
			'order' => 'DESC',
			'paged' => "",
			'post_status' => ['publish'],
		);

		$arrCountries = [];
		$arrIndastry = [];
		if ($objResult = $this->customWPQueryWithTotal($args)) {
			foreach ($objResult["posts"] as $item) {
				$id = $item->ID;
				//все поля страницы
				$arrFeilds = get_fields($id);
				//страны
				if (isset($arrFeilds["country"]) && !empty($arrFeilds["country"])) {
					foreach ($arrFeilds["country"] as $idCountry) {
						$arrCountries[] = [
							"text" => apply_filters('the_title', get_the_title($idCountry)),
							"value" => $idCountry,
						];
					}
				}
				//уникальные отрасли
				if (isset($arrFeilds["category"]) && !empty($arrFeilds["category"])) {
					$arrIndastry[] = [
						"text" => apply_filters('the_title', $arrFeilds["category"]),
						"value" => $arrFeilds["category"],
					];
				}
			}

			if ($arrCountries) {
				//удаляем одинковые элементы
				$arrCountriesWhite = array_unique($arrCountries, SORT_REGULAR);
				//сортируем по Алфавиту сбрасывая ключи
				sort($arrCountriesWhite);

				if ($arrCountriesWhite) {
					$filters[] = [
						"id" => "FIELD_COUNTRY",
						"name" => "FIELD_COUNTRY",
						"type" => "select",
						"value" => isset($arrFilters["FIELD_COUNTRY"]) ? (int)$arrFilters["FIELD_COUNTRY"] : null,
						"label" => null,
						"options" => $arrCountriesWhite,
						"placeholder" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['tenders'], "filter-country-title"),
						"width" => "268px"
					];
				}
			}

			if ($arrIndastry) {
				//удаляем одинковые элементы
				$arrIndastryWhite = array_unique($arrIndastry, SORT_REGULAR);
				//сортируем по Алфавиту сбрасывая ключи
				sort($arrIndastryWhite);

				$filters[] = [
					"id" => "FIELD_INDUSTRY",
					"name" => "FIELD_INDUSTRY",
					"type" => "select",
					"value" => isset($arrFilters["FIELD_INDUSTRY"]) ? $arrFilters["FIELD_INDUSTRY"] : null,
					"label" => null,
					"options" => $arrIndastryWhite,
					"placeholder" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['tenders'], "filter-industry-title"),
					"width" => "268px"
				];
			}
			//$timestamp = strtotime(date("d.m.y"));
			$timestamp = null;

			if (isset($arrFilters["EXPIRATION_DATE"]) && $arrFilters["EXPIRATION_DATE"] > 0) {
				$timestamp[] = (int)$arrFilters["EXPIRATION_DATE"];
			}

			$filters[] = [
				"id" => "EXPIRATION_DATE",
				"name" => "EXPIRATION_DATE",
				"type" => "date",
				"label" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['tenders'], "filter-expiration-date-title"),
				"placeholder" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['tenders'], "filter-expiration-date-title"),
				"dayNames" => $this->clasVedParametrs->shortNameWeek(),
				"monthNames" => $this->clasVedParametrs->nameMonthOne(),
				"value" =>
					$timestamp
				,
				"minDate" => "now",
				"maxDate" => null,
				"highlightDates" => null,
				"readonly" => false,
				"format" => "DD.MM.YYYY",
				"disabled" => false,
				"width" => "268px"
			];

			//поиск
			$filters[] = [
				"id" => "FIELD_SEARCH",
				"name" => "FIELD_SEARCH",
				"type" => "search-text",
				"value" => isset($arrFilters["FIELD_SEARCH"]) ? $arrFilters["FIELD_SEARCH"] : null,
				"label" => $this->clasVedLangTrans->getFieldValue($this->trPageId, ['tenders'], "search-label"),
				"placeholder" => null,

			];


			if ($filters) {
				return $filters;
			}

			return false;
		}
	}


	/**
	 * Список шаблонов блоков (это символьные имена групп полей  плагина АСF)
	 *
	 * @return string[]
	 */
	public function nameSiteBlock()
	{
		return [
			'ratings', //шаблон рейтингов features.json
			'about', //страницы о Беларуси
			'tabs-republic', //блок с табами и картинкой
			'ratings-export',//экспортные рейтинги
			'machine', //блок с картинкой и 3-мя карточками features-banner-3.json
			'main-banner', // главный баннер main-banner.json
			'services',
			'projects',
			'ratings-main', //рейтиги как на главной с сылками
			'news',
			'location',
			'banner', //шаблон для баннеров типа Наша миссия на странице об агенстве
			'banner-chess',
			'banner-companies',//шаблон для баннеров типа Дочерние предприятия на странице об агенстве
			'it', //шаблон блока Беларусь как IT государство - на странице О Беларуси
			'banner-it', //шаблон блока  Парк высоких технологий - на странице О Беларуси  (title-banner-banking-group.json)
			'logo', //бегкщая строка
			'stone', // шаблон блока  Великий камень - на странице О Беларуси
			'list-companies', // шаблон блока  Предприятия  - на странице О Беларуси
			'banner-united', // шаблон блока  Евразийский экономический союз  - на странице О Беларуси
			'banner-title',//
			'banner-two-line', // шаблон блока  Лучшая страна c двумя блоками картинок - на странице Почему Беларуси
			'block-export', // шаблон блока  Экспортная деятельность - на странице Почему Беларуси
			'investment-top', //шаблон блока  ТОП-5- на странице Почему Беларуси  - chess-banner-3.json
			'banner-silk', // шаблон блока  Шелковый путь - на странице Почему Беларуси
			'structure', // шаблон блока  Структуры - на странице Банковская группа hierarchy.json
			'contacts', // шаблон блока  C адресами - на странице Контакты
			'management', // шаблон блока  Руководство - на странице Контакты  contacts.json
			'block-position',  // шаблон блока  Органы управления - на странице Корпоративное управление management-banner.json
			'supervisory',// шаблон блока  Члены Наблюдательного совета - на странице Корпоративное управление positions.json
			'news', //шаблон блока  новости - на главной странице news-banner.json
			'director', // шаблон блока Генеральный директор - на странице Корпоративное управление
			'structure-director',// шаблон блока Структура - на странице Корпоративное управление
			'banner-map',//шаблон блока выгодное распопложение  - на странице Почему Беларуси
			'banner-title-two-button', //шаблон баннера с двумя кнопками - на странице FAQ
			'investment-projects',//шаблон карточке Инвестиционные проекты
			'banner-logo',  //шаблон  Холдинг Borwood logo-banner.json
			'banner-galery', //шаблон  Холдинг Borwood info-banner-company-br-forest.json
			'slider-services', ////шаблон  слайдера на детальной странице услуги
			'banner-socials', // шаблон баннера где есть иконки социальны сетей
			'banner-menu', //шаблон баннера того что можно показывать в меню
		];
	}


	/**
	 * Поиск и вставка строки
	 *
	 * @param $str
	 * @param $find
	 * @param $insert
	 * @return array|false|string|string[]
	 */
	public function insertFindValueInString($str, $find, $insert)
	{

		$pos = strpos($str, $find);

		if ($pos === false) {
		} else {
			return str_replace($find, $insert, $str);
		}

		return false;
	}

	/**
	 * Для функционала слабовидящих
	 *
	 * @param $key
	 * @return string|void
	 */
	public function getLang($key)
	{

		$arrLangs = $this->clasVedParametrs->langsLocalization();
		if ($arrLangs) {
			if (array_key_exists($key, $arrLangs)) {
				return $arrLangs[$key];
			}
		}
	}


	/**
	 * для панели языков
	 *
	 * @param $array
	 * @return array
	 */
	public function arraySelectLangs($array)
	{

		$arrResult = [];

		global $wp;

		$langCurrent = \WPGlobus::Config()->language;


		foreach ($array as $key => $lang) {

			if (!isset($lang["checkLang"]) && is_array($lang)) {

				$flagActive = false;

				if ($langCurrent == $key) {
					$flagActive = true;
				}

				if ($key == "ru")
					$key = "";

				$arrResult[] = [
					"link" => !empty($key) ? '/' . $key . '/' . $wp->request : "/" . $wp->request,
					"text" => $key,
					"fullText" => $lang["fullText"],
					"image" => $lang["image"],
					"active" => $flagActive ? true : false,
				];
			}
		}
		return $arrResult;
	}


	/**
	 * В зависимости от языка отдаем url для поиска
	 *
	 * @return string
	 */
	public function setUrlLang($path)
	{
		$arrUrl=parse_url($path);

		if($arrUrl && isset($arrUrl["path"])){
			$path=$arrUrl["path"];
		}


		//язык из плагина
		$lang = \WPGlobus::Config()->language;

		$prefLink = "";

		//языковой url
		if ($lang != "ru") {
			$prefLink = "/" . $lang;
		}

		return $prefLink . $path;
	}

	/**
	 * Нужный язык, плюс конвертация спец символов
	 *
	 * @param $str
	 * @return string
	 */
	public function formatStringLang($str)
	{
		return $this->convertHtmlInCharacters(apply_filters('the_title', $str));
	}

	/**
	 *
	 * Показывать дату в зависимости от страны
	 *
	 * @param $strDate
	 * @param $flagPoint
	 * @param $nameMonth
	 * @return mixed|string
	 */
	public function getDateLang($strDate, $flagPoint = " ", $nameMonth = true)
	{


		//язык из плагина
		$lang = $this->clasVedParametrs->getLang();

		$strCustomDate = $strDate;
		//массив даты надо для того что бы достать из бд названия месяца на других языках
		$arrCustomDate = explode(",", $strDate);


		//Месяцы из настроек (Деловой календарь)
		if ($arrMonthName = $this->clasVedParametrs->nameMonth()) {


			if (isset($arrMonthName[(int)($arrCustomDate[1])][2]) && !empty($arrMonthName[(int)($arrCustomDate[1])
				][2]) && $nameMonth) {
				$nameM = $arrMonthName[(int)($arrCustomDate[1])][2];
			}else{
				$nameM = $arrCustomDate[1];
			}


			if ($lang == "zh" || $lang == "ar") {
				$strCustomDate = $arrCustomDate[2] . $flagPoint . $nameM . $flagPoint . $arrCustomDate[0];
			} else {
				$strCustomDate = $arrCustomDate[0] . $flagPoint . $nameM . $flagPoint . $arrCustomDate[2];
			}
		}

		return $strCustomDate;
	}


	/**
	 *
	 * Добавляем список стран с масками
	 *
	 * @param $id
	 * @param $code
	 * @param $countryName
	 * @param $mask
	 * @return void
	 */
	public function addMask($id,$code,$countryName,$mask){



		$meta_query[] = [
			'key' => 'id',
			'value' => $id,
			'compare' => 'LIKE',
		];

		$args = array(
			'post_title' => $countryName,
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => "ved_phone_mask",
			'meta_query' => $meta_query,
		);

		$arResult=$this->customWPQueryWithTotal($args);


		if($arResult["total"]>0){
			if($arResult){

				$postId=$arResult["posts"][0]->ID;

				// Создаем массив данных
				$my_post = [
					'ID' =>$postId,
					'menu_order' => 1000,
				];

               // Обновляем данные в БД
				wp_update_post( wp_slash( $my_post ) );



				update_field("id", $id, $postId);
				update_field("code", $code, $postId);
				update_field("mask", $mask, $postId);
			}
		}else{
			$post_id = wp_insert_post($args);
			if (is_wp_error($post_id)) {
				// return $post_id->get_error_message();
			} else {


				update_field("id", $id, $post_id);
				update_field("code", $code, $post_id);
				update_field("mask", $mask, $post_id);



			}
		}

	}

	/**
	 * https://yandex.ru/dev/maps/jsapi/doc/2.1/dg/concepts/localization.html
	 *
	 * @param $lang
	 * @return void
	 */
	public function getlanguageRegionForMap($lang){

		if($lang!="ru"){
			if( $lang=="by"){
				$lang="ru_RU";
			}else{
				$lang="en_US";
			}
		}else{
			$lang="ru_RU";
		}

		return $lang;
	}


	public function getCurrentNowDateTimeZone(){

		$tz = 'Europe/Minsk';
		$timestamp = time();
		$dt = new \DateTime("now", new \DateTimeZone($tz)); //first argument "must" be a string
		$dt->setTimestamp($timestamp); //adjust the object to correct timestamp
		return $dt->format('d.m.Y H:i');
	}


	/**
	 * Метод сортирует по загаловку
	 *
	 * @param $a_new
	 * @param $b_new
	 * @return int
	 */
	public static function customSortTitle($a, $b)
	{
		return strcmp($a["name2"], $b["name2"]);
	}


}
<?php
/**
 * This file followings the WordPress naming conventions, using
 * class-{classname}.php
 */

namespace Helper;
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');


class SearchFilter
{

    private $property;

    private $classListForms;
    private $listForms;

    private $classListCategorys;
    private $listCategorys;

    private $listSearch; //то что нужно заменить

    private $pagesParents;

	private $calendarPageId;
	//Настройки
	private $clasVedParametrs;

	private $classExtra;


    /**
     * construct
     */
    public function __construct()
    {
        $this->property = 'SearchFilter';

        //формы
        $this->classListForms = new \Helper\ListForms();
        $this->listForms = $this->classListForms->getForms();
        //список наших категорий
        $this->classListCategorys = new \Helper\ListCategorys();
        $this->listCategorys = $this->classListCategorys->getArrayCategorysForSearch();

		$this->clasVedParametrs = new \Helper\VedParametrs();

		$this->calendarPageId=$this->clasVedParametrs::CALENDAR_PAGE_ID;

		$this->listSearch = [
            "page",
            "news",
        ];

		$this->classExtra = new \Helper\Extra();

        $this->pagesParents=[
            2773,//faq blr
            2783,//faq
            2827,//справочники
            2490,//Структура Агентства
            3416,//Деловой календарь
			//572,//услуги для белорусских компаний
		//	599,//услуги для иностранных
        ];

    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Фильтр проверяет используется объект на других страницах и если да выводит эти страницы
     *
     * @param $arrResult
     * @return array|false
     */
    public function filter($arrResult)
    {


		$result = [];

        foreach ($arrResult as $kay => $item) {


			$id = $item->ID;

			if(in_array($id,$this->clasVedParametrs->pageExcludeSearch())){
				break;
			}

            if($item->post_type=="events_calendar"){
                $result[] = get_post($this->calendarPageId);
            }else{

                //тут искать не надо
                if (array_key_exists($item->post_type, $this->listCategorys)) {

                    //если входит то делаем доп. запрос
                    if ($results = $this->getSqlResult($id)) {
                        //доссьаем объекты постов
                        foreach ($results as $post) {
                            $result[] = get_post($post->post_id);
                        }
                    }
                    //Проверяем входит наш объект в группу проверки
                }elseif(in_array($item->post_type,$this->listSearch)){
					//ищем родителей (проверочных)
                    $arrParens=get_ancestors( $id, 'page');

					if($arrParens){
                        $last= end($arrParens);
                        if(in_array($last, $this->pagesParents)){
                            $result[] = get_post($last);
                        }else{
							$result[] = $item;
						}
                    }else{

                        $result[] = $item;
                    }
                }
            }

        }
		return $result;
	}

	public function filterPostMeta($search){

		$arResult=[];
		$arResultFilter=[];
		if ($results = $this->searchMod($search)) {
			foreach ($results as $item){

				$id=$item->post_id;

				if(get_post_type($id)){
					$arResult[]=$item;
				}
			}
		}

		if(!empty($arResult)){
			$arResultFilter=$this->classExtra->object_unique_key($arResult,"post_id");
		}

		return$arResultFilter;
	}

    public function searchKey($searchKey, array $arr, array &$result)
    {
        // Если в массиве есть элемент с ключем $searchKey, то ложим в результат
        if (isset($arr[$searchKey])) {
            $result[] = $arr[$searchKey];
        }
        // Обходим все элементы массива в цикле
        foreach ($arr as $key => $param) {

            // Если эллемент массива есть массив, то вызываем рекурсивно эту функцию
            if (is_array($param)) {
                search_key($searchKey, $param, $result);
            }
        }
    }

    /**
     * Запрос ищет только те объеты в которых так или иначе используется искомая запись (по ID)
     *
     * @param $id
     * @return false
     */
    private function getSqlResult($id)
    {
        global $wpdb;
        $results = $wpdb->get_results("
                   SELECT  {$wpdb->prefix}postmeta.post_id
                      FROM {$wpdb->prefix}postmeta
                      LEFT JOIN     {$wpdb->prefix}posts
                             ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta.post_id
                      WHERE {$wpdb->prefix}postmeta.meta_value
                          LIKE '%\"" . $id . "\"%'
                          AND ({$wpdb->prefix}posts.post_type = 'page' OR  {$wpdb->prefix}posts.post_type = 'news' ) ", OBJECT);
        if ($results) {
            return $results;
        } else {
            return false;
        }
    }


	public function searchMod($search){


		global $wpdb;

	//	echo $wpdb->prefix;
		$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_value LIKE '%" . $search . "%' ");

		/*if ( $wpdb->last_error ) {
			echo 'wpdb error: ' . $wpdb->last_error;
		}*/

		if ($results) {
			return $results;


		} else {
			return false;
		}

	}

	/*public function getRevision(){

		global $wpdb;

		$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'revision'");
		if ($results) {


			return $results;


		} else {


			return false;
		}

	}*/

/*
	public function deleteRevision(){

		global $wpdb;

		$results = $wpdb->get_results("DELETE FROM {$wpdb->prefix}posts WHERE post_type = 'revision'");
		if ($results) {


			return $results;


		} else {


			return false;
		}

	}*/


	/**
	 * Записываем поисковый запрос
	 *
	 * @param $request
	 * @return void
	 */
	public function addSearchRequest($request,$lang){

		$newTXt = htmlspecialchars($request, ENT_QUOTES);

		$postTitle = "Поисковый запрос от : " . date('d.m.Y H:i');

		// Создаем массив данных новой записи
		$post_data = array(
			'post_title' => $postTitle,
			'post_content' =>$lang." : ".$newTXt,
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => "ved_search_request",
		);
		// Вставляем запись в базу данных
		$post_id = wp_insert_post($post_data);
		if (is_wp_error($post_id)) {
			// return $post_id->get_error_message();
		}
	}


}

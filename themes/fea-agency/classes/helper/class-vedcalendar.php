<?php

namespace Helper;

class VedCalendar
{
	private $classExtra;

	private $property;

	private $filters;

	private $countiesPageId;

	private $calendarPageId;

	const NUMBER_CELLS_CALENDAR = 35;

	//переводы
	private $clasVedLangTrans;
	//Настройки
	private $clasVedParametrs;

	private $listTitle;

	private $trPageId;

	/**
	 * construct
	 */
	public function __construct()
	{
		$this->property = 'VedCalendar';

		//Вспомогательный класс
		$this->classExtra = new \Helper\Extra();

		$this->countiesPageId=\Helper\VedParametrs::COUNTRIES_PAGE_ID;

		$this->clasVedLangTrans = new \Helper\VedLanguagesTranslate();

		$this->clasVedParametrs = new \Helper\VedParametrs();

		$this->calendarPageId=$this->clasVedParametrs::CALENDAR_PAGE_ID;

		$this->trPageId=$this->clasVedParametrs::LANGUDGE_TRANSLATE_PAGE_ID;
	}

	/**
	 * @return string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Собираем календарь
	 *
	 * @param $title
	 * @param $year
	 * @param $month
	 * @return array
	 */
	public function getNewCalendar($title, $year, $month,$action="")
	{


		$this->listTitle=$this->getPageField($this->calendarPageId);


		$arrResult = [];
		//заголовок
		$arrResult["title"] = $title;

		$arrResult["weak"] = !empty($action)?($action=="next"?"first":"last"):"";

		//текст кнопки
		$arrResult["andMoreText"] =  $this->clasVedLangTrans->getFieldValue($this->trPageId,	['business-calendar'],"andMoreText");
		//неделя - месяц
		$arrResult["tabs"] = [
			"weak" => $this->clasVedLangTrans->getFieldValue($this->trPageId,	['business-calendar'],"weak"),
			"month" => $this->clasVedLangTrans->getFieldValue($this->trPageId,	['business-calendar'],"month")
		];
		$arrResult["remote"] = $this->classExtra->setUrlLang("/wp-json/vedroute/v1/calendar/".$year);
		//дни недели
		$arrResult["dayNames"] = $this->getShortNameWeek();

		$arrResult["year"] = [
			"value" => "year_" . $year,
			"text" => $year
		];
        //кнопка очистить все
		$arrResult["resetFilter"]= $this->clasVedLangTrans->getFieldValue($this->trPageId,	['business-calendar'],"resetFilter");

		$arrResult["month"] = [
			"value" => "value_" . strtolower($this->getNameMonth($month)[0]),
			"text" => strtolower($this->getNameMonth($month)[1]),
			"pluralizeWord" => strtolower($this->getNameMonth($month)[2])
		];

		//фильтры
		$this->filters = $this->getCalendarFilter();
		$arrResult["filters"] = $this->filters;
		//дни
		$arrResult["days"] = $this->getAllDaysMonth($year, $month);
		//Мероприятия в боковой панели
		if ($arrEvents = $this->getEvents("", "", "", false, 3,"event")) {

			//заголовок правого блока
			$arrBlockRight=get_field('block-right', $this->calendarPageId);
            //сортируем по дате
			usort($arrEvents, ["\Helper\Extra","customSortDate"]);
			$arrEventsRevers=array_reverse($arrEvents);

			$arrResult["nearestEvents"] = [
				"title" => $arrBlockRight["title"]? $arrBlockRight["title"]:"Ближайшие мероприятия",
				"items" => $arrEventsRevers
			];
		}

		return $arrResult;
	}

	/**
	 * Собираем фильтры на основе одгого поста
	 *
	 * @return array
	 */
	public function getCalendarFilter()
	{

		$arrResult = [];

		$args = array(
			'posts_per_page' => 1,
			'post_type' => "events_calendar",
			'orderby' => 'date',
			'order' => 'DESC',
			'post_status' => ['publish'],

		);
		//достаем записи по фильтру
		if ($objResult = $this->classExtra->customWPQueryWithTotal($args)) {

			if ($objResult["total"] > 0) {

				foreach ($objResult["posts"] as $item) {

					$id = $item->ID;
					//Список типов мероприятий
					if ($arr = get_field_object("ved_event_type", $id)) {
						if (isset($arr["choices"])) {

							$options[] = [
								"value" => "all",
								"text" => $this->clasVedLangTrans->getFieldValue($this->trPageId,	['business-calendar'],"all"),
							];
							$count = 0;
							foreach ($arr["choices"] as $choices) {
								$options[] = [
									"value" => "value_" . $arr["name"] . "_" . $count,
									"text" => apply_filters( 'the_title', $choices )
								];

								$count++;
							}
							$name="";
							if(isset($this->listTitle["calendar"]) && !empty($this->listTitle["calendar"]["event"])){
								$name=$this->listTitle["calendar"]["event"];
							}

							$arrResult[] = [
								"id" => $arr["name"],
								"name" => $arr["name"],
								"type" => "select",
								"label" => null,
								"value" => null,
								"placeholder" => !empty($name)?$name:"Выберите категорию",
								"options" => $options,
								"width"=> "268px"
							];

							unset($name);
						}
					}
					unset($arr, $options, $choices, $count);

					//Список отраслей
					if ($arr = get_field_object("ved_industry", $id)) {
						if (isset($arr["choices"])) {

							$options[] = [
								"value" => "all",
								"text" =>  $this->clasVedLangTrans->getFieldValue($this->trPageId,	['business-calendar'],"all")
							];
							$count = 0;
							foreach ($arr["choices"] as $choices) {
								$options[] = [
									"value" => "value_" . $arr["name"] . "_" . $count,
									"text" => apply_filters( 'the_title', $choices )
								];

								$count++;
							}

							$name="";
							if(isset($this->listTitle["calendar"]) && !empty($this->listTitle["calendar"]["industry"])){
								$name=$this->listTitle["calendar"]["industry"];
							}

							$arrResult[] = [
								"id" => $arr["name"],
								"name" => $arr["name"],
								"type" => "select",
								"label" => null,
								"value" => null,
								"placeholder" =>  !empty($name)?$name:"Выберите отрасль",
								"options" => $options,
								"width"=> "268px"
							];

							unset($name);
						}
					}
					unset($arr, $options, $choices, $count);

				}
			}
		}
		//Страны
		if ($arrCounties = $this->getCountries("countries")) {

			$name="";
			if(isset($this->listTitle["calendar"]) && !empty($this->listTitle["calendar"]["country"])){
				$name=$this->listTitle["calendar"]["country"];
			}


			$arrResult[] = [
				"id" => "countries",
				"name" => "countries",
				"type" => "select",
				"label" => null,
				"value" => null,
				"placeholder" =>!empty($name)?$name: "Выберите страну",
				"options" => $arrCounties,
				"width"=> "268px"
			];

			unset($name);
		}


		return $arrResult;
	}

	/**
	 * Достаем страны
	 *
	 * @param $fieldId
	 * @return void
	 */
	public function getCountries($fieldId)
	{

		$arrResult = [];

		$args = array(
			'posts_per_page' => -1,
			'post_type' => "page",
			'post_parent' => $this->countiesPageId,
			'post_status' => ['publish'],

		);
		//достаем записи по фильтру
		if ($objResult = $this->classExtra->customWPQueryWithTotal($args)) {

			if ($objResult["total"] > 0) {
				foreach ($objResult["posts"] as $item) {
					$arrResult[] = [
						"text" => $this->classExtra->formatStringLang($item->post_title),
					];
				}
			}
			sort($arrResult);
			foreach ($arrResult as $key => &$country) {
				$country = [
					"text" => $country["text"],
					"value" => "value_" . $fieldId . "_" . $key,
				];
			}
		}

		$first = [
			"value" => "all",
			"text" => $this->clasVedLangTrans->getFieldValue($this->trPageId,	['business-calendar'],"all")
		];
		array_unshift($arrResult, $first);

		return $arrResult;
	}


	/**
	 * Мероприятия
	 *
	 * @param $year
	 * @param $month
	 * @param $day
	 * @return array|false|void
	 */
	public function getEvents($year, $month, $day, $flagFilterDate = true, $flagCountPost = -1,$title="")
	{
		$meta_query = [];
		if ($flagFilterDate) {
            //если февраль
			if($month==2){
				if($this->isLeap($year)){
					$day=29;
				}else{
					$day=28;
				}
			}

			$meta_query[] = [
				array(
					'key' => 'date1',
					'value' => array($year . "-" . $month . "-" . $day),
					'compare' => '=',
					'type' => 'date'
				),
			];


		}else{
			/*$meta_query[]=[
				'relation' => 'AND',
				array(
					'key'     => 'date2',
					'value'   => array( date("Y-m-d")),
					'compare' => '>=',
					'type'    => 'date'
				),
				array(
					'key'     => 'date1',
					'value'   =>  array(date("Y-m-d")),
					'type'      => 'date',
					'compare' =>  '<='
				),



			];*/
			$meta_query[]=[
				'relation' => 'AND',
				array(
					'key'     => 'date1',
					'value'   => array( date("Y-m-d")),
					'compare' => '>=',
					'type'    => 'date'
				),




			];
		}

		$args = array(
			'posts_per_page' => $flagCountPost,
			'post_type' => "events_calendar",
			'orderby' => 'date',
			'order' => 'DESC',
			'post_status' => ['publish'],
			'meta_query' => $meta_query,

		);
		//достаем записи по фильтру
		if ($objResult = $this->classExtra->customWPQueryWithTotal($args)) {

			$arrResult = [];

			if ($objResult["total"] > 0) {
				foreach ($objResult["posts"] as $item) {

					//тип мероприятия
					$arrEvents = [];
					//отрасль
					$arrIndustrys = [];
					//города
					$arrCountries = [];

					$id = $item->ID;
					//все поля страницы
					$arrFeilds = get_fields($id);
					//сылка на сторонний ресурс
					$link = [];
					if (isset($arrFeilds["link"]) && !empty($arrFeilds["link"])) {
						$link = [
							"link" => $arrFeilds["link"],
							"text" => $this->clasVedLangTrans->getFieldValue($this->trPageId,	['buttons'],"detail")
						];
					}
					//Дата От и До - чисто заголовок (по date1 будет выборка на фронт)
					$date1 = "";
					if (isset($arrFeilds["date1"])) {
						$date1 = $arrFeilds["date1"];
					}
					$date2 = "";
					if (isset($arrFeilds["date2"])) {
						$date2 = $arrFeilds["date2"];
					}
					//мероприятия
					if (isset($arrFeilds["ved_event_type"]) && !empty($arrFeilds["ved_event_type"])) {

						$arrEvents = $this->getItemFilterParams(
							'ved_event_type',
							$this->filters,
							$arrFeilds["ved_event_type"],
							'id',
							'text',
							isset($this->listTitle["$title"])?$this->listTitle["$title"]["event"]:""
						);
					}
					unset($key);
					//отрасль
					if (isset($arrFeilds["ved_industry"]) && !empty($arrFeilds["ved_industry"])) {

						$arrIndustrys = $this->getMultiItemFilterParams(
							'ved_industry',
							$this->filters,
							$arrFeilds["ved_industry"],
							'id',
							'text',
							""
						);

					}
					unset($key);
					//Страна
					if (isset($arrFeilds["countries"]) && !empty($arrFeilds["countries"])) {


						$city = apply_filters( 'the_title', $arrFeilds["countries"][0]->post_title);


						$arrCountries = $this->getItemFilterParams(
							'countries',
							$this->filters,
							$city,
							'id',
							'text',
							isset($this->listTitle["$title"])?$this->listTitle["$title"]["country"]:""
						);

					}
					unset($key);

					if ($arrEvents)
						$result[] = $arrEvents;

					if ($arrCountries)
						$result[] = $arrCountries;

					if ($arrIndustrys)
						$result[] = $arrIndustrys;


					$customDate1=str_replace(".",",",$date1);
					$customDate1=$this->classExtra->getDateLang($customDate1,".",false);

					$customDate2=str_replace(".",",",$date2);
					$customDate2=$this->classExtra->getDateLang($customDate2,".",false);

					$arrResult[] = [
						"date" => $customDate1 . " – " . $customDate2,
						"color" => isset($arrFeilds["color"]) ? $arrFeilds["color"] : "#FFFFFF",
						"name" => $this->classExtra->convertHtmlInCharacters(apply_filters( 'the_title',
								$item->post_title )),
						"link" => $link,
						"properties" => $result,
						"sortdate"=> $date1
					];

					unset($arrFeilds, $result);
				}
			}

			if ($arrResult) {
				return $arrResult;
			}

			return false;
		}
	}

	/**
	 * Собираем на основе фильтра(оттуда нужны параметры  для фильтрации)
	 * маркеры фильтров мероприятий, по которым и будет работать фильтра на фронте
	 *
	 * Множественное
	 *
	 * @param $key
	 * @param $array
	 * @param $arrValues
	 * @param $columnId
	 * @param $columnId2
	 * @return array|false
	 */
	public function getMultiItemFilterParams($key, $array, $arrValues, $columnId, $columnId2,$title)
	{
		$arrResult = [];
		//ключ фильтра
		$searchkey = array_search($key, array_column($array, $columnId));
		//значение фильтра
		$arrValue = [];
		//имена значений фильтра
		$arrtext = [];
		foreach ($arrValues as $item) {
			//ключ
			if ($key2 = array_search($item, array_column($array[$searchkey]["options"], $columnId2))) {
				array_push($arrValue, $array[$searchkey]["options"][$key2]["value"]);
				array_push($arrtext, apply_filters( 'the_title', $array[$searchkey]["options"][$key2]["text"] ));
			}
		}


		//собираем сам массив для фильтра
		$arrResult = [
			"fieldName" => $array[$searchkey]["id"],
			"name" => null,
			"value" =>
				$arrValue
			,
			"text" =>

				$arrtext

		];

		unset($arrValue, $arrtext);

		if ($arrResult)
			return $arrResult;

		return false;

	}


	/**
	 * Собираем на основе фильтра(оттуда нужны параметры  для фильтрации)
	 * маркеры фильтров мероприятий, по которым и будет работать фильтра на фронте
	 *
	 * @param $key
	 * @param $array
	 * @param $value
	 * @param $columnId
	 * @param $columnId2
	 * @return array|false
	 */
	public function getItemFilterParams($key, $array, $value, $columnId, $columnId2,$title)
	{
		$arrResult = [];
		//ключ фильтра
		$searchkey = array_search($key, array_column($array, $columnId));
		//находим конечный массив
		if ($key2 = array_search($value, array_column($array[$searchkey]["options"], $columnId2))) {
			//собираем сам массив для фильтра
			$arrResult = [
				"fieldName" => $array[$searchkey]["id"],
				"name" => !empty($title)?$title:$array[$searchkey]["placeholder"],
				"value" => [
					$array[$searchkey]["options"][$key2]["value"]
				],
				"text" => [
					apply_filters( 'the_title', $array[$searchkey]["options"][$key2]["text"] )
				]
			];
		}

		if ($arrResult)
			return $arrResult;

		return false;

	}


	/**
	 * Проходим по всем дня месяца и собираем календарь с учетом того что ячеек 35, а дней до 31
	 *
	 * @param $year
	 * @param $month
	 * @return array|null
	 */
	public function getAllDaysMonth($year, $month)
	{
		$arrResult = [];
		//номер послденего дня месяца в неделе
		$lastWeekInFor = 0;

		for ($d = 1; $d <= 31; $d++) {
			$time = mktime(12, 0, 0, $month, $d, $year);
			if (date('m', $time) == $month) {

				if ($d == 1 && date("N", $time) != 1) {
					$arrResult = $this->addNeedDaysStart(date("N", $time), $year, $month);
				}
				if ($arrEvents = $this->getEvents($year, $month, $d,true,-1,"event")) {
					$arrResult[] = [
						"day" => date('d', $time),
						"dayName" => $this->getShortNameWeek(date("N", $time), true),
						"open" => false,
						"monthValue" => "value_" . strtolower($this->getNameMonth(date('m'))[0]),
						"events" => null,

						"name" => $d . " " . strtolower($this->getNameMonth(date('m'))[2]) . " " . $year . " г. " . strtolower
							($this->geFullNameWeek(date("N", $time))),
						"currentMonth" => true,
						'events' => $arrEvents
					];
				} else {


					$arrResult[] = [
						"day" => date('d', $time),
						"dayName" => $this->getShortNameWeek(date("N", $time), true),
						"open" => false,
						"monthValue" => "value_" . strtolower($this->getNameMonth(date('m'))[0]),
						"currentMonth" => true,
						"events" => null,
					];
				}
				$lastWeekInFor = date("N", $time);
			}
		}

		if (count($arrResult) != SELF::NUMBER_CELLS_CALENDAR && $lastWeekInFor > 0) {
			$result = $this->addNeedDaysEnd($lastWeekInFor, $year, $month);
			$arrResult += $result;
		}
		//сбрасываем ключи
		$arrResult = array_values($arrResult);


		return $arrResult;
	}


	/**
	 * Для сборки календаря добавляем недостающие дни в конец
	 *
	 * @param $dayInWeek
	 * @param $year
	 * @param $month
	 * @return array|void
	 */
	public function addNeedDaysEnd($dayInWeek, $year, $month)
	{
		//если последний день месяцы воскресенье ничего не делаем
		if ($dayInWeek != 7) {

			$arrResult = [];
			//дни недели
			$weekDays = $this->getShortNameWeek('', true);
			//если 12-ый месяц
			if ($month == 12) {
				$month = 1;
				$year++;
			} else {
				$month++;
			}
			//первый день месяца)) 01
			$dayMonth = date("1", strtotime($month));
			//обрезаем массив от дня
			$weekDaysCut = array_slice($weekDays, $dayInWeek, 7);

			$count = 1;

			foreach ($weekDaysCut as $key => $day) {

				if ($count != 1)
					$dayMonth++;


				if ($arrEvents = $this->getEvents($year, $month, $dayMonth)) {
					$arrResult[$count * 100] = [
						"day" => "0" . $dayMonth,
						"dayName" => $day,
						"open" => false,
						"monthValue" => "value_" . strtolower($this->getNameMonth($month)[0]),
						"name" => $dayMonth . " " . strtolower($this->getNameMonth($month)[2]) . " " . $year . " г. " .
							$this->geFullNameWeek($key + 1),
						"currentMonth" => false,
						'events' => $arrEvents
					];
				} else {
					$arrResult[$count * 100] = [
						"day" => "0" . $dayMonth,
						"dayName" => $day,
						"open" => false,
						"monthValue" => "value_" . strtolower($this->getNameMonth($month)[0]),
						"currentMonth" => false,
						"events" => null
					];
				}

				$count++;
			}

			return $arrResult;
		}

		return false;
	}

	/**
	 * Для сборки календаря добавляем в начало
	 *
	 * @param $dayInWeek
	 * @param $year
	 * @param $month
	 * @return array|false
	 */
	public function addNeedDaysStart($dayInWeek, $year, $month)
	{
		//если это начало недели
		if ($dayInWeek != 1) {

			$arrResult = [];

			$weekDays = $this->getShortNameWeek('', true);

			if ($month == 1) {
				$month = 12;
				$year = $year - 1;
			} else {
				$month--;
			}
			//последний день месяца
			$last = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			//обрезаем массив дней недели
			$weekDaysCut = array_slice($weekDays, 0, $dayInWeek - 1, true);
			//разворачиваем массив в сторону дней недели
			$weekDaysReversBack = array_reverse($weekDaysCut, true);

			$count = 1;

			foreach ($weekDaysReversBack as $key => $day) {

				if ($count != 1)
					$last--;

				if ($arrEvents = $this->getEvents($year, $month, $last)) {

					$days[] = [
						"day" => $last,
						"dayName" => $day,
						"open" => false,
						"monthValue" => "value_" . strtolower($this->getNameMonth($month)[0]),
						"name" => $last . " " . strtolower($this->getNameMonth($month)[2]) . " " . $year . " г. " . strtolower
							($this->geFullNameWeek($key)),
						"currentMonth" => false,
						'events' => $arrEvents
					];
				} else {
					$days[] = [
						"day" => $last,
						"dayName" => $day,
						"open" => false,
						"monthValue" => "value_" . strtolower($this->getNameMonth($month)[0]),
						"currentMonth" => false,
						"events" => null
					];
				}

				$count++;
			}
			//разворачиваем массив что бы начало было с понедельника
			$arrResult = array_reverse($days);

			return $arrResult;
		}

		return false;
	}

	/**
	 * дни недели короткое
	 *
	 * @param $key
	 * @return string|string[]
	 */
	public function getShortNameWeek($key = "", $flagShift = false)
	{
		$arrResult=$this->clasVedParametrs->shortNameWeek();

		if ($flagShift) {
			array_unshift($arrResult, "empty");
			unset($arrResult[0]);
		}


		if (!empty($key)) {
			return $arrResult[$key];
		}

		return $arrResult;
	}

	/**
	 * дни недели полное
	 *
	 * @param $key
	 * @return string|string[]
	 */
	public function geFullNameWeek($key = "")
	{

		$arrResult=$this->clasVedParametrs->fullNameWeek();

		array_unshift($arrResult, "empty");
		unset($arrResult[0]);

		if (!empty($key)) {
			return $arrResult[$key];
		}

		return $arrResult;
	}

	/**
	 * Месяцы года
	 *
	 * @param $key
	 * @return string[]|\string[][]
	 */
	public function getNameMonth($key = "", $code = "")
	{
		$arrResult=$this->clasVedParametrs->nameMonth();



		if (!empty($code)) {
			return array_search($code, array_column($arrResult, 0)) + 1;
		}


		if (!empty($key)) {
			return $arrResult[$key];
		}

		return $arrResult;
	}

	/**
	 * Проверка на высокосный год
	 *
	 * @param $year
	 * @return string
	 */
	public function isLeap($year)
	{
		return date("L", mktime(0,0,0, 7,7, $year));
	}


	/**
	 * Название списков из страницы
	 *
	 * @param $pageId
	 * @return array|false
	 */
	public function getPageField($pageId){
		//все поля страницы
		$arrFeilds = get_fields($pageId);

		$arrResult=[];

		if(isset($arrFeilds["title-fields"])){
			foreach ($arrFeilds["title-fields"] as $key =>$item){

				if($key=="item"){
					$arrResult["calendar"]=$item;
				}
				if($key=="item2"){
					$arrResult["event"]=$item;
				}
			}
		}

		if($arrResult)
			return $arrResult;

		return false;
	}

}
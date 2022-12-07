<?php
namespace Helper;

//wordpress
require_once($_SERVER["DOCUMENT_ROOT"] . '/wp-load.php');


class VedRestApi
{
	const route_namespace = 'vedroute/v1';

	private $classExtra;
	private $classSendForms;
	private $clasSearchFilter;
	private $classVedCalendar;
	private $calendarPageId;
	//id страницы настроек с переводами
	private $trPageId;
	//переводы
	private $clasVedLangTrans;
	//настройки форм
	private $classListForms;

	function __construct()
	{
		add_action(
			'rest_api_init',
			[$this, "register_routes"],
			10, 0
		);

		//Вспомогательный класс
		$this->classExtra = new \Helper\Extra();
		//формы
		$this->classSendForms = new \Helper\SendForm();
		//фильтр по поиску
		$this->clasSearchFilter = new \Helper\SearchFilter();
		//календарь
		$this->classVedCalendar = new \Helper\VedCalendar();

		$this->trPageId = \Helper\VedParametrs::LANGUDGE_TRANSLATE_PAGE_ID;

		$this->calendarPageId = \Helper\VedParametrs::CALENDAR_PAGE_ID;

		$this->clasVedLangTrans = new \Helper\VedLanguagesTranslate();

		//Вспомогательный класс
		$this->classListForms = new \Helper\ListForms();


	}


	public function register_routes()
	{

		register_rest_route(self::route_namespace,
			'/news',
			array(
				array(
					'methods' => \WP_REST_Server::ALLMETHODS,
					'callback' => array($this, 'getNews'),
				),

			)
		);
		register_rest_route(self::route_namespace,
			'/search/(?P<lang>[a-z0-9_\-]+)',
			array(
				array(
					'methods' => \WP_REST_Server::ALLMETHODS,
					'callback' => array($this, 'getSearch'),
				),

			)
		);


		register_rest_route(self::route_namespace,
			'/sendform/(?P<id>[a-z0-9_\-]+)/(?P<pageid>[\d]+)',
			array(
				array(
					'methods' => \WP_REST_Server::ALLMETHODS,
					'callback' => array($this, 'getResultForm'),
				),

			)
		);
		register_rest_route(self::route_namespace,
			'/tenders',
			array(
				array(
					'methods' => \WP_REST_Server::ALLMETHODS,
					'callback' => array($this, 'getTenders'),
				),

			)
		);

		register_rest_route(self::route_namespace,
			'/calendar/(?P<year>[\d]+)',
			array(
				array(
					'methods' => \WP_REST_Server::ALLMETHODS,
					'callback' => array($this, 'getCalendar'),
				),

			)
		);


		register_rest_route(self::route_namespace,
			'/successform/(?P<id>[a-z0-9_\-]+)',
			array(
				array(
					'methods' => \WP_REST_Server::ALLMETHODS,
					'callback' => array($this, 'successResultForm'),
				),

			)
		);

		register_rest_route(self::route_namespace,
			'/openpopupform/(?P<id>[a-z0-9_\-]+)/(?P<careerid>[\d]+)',
			array(
				array(
					'methods' => \WP_REST_Server::ALLMETHODS,
					'callback' => array($this, 'openPopupForm'),
				),

			)
		);

		register_rest_route(self::route_namespace,
				'/openPopupFormServices/(?P<id>[a-z0-9_\-]+)/(?P<pageid>[\d]+)',
				array(
						array(
								'methods' => \WP_REST_Server::ALLMETHODS,
								'callback' => array($this, 'openPopupFormServices'),
						),

				)
		);


	}

	/**
	 * Для страниц сервисов
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function openPopupFormServices(\WP_REST_Request $request)
	{
		$arrResult = [];
		//id формы
		$id = $request->get_param('id');

		//pageID
		$pageid = $request->get_param('pageid');

		//форма
		if ($result = $this->classSendForms->sendForm($id, $pageid, [])) {
			$arrResult = $result + $this->classExtra->getParamsAdditionalForms(0);
			$arrResult["recaptcha"] = $this->classExtra->getGoogleKey();


		}

		ob_start();


		?>
		<div class="modal-window">
			<button class="modal-window__close js-close-modal" type="button">
				<svg class="svg-icon modal-window__close-icon">
					<use href="/local/assets/dist/icons/sprite.svg#svg-icon-close-modal"></use>
				</svg>
			</button>
			<div class="modal-window__body">
				<v-form :data-source="<?php  echo htmlspecialchars(json_encode($arrResult)); ?>"></v-form>
			</div>
		</div>
		<?php
		$print = ob_get_contents();
		ob_end_clean();
		echo  $print;
	}


	/**
	 * Для форм на странице карьера
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function openPopupForm(\WP_REST_Request $request)
	{
		$arrResult = [];
		//id формы
		$id = $request->get_param('id');

		//careerid
		$careerid = $request->get_param('careerid');

		//Нужно достать название вакансии
		$vacancyName="";
		if($careerid>0){
			$vacancyName=get_the_title($careerid);
		}


        //форма
		if ($result = $this->classSendForms->sendForm($id, 0, [])) {
			$arrResult = $result + $this->classExtra->getParamsAdditionalForms(0);
			$arrResult["recaptcha"] = $this->classExtra->getGoogleKey();


			if(!empty($vacancyName)){
				$arrResult["fields"][0]["value"]=$vacancyName;
			}
		}

		ob_start();


		?>
		<div class="modal-window">
			<button class="modal-window__close js-close-modal" type="button">
				<svg class="svg-icon modal-window__close-icon">
					<use href="/local/assets/dist/icons/sprite.svg#svg-icon-close-modal"></use>
				</svg>
			</button>
			<div class="modal-window__body">
				<v-form :data-source="<?php  echo htmlspecialchars(json_encode($arrResult)); ?>"></v-form>
			</div>
		</div>
	<?php
		$print = ob_get_contents();
		ob_end_clean();
		echo  $print;
	}

	/**
	 * Ответ пользователю на успешную отправку форм (сам текст береться из настроек)
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function successResultForm(\WP_REST_Request $request)
	{

		//id формы
		$id = $request->get_param('id');

		//Заголовок
		$title = "Ваше обращение успешно отправлено";
		//Текст
		$content = "Ознакомьтесь с порядком рассмотрения обращений справа от формы заявки";
		//Текст кнопки
		$btnTitle = "Понятно";

		if (!empty($id)) {
			if ($arrTxtSettings = $this->classListForms->getFormSettingsInPage($id, "modal")) {
				$title = $arrTxtSettings["title"];
				$content = $arrTxtSettings["text"];
				$btnTitle = $arrTxtSettings["button-title"];
			}
		}


		echo <<<END

    <div class="modal-window">
            <button class="modal-window__close js-close-modal" type="button">
                <svg class="svg-icon modal-window__close-icon">
                    <use href="/local/assets/dist/icons/sprite.svg#svg-icon-close-modal"></use>
                </svg>
            </button>
            <div class="modal-window__body">
                <div class="success-block"><img class="success-block__icon" src="/local/assets/images/success.png"
                                                alt=""/>

                    <div class="h4 success-block__title">$title</div>
                    <div class="success-block__text"> $content
                    </div>
                    <div class="success-block__action">
                        <button class="btn btn--outline btn--sm js-close-modal" type="button">$btnTitle</button>
                    </div>
                </div>
            </div>
        </div>

END;

	}


	/**
	 * Формы
	 *
	 * @param \WP_REST_Request $request
	 * @return void|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function getResultForm(\WP_REST_Request $request)
	{
		$arrResult = [];

		//id страницы
		$pageId = $request->get_param('pageid');
		//id формы
		$id = $request->get_param('id');
		//grecaptcha
		$grecaptcha = $request->get_param('grecaptcha');
		//параметры с формы
		$arrRequest = $request->get_param('state');

		//	echo "<pre>";		print_r($_REQUEST);		echo "</pre>";

		//	echo "<pre>";		print_r($_POST);		echo "</pre>";


		$arrRequest["form"]["recaptcha"]["status"] = false;
		if (!empty($grecaptcha)) {
			$arrRequest["form"]["recaptcha"]["status"] = $grecaptcha;
		}
		if ($_FILES) {
			$arrRequest["form"]["files"] = $_FILES;
		}

		//echo "<pre>";		print_r($arrRequest);		echo "</pre>";

		if (!empty($id) && !empty($arrRequest)) {
			if (isset($arrRequest["action"]) && $arrRequest["action"] == "submit") {
				if ($result = $this->classSendForms->sendForm($id, $pageId, $arrRequest["form"])) {
					$dopArray = [];
					if ($pageId > 0) {
						$dopArray = $this->classExtra->getParamsAdditionalForms($pageId);
					}
					if ($dopArray) {
						return rest_ensure_response($result + $dopArray);
					} else {
						return rest_ensure_response($result);
					}

				}
			}

		}


		//  return rest_ensure_response($arrRequest["form"]);

	}


	/**
	 * Новости отдаем по фильтру список
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function getNews(\WP_REST_Request $request)
	{

		$pageParams = $request->get_param('page');

		if ($pageParams) {

			$postType = (isset($pageParams["post-type"]) && $pageParams["post-type"]) ? $pageParams["post-type"] : "news";
			//по умолчанию первая страница пагинации
			$paged = 1;
			//кол-во страниц при пагинации задается из настроек
			$limit = (isset($pageParams["limit"]) && $pageParams["limit"] > 0) ? $pageParams["limit"] : 30;
			//кол-во страниц со сдвигом, каждую новую страницу +limit
			$offset = (isset($pageParams["offset"]) && $pageParams["offset"] > 0) ? $pageParams["offset"] : 0;
			//какую страницу показывать
			if ($offset > 0 && $limit > 0) {
				//+1 потому что vue считает от 0, а для wordpress (WPQuery) пагинация начинается от 1
				$paged = floor($offset / $limit) + 1;
			}
			//фильтр по категориям
			//$_POST["filter"]["CATEGORIES"]["value"] - это slug рубрики (категории)
			$slug = "";
			//фильтр по дате
			$dateLabel = "";
			$filter = $request->get_param('filter');
			if ($filter) {
				if (isset($filter) && !empty($filter["CATEGORIES"]["value"])) {
					$slug = $filter["CATEGORIES"]["value"];
				}

				if (isset($filter) && !empty($filter["PERIOD"]["value"])) {
					$dateLabel = $filter["PERIOD"]["value"];
				}
			}

			if ($arrResult = $this->classExtra->responsePosts(
				$postType,
				$paged,
				$limit,
				$offset,
				$slug,
				$dateLabel,
				$postType == "news" ? true : false)
			) {
				if (!empty($arrResult["items"])) {
					return rest_ensure_response($arrResult);

				}
			}
		}

		return rest_ensure_response(false);
	}

	/**
	 * Отдаем по фильтру
	 *
	 * @param \WP_REST_Request $request
	 * @return void|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function getSearch(\WP_REST_Request $request)
	{
		$queryPost = $request->get_param('query');

		//Язык
		$lang = $request->get_param('lang');

		$prefLink = "";
		//языковой url
		if ($lang != "ru") {
			$prefLink = "/" . $lang;
		}

		$arrResult = [];

		if ($queryPost) {

			if (strlen($queryPost) > \Helper\VedParametrs::COUNT_SYMBOLS_SEARCH) {
				$this->clasSearchFilter->addSearchRequest($queryPost, $lang);
			}

			$arrResult["link"] = $prefLink . "/search?query=" . $queryPost;
			$arrResult['placeholder'] = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['search', "pop-up"], "placeholder");
			$arrResult['alriaLabel'] = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['search', "pop-up"], "alriaLabel");
			$arrResult['emptyText'] = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['search', "pop-up"], "emptyText");
			$arrResult['linkText'] = $this->clasVedLangTrans->getFieldValue($this->trPageId,
				['search', "pop-up"], "linkText");

			$arrResult['remote'] = $prefLink . "/wp-json/vedroute/v1/search/" . $lang;

			$args = array(
				"post_type" => "any", // Тип записи: post, page, кастомный тип записи
				"post_status" => "publish",
				"order" => "DESC",
				"orderby" => "date",
				"posts_per_page" => -1
			);

			$input_text = strip_tags($queryPost);
			$input_text = htmlspecialchars($input_text);

			$args['s'] = $input_text;
			$arrResult["query"] = $input_text;

			$items = [];
			//поиск по типу записей
			if ($objResult = $this->classExtra->customWPQueryWithTotal($args)) {

				if ($objResult["total"] > 0) {

					if ($result = $this->clasSearchFilter->filterPostMeta($args['s'])) {

						$arrId=[];
						foreach ($result as $item){
							$arrId[]=$item->post_id;
						}

						$args = array(
								"post_type" => "any", // Тип записи: post, page, кастомный тип записи
								"post_status" => "publish",
								"order" => "DESC",
								"orderby" => "date",
								"posts_per_page" => -1,
								"post__in"=>$arrId
						);
						if ($objResult2 = $this->classExtra->customWPQueryWithTotal($args)) {
							$objResult["posts"] = (object) array_merge($objResult["posts"], $objResult2["posts"]);
						}
					}


					if ($result = $this->clasSearchFilter->filter($objResult["posts"])) {
						$objResult["posts"] = $this->classExtra->object_unique_key($result, "ID");
					} else {
						$objResult["posts"] = $items;
					}



					if ($resultItems = $this->classExtra->searchResponse($objResult["posts"])) {
						$items = $resultItems;
					}
				}
			}



			$arrResult["suggestionsCount"] = isset($items) ? count($items) : 0;
			$arrResult["suggestions"] = isset($items) ? $items : "";


			return rest_ensure_response($arrResult);
		}
	}

	/**
	 * Тендеры
	 *
	 * @param \WP_REST_Request $request
	 * @return false|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function getTenders(\WP_REST_Request $request)
	{

		$arrResult = [];
		$pageParams = $request->get_param('page');
		//по умолчанию первая страница пагинации
		$paged = 0;
		//кол-во страниц при пагинации задается из настроек
		$limit = (isset($pageParams["limit"]) && $pageParams["limit"] > 0) ? $pageParams["limit"] : 4;
		//кол-во страниц со сдвигом, каждую новую страницу +limit
		$offset = (isset($pageParams["offset"]) && $pageParams["offset"] > 0) ? $pageParams["offset"] : 0;

		//какую страницу показывать
		if ($offset > 0 && $limit > 0) {
			//+1 потому что vue считает от 0, а для wordpress (WPQuery) пагинация начинается от 1
			$paged = floor($offset / $limit) + 1;
		}
		//фильтр
		$arrFilters = [];
		$filter = $request->get_param('filter');

		if (isset($filter) && $filter) {
			//страны
			if (!empty($filter["FIELD_COUNTRY"]["value"])) {
				$arrFilters["FIELD_COUNTRY"] = $filter["FIELD_COUNTRY"]["value"];
			}
			//отрасли
			if (!empty($filter["FIELD_INDUSTRY"]["value"])) {
				$arrFilters["FIELD_INDUSTRY"] = $filter["FIELD_INDUSTRY"]["value"];
			}
			//дата
			if (!empty($filter["EXPIRATION_DATE"]["value"][0])) {
				$arrFilters["EXPIRATION_DATE"] = $filter["EXPIRATION_DATE"]["value"][0];
			}
			//поиск
			if (!empty($filter["FIELD_SEARCH"]["value"])) {
				$arrFilters["FIELD_SEARCH"] = $filter["FIELD_SEARCH"]["value"];
			}
		}

		if ($arrResult = $this->classExtra->responseTenders("tenders", $paged, $limit, $offset, $arrFilters)) {
			return rest_ensure_response($arrResult);
		}

		return false;
	}

	/**
	 * Деловой Каледарь
	 *
	 * @param \WP_REST_Request $request
	 * @return false|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function getCalendar(\WP_REST_Request $request)
	{

		$arrResult = [];

		$title = get_the_title($this->calendarPageId);
		$title = apply_filters('the_title', $title);


		//символьный код месяца
		$month = $request->get_param('month');
		//действие куда мотать
		$action = $request->get_param('action');
		//год
		$year = $request->get_param('year');

		if (!empty($action) && !empty($month)) {
			//достаем название месяца
			$pieces = explode("value_", $month);
			if (count($pieces) > 1) {
				$key = $this->classVedCalendar->getNameMonth("", $pieces[1]);

				if ($action == "next") {
					if ($key == 12) {
						$key = 1;
						$year++;
					} else {
						$key++;
					}
				} else {
					if ($key == 1) {
						$key = 12;
						$year--;
					} else {
						$key--;
					}
				}

				if ($arrResult = $this->classVedCalendar->getNewCalendar($title, $year, $key, $action)) {
					return rest_ensure_response($arrResult);
				}
			}
		}
		return false;
	}
}
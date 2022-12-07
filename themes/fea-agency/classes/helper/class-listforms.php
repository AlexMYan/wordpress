<?php

namespace Helper;

class ListForms
{
	private $property;
	//переводы
	private $clasVedLangTrans;
	//id страницы настроек с переводами
	private $formPageId;
	//Параметры
	private $clasVedParametrs;


	/**
	 * construct
	 */
	public function __construct()
	{
		$this->property = 'ListForms';


		$this->clasVedLangTrans = new \Helper\VedLanguagesTranslate();

		$this->formPageId = \Helper\VedParametrs::FORMS_PAGE_ID;

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
	 * Достаем нужные меню
	 *
	 * @param $id
	 * @return string[]|\string[][]
	 */
	public function getForms($id = "")
	{
		$arrResult = [];

		$arrResult = $this->fillParamsForm();


		if ($arrResult && !empty($id)) {

			foreach ($arrResult as $key => $item) {

				if ($key == $id) {

					return [$key => $item];
				}


				unset($arrACFKey);

			}
		}


		return $arrResult;

	}


	/**
	 * заполняем поля форм из настроек
	 *
	 * @return array[]|\string[][]
	 */
	function fillParamsForm()
	{

		$arrResult = [];

		if ($arrResult = $this->vedListForms()) {

			$count = 0;
			foreach ($arrResult as $key => &$item) {


				if ($count == 0) {
					if (isset($_REQUEST["dev"]) && $_REQUEST["dev"] == "Y") {

						//echo "<pre>";						print_r($item);						echo "</pre>";
					}
				}

				if ($arrACFKey = $this->arrFormACFKey($key)) {

					if ($arrACFFields = $this->getOptionPage($this->formPageId, $arrACFKey)) {

						if (isset($arrACFFields["value"])) {


							$arr = $arrACFFields["value"];


							$item["acf-key"] = $arrACFKey;


							if (!empty($arr["title"])) {
								$item["title"] = $arr["title"];
							}

							if (!empty($arr["title2"])) {
								$item["title2"] = $arr["title2"];
							}

							if (strlen($arr["subtitle"]) > 0) {

								$item["subtitle"] = $arr["subtitle"];
							}


							if (!empty($arr["title_menu"])) {
								$item["title_menu"] = $arr["title_menu"];
							}

							if (!empty($arr["description"])) {
								$item["description"] = $arr["description"];
							}

							if (!empty($arr["title-from"])) {
								$item["title-from"] = $arr["title-from"];
							}

							if (!empty($arr["success"])) {
								$item["success"] = $arr["success"];
							}
							$str = "";
							$arrFormats = [];

							if (isset($arr["fields-form"]["formats"]["format-select"]) && !empty($arr["fields-form"]["formats"]["format-select"])) {
								$arrFormats = $arr["fields-form"]["formats"]["format-select"];

								$numItems = count($arrFormats);
								$i = 0;
								foreach ($arrFormats as $format) {
									if (++$i === $numItems) {
										$str .= "-".$format.".";
									} else {
										$str .= "-".$format . ", ";
									}

								}

								//unset($str,$arrFormats);
							}


							if (isset($arr["fields-form"]) && $arr["fields-form"] && $item["fields"]) {


								foreach ($arr["fields-form"] as $k => $value) {
									$keySearch = array_search($k, array_column($item["fields"], 'id'));

									if ($keySearch === false) {
									} else {

										if ($k == "files" && !empty($str)) {

											$item["fields"][$keySearch]["allowedExtensions"] = $arrFormats;
											$item["fields"][$keySearch]["allowedExtensions_txt"] = $str;

										}
										$item["fields"][$keySearch]["label"] = $value;
									}

								}
							}


						}
					}
				}

				$count++;

				unset($str, $arrFormats);
			}
		}
		/*
				echo "<pre>";
				print_r($arrResult);
				echo "</pre>";*/

		return $arrResult;
	}

	/**
	 * Поля формы с учетеом настроек в админке
	 *
	 * @param $keyForm
	 * @param $keyField
	 * @return false|mixed
	 */
	public function getFormSettingsInPage($keyForm, $keyField)
	{
		$arrResult = [];

		if ($arrResult = $this->vedListForms()) {

			foreach ($arrResult as $key => $item) {

				if ($keyForm == $key) {

					if ($arrACFKey = $this->arrFormACFKey($key)) {
						if ($arrACFFields = $this->getOptionPage($this->formPageId, $arrACFKey)) {
							if (isset($arrACFFields["value"])) {

								$arr = $arrACFFields["value"];

								if (array_key_exists($keyField, $arr)) {
									return $arr[$keyField];
								}
							}
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Поля из настроек плагина АСF записис
	 *
	 * @param $pageId
	 * @param $key
	 * @return array|false
	 */
	public function getOptionPage($pageId, $key)
	{
		return get_field_object($key, $pageId);
	}

	/**
	 * Список текстовых полей (ни на что не влияют) сделанных через ACF
	 *
	 * @param $key
	 * @return false|string|string[]
	 */
	private function arrFormACFKey($key = "")
	{
		$arrResult = [
			"ved_forms_in_main" => "field_634948f2efaf8",
			"ved_forms_faq" => "field_634ae52094a89",
			"ved_forms_appeal" => "field_634cf2e4e4a2b",
			"ved_forms_manual" => "field_634cf57a03a25",
			"ved_forms_tenders" => "field_634cf83f42fc9",
			"ved_forms_services" => "field_634cf99a7403e",
			"ved_forms_career_empty" => "field_634d00eb00105",
			"ved_forms_career" => "field_634d01e8cdd56",
			"ved_subscription" => "field_635cdb085ef87"

		];

		if (!empty($key)) {
			if (isset($arrResult[$key])) {
				return $arrResult[$key];
			}

			return false;
		}

		return $arrResult;
	}

	/**
	 * Список форм
	 *
	 * @return \string[][]
	 */
	private function vedListForms()
	{

		//язык из плагина
		$lang = \WPGlobus::Config()->language;

		$prefLink = "";

		//языковой url
		if ($lang != "ru") {
			$prefLink = "/" . $lang;
		}

		$textNote1=$this->clasVedLangTrans->getFieldValue($this->formPageId, ['total-settings'], "textNote1");
		$textNote2=$this->clasVedLangTrans->getFieldValue($this->formPageId, ['total-settings'], "textNote2");
		$suffixes1=$this->clasVedLangTrans->getFieldValue($this->formPageId, ['total-settings'], "suffixes-1");
		$suffixes2=$this->clasVedLangTrans->getFieldValue($this->formPageId, ['total-settings'], "suffixes-2");


		return [
			"ved_forms_in_main" => [
				"title" => "",
				"title_menu" => "",
				"description" => "",
				"post_type" => "ved_forms_posts",
				"email" => "",
				"title2" => "",
				"subtitle" => "",
				"section_id" => "ved_forms_section_id",
				//	"modal" => "/local/assets/dist/markup/modal-response-success.html",
				//"modal" => "/modal/success.php?formID=ved_forms_in_main",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_forms_in_main/",
				"success" => "",
				"show_in_menu" => true,
				"groups" => [
					"id" => "ved_forms_in_main"
				],
				"fields" => [
					[
						"groupId" => "ved_forms_in_main",
						"id" => "name",
						"name" => "name",
						"type" => "text",
						"inputmode" => "text",
						"label" => "Имя и фамилия",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_in_main",
						"id" => "phone",
						"name" => "phone",
						"type" => "tel",
						"inputmode" => "tel",
						"label" => "Номер телефона",
						"placeholder" => "+375 (__) ___-__-__",
						"value" => "",
						"required" => true,
						"masks" => [
							[
								"id" => "bel",
								"name" => "Беларусь",
								"code" => "+375",
								"mask" => "{+375} (00) 000-00-00",
								"placeholder" => "+375 (__) ___-__-__"
							],
							[
								"id" => "kaz",
								"name" => "Казахстан",
								"code" => "+997",
								"mask" => "{+997} (000) 000-00-00",
								"placeholder" => "+997 (___) ___-__-__"
							]
						],
						"selectField" => [
							"type" => "select",
							"label" => null,
							"value" => "bel",
							"placeholder" => "Страна",
							"options" => [
								[
									"value" => "bel",
									"text" => "Беларусь +375"
								],
								[
									"value" => "kaz",
									"text" => "Казахстан +997"
								]
							]
						],
					],
					[
						"groupId" => "ved_forms_in_main",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
				]
			],


			"ved_subscription" => [
				"title" => "",
				"title_menu" => "",
				"description" => "",
				"post_type" => "ved_subscription",
				"email" => "",
				"title2" => "",
				"subtitle" => "",
				"section_id" => "ved_forms_section_id",
				//	"modal" => "/modal/success.php?formID=ved_subscription",
				"modal2" => "/local/assets/dist/markup/modal-subscription-form-success2.html",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_subscription/",
				"success" => "",
				"show_in_menu" => false,
				"groups" => [
					"id" => "ved_subscription"
				],
				"fields" => [
					[
						"groupId" => "ved_subscription",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
				]
			],


			"ved_forms_tenders" => [
				"title" => "",
				"title_menu" => "",
				"description" => "",
				"post_type" => "ved_forms_posts",
				"email" => "",
				"title2" => "",
				"subtitle" => "",
				"section_id" => "ved_forms_section_id",
				//	"modal" => "/modal/success.php?formID=ved_forms_tenders",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_forms_tenders/",
				"success" => "",
				"show_in_menu" => true,
				"groups" => [
					"id" => "ved_forms_tenders"
				],
				"fields" => [
					[
						"groupId" => "ved_forms_tenders",
						"id" => "name",
						"name" => "name",
						"type" => "text",
						"inputmode" => "text",
						"label" => "Имя и фамилия",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_tenders",
						"id" => "phone",
						"name" => "phone",
						"type" => "tel",
						"inputmode" => "tel",
						"label" => "Номер телефона",
						"placeholder" => "+375 (__) ___-__-__",
						"value" => "",
						"required" => true,
						"masks" => [
							[
								"id" => "bel",
								"name" => "Беларусь",
								"code" => "+375",
								"mask" => "{+375} (00) 000-00-00",
								"placeholder" => "+375 (__) ___-__-__"
							],
							[
								"id" => "kaz",
								"name" => "Казахстан",
								"code" => "+997",
								"mask" => "{+997} (000) 000-00-00",
								"placeholder" => "+997 (___) ___-__-__"
							]
						],
						"selectField" => [
							"type" => "select",
							"label" => null,
							"value" => "bel",
							"placeholder" => "Страна",
							"options" => [
								[
									"value" => "bel",
									"text" => "Беларусь +375"
								],
								[
									"value" => "kaz",
									"text" => "Казахстан +997"
								]
							]
						],
					],
					[
						"groupId" => "ved_forms_tenders",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"id" => "textarea",
						"type" => "textarea",
						"name" => "textarea",
						"inputmode" => "text",
						"label" => "Текст обращения",
						"placeholder" => "",
						"value" => "",
						"required" => true,
						"maxlength"=> 3000,
						"textNote1" => $textNote1,
						"textNote2" => [
							"root" => $textNote2,
							"suffixes" => ["", $suffixes1, $suffixes2]
						]
					],
				]
			],
			"ved_forms_faq" => [
				"title" => "",
				"title2" => "",
				"title_menu" => "",
				"subtitle" => "",
				"post_type" => "ved_forms_posts",
				"description" => "",
				"email" => "",
				"section_id" => "ved_forms_section_id",
				//"modal" => "/modal/success.php?formID=ved_forms_faq",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_forms_faq/",
				"success" => "",
				"show_in_menu" => true,
				"groups" => [
					"id" => "ved_forms_faq"
				],
				"fields" => [
					[
						"groupId" => "ved_forms_faq",
						"id" => "name",
						"name" => "name",
						"type" => "text",
						"inputmode" => "text",
						"label" => "Имя и фамилия",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_faq",
						"id" => "phone",
						"name" => "phone",
						"type" => "tel",
						"inputmode" => "tel",
						"label" => "Номер телефона",
						"placeholder" => "+375 (__) ___-__-__",
						"value" => "",
						"required" => true,
						"masks" => [
							[
								"id" => "bel",
								"name" => "Беларусь",
								"code" => "+375",
								"mask" => "{+375} (00) 000-00-00",
								"placeholder" => "+375 (__) ___-__-__"
							],
							[
								"id" => "kaz",
								"name" => "Казахстан",
								"code" => "+997",
								"mask" => "{+997} (000) 000-00-00",
								"placeholder" => "+997 (___) ___-__-__"
							]
						],
						"selectField" => [
							"type" => "select",
							"label" => null,
							"value" => "bel",
							"placeholder" => "Страна",
							"options" => [
								[
									"value" => "bel",
									"text" => "Беларусь +375"
								],
								[
									"value" => "kaz",
									"text" => "Казахстан +997"
								]
							]
						],
					],
					[
						"groupId" => "ved_forms_faq",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"id" => "textarea",
						"type" => "textarea",
						"name" => "textarea",
						"inputmode" => "text",
						"label" => "Текст обращения",
						"placeholder" => "",
						"value" => "",
						"required" => true,
						"maxlength"=> 3000,
						"textNote1" => $textNote1,
						"textNote2" => [
							"root" => $textNote2,
							"suffixes" => ["", $suffixes1, $suffixes2]
						]
					],
				]
			],
			"ved_forms_manual" => [
				"title" => "",
				"title2" => "",
				"title_menu" => "",
				"description" => "",
				"subtitle" => "",
				"post_type" => "ved_forms_posts",
				"email" => "",
				"section_id" => "ved_forms_section_id",
				//"modal" => "/modal/success.php?formID=ved_forms_manual",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_forms_manual/",
				"success" => "",
				"show_in_menu" => true,
				"groups" => [
					"id" => "ved_forms_manual"
				],
				"fields" => [
					[
						"groupId" => "ved_forms_manual",
						"id" => "name",
						"name" => "name",
						"type" => "text",
						"inputmode" => "text",
						"label" => "Имя и фамилия",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_manual",
						"id" => "phone",
						"name" => "phone",
						"type" => "tel",
						"inputmode" => "tel",
						"label" => "Номер телефона",
						"placeholder" => "+375 (__) ___-__-__",
						"value" => "",
						"required" => true,
						"masks" => [
							[
								"id" => "bel",
								"name" => "Беларусь",
								"code" => "+375",
								"mask" => "{+375} (00) 000-00-00",
								"placeholder" => "+375 (__) ___-__-__"
							],
							[
								"id" => "kaz",
								"name" => "Казахстан",
								"code" => "+997",
								"mask" => "{+997} (000) 000-00-00",
								"placeholder" => "+997 (___) ___-__-__"
							]
						],
						"selectField" => [
							"type" => "select",
							"label" => null,
							"value" => "bel",
							"placeholder" => "Страна",
							"options" => [
								[
									"value" => "bel",
									"text" => "Беларусь +375"
								],
								[
									"value" => "kaz",
									"text" => "Казахстан +997"
								]
							]
						],
					],
					[
						"groupId" => "ved_forms_manual",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_manual",
						"id" => "textarea",
						"type" => "textarea",
						"name" => "textarea",
						"inputmode" => "text",
						"label" => "Текст обращения",
						"placeholder" => "",
						"value" => "",
						"required" => true,
						"maxlength"=> 3000,
						"textNote1" => $textNote1,
						"textNote2" => [
							"root" => $textNote2,
							"suffixes" => ["", $suffixes1, $suffixes2]
						]
					],
				]
			],


			"ved_forms_services" => [
				"title" => "",
				"title2" => "",
				"title_menu" => "",
				"subtitle" => "",
				"post_type" => "ved_forms_posts",
				"description" => "",
				"email" => "",
				"section_id" => "ved_forms_section_id",
				//"modal" => "/modal/success.php?formID=ved_forms_services",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_forms_services/",
				"success" => "",
				"show_in_menu" => true,
				"groups" => [
					"id" => "ved_forms_services"
				],
				"fields" => [
					[
						"groupId" => "ved_forms_services",
						"id" => "name",
						"name" => "name",
						"type" => "text",
						"inputmode" => "text",
						"label" => "Имя",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_services",
						"id" => "surname",
						"name" => "surname",
						"type" => "text",
						"label" => "Фамилия",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_services",
						"id" => "patronymic",
						"name" => "patronymic",
						"type" => "text",
						"label" => "Отчество",
						"placeholder" => "",
						"value" => "",
						"required" => false
					],
					[
						"groupId" => "ved_forms_services",
						"id" => "company",
						"name" => "company",
						"type" => "text",
						"label" => "Наименование предприятия",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_services",
						"id" => "position",
						"name" => "position",
						"type" => "text",
						"label" => "Должность",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_services",
						"id" => "city",
						"name" => "city",
						"type" => "text",
						"label" => "Город",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[


						"groupId" => "ved_forms_services",
						"id" => "phone",
						"name" => "phone",
						"type" => "tel",
						"inputmode" => "tel",
						"label" => "Мобильный телефон",
						"placeholder" => "+375 (__) ___-__-__",
						"value" => "",
						"required" => true,
						"masks" => [
							[
								"id" => "bel",
								"name" => "Беларусь",
								"code" => "+375",
								"mask" => "{+375} (00) 000-00-00",
								"placeholder" => "+375 (__) ___-__-__"
							],
							[
								"id" => "kaz",
								"name" => "Казахстан",
								"code" => "+997",
								"mask" => "{+997} (000) 000-00-00",
								"placeholder" => "+997 (___) ___-__-__"
							]
						],
						"selectField" => [
							"type" => "select",
							"label" => null,
							"value" => "bel",
							"placeholder" => "Страна",
							"options" => [
								[
									"value" => "bel",
									"text" => "Беларусь +375"
								],
								[
									"value" => "kaz",
									"text" => "Казахстан +997"
								]
							]
						],
					],
					[

						"groupId" => "ved_forms_services",
						"id" => "workphone",
						"name" => "phone",
						"type" => "tel",
						"inputmode" => "tel",
						"label" => "Рабочий телефон",
						"placeholder" => "+375 (__) ___-__-__",
						"value" => "",
						"required" => true,
						"masks" => [
							[
								"id" => "bel",
								"name" => "Беларусь",
								"code" => "+375",
								"mask" => "{+375} (00) 000-00-00",
								"placeholder" => "+375 (__) ___-__-__"
							],
							[
								"id" => "kaz",
								"name" => "Казахстан",
								"code" => "+997",
								"mask" => "{+997} (000) 000-00-00",
								"placeholder" => "+997 (___) ___-__-__"
							]
						],
						"selectField" => [
							"type" => "select",
							"label" => null,
							"value" => "bel",
							"placeholder" => "Страна",
							"options" => [
								[
									"value" => "bel",
									"text" => "Беларусь +375"
								],
								[
									"value" => "kaz",
									"text" => "Казахстан +997"
								]
							]
						],
					],
					[
						"groupId" => "ved_forms_services",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
				]
			],

			"ved_forms_career" => [
				"title" => "",
				"title_menu" => "",
				"description" => "",
				"post_type" => "ved_forms_posts",
				"email" => "",
				"title2" => "",
				"subtitle" => "",
				"section_id" => "ved_forms_section_id",
				//"modal" => "/modal/success.php?formID=ved_forms_career",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_forms_career/",
				"success" => "",
				"show_in_menu" => true,
				"groups" => [
					"id" => "ved_forms_career"
				],
				"fields" => [
					[
						"groupId" => "ved_forms_career",
						"id" => "vacancy",
						"name" => "vacancy",
						"type" => "text",
						"label" => "Вакансия",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_career",
						"id" => "fio",
						"name" => "fio",
						"type" => "text",
						"label" => "ФИО",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_career",
						"id" => "city",
						"name" => "city",
						"type" => "text",
						"label" => "Город",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[


						"groupId" => "ved_forms_career",
						"id" => "phone",
						"name" => "phone",
						"type" => "tel",
						"inputmode" => "tel",
						"label" => "Телефон",
						"placeholder" => "+375 (__) ___-__-__",
						"value" => "",
						"required" => true,
						"masks" => [
							[
								"id" => "bel",
								"name" => "Беларусь",
								"code" => "+375",
								"mask" => "{+375} (00) 000-00-00",
								"placeholder" => "+375 (__) ___-__-__"
							],
							[
								"id" => "kaz",
								"name" => "Казахстан",
								"code" => "+997",
								"mask" => "{+997} (000) 000-00-00",
								"placeholder" => "+997 (___) ___-__-__"
							]
						],
						"selectField" => [
							"type" => "select",
							"label" => null,
							"value" => "bel",
							"placeholder" => "Страна",
							"options" => [
								[
									"value" => "bel",
									"text" => "Беларусь +375"
								],
								[
									"value" => "kaz",
									"text" => "Казахстан +997"
								]
							]
						],
					],
					[
						"groupId" => "ved_forms_career",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_career",
						"id" => "textarea",
						"name" => "textarea",
						"type" => "textarea",
						"inputmode" => "text",
						"label" => "Комментарий",
						"placeholder" => "",
						"value" => "",
						"required" => true,
						"maxlength"=> 3000,
						"textNote1" => $textNote1,
						"textNote2" => [
							"root" => $textNote2,
							"suffixes" => ["", $suffixes1, $suffixes2]
						]
					],
					[
						"groupId" => "ved_forms_career",
						"id" => "files",
						"name" => "files",
						"type" => "file",
						"label" => "Прикрепить файл",
						"note" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_8',
							'fields-form'], "note"),
						"maxFileSize" => 5242880,
						"allowedExtensions" => [
							"doc" => 'application/msword',
							"docx" => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
							"rtf" => 'application/rtf',
							"txt" => "text/plain",
							"pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
							"xml" => "image/svg+xml",
							"rar" => "application/rar",
							"zip" => "application/zip",
							"odt" => "application/vnd.oasis.opendocument.text",
							"pdf" => "application/pdf",
							"jpg|jpeg|jpe" => "image/jpeg",
							"png" => "image/png",
							"svg" => "image/svg+xml"
						],
						"placeholder" => "",
						"value" => "",
						"required" => false,
						"errorMessages" => [
							"fileSizeError" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_8', 'fields-form'], "fileSizeError"),
							"fileExtensionError" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_8', 'fields-form'], "fileExtensionError")
						]
					],

				]
			],
			"ved_forms_career_empty" => [
				"title" => "",
				"title_menu" => "",
				"description" => "",
				"post_type" => "ved_forms_posts",
				"email" => "",
				"title2" => "",
				"subtitle" => "",
				"section_id" => "ved_forms_section_id",
				//	"modal" => "/modal/success.php?formID=ved_forms_career_empty",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_forms_career_empty/",
				"success" => "",
				"show_in_menu" => true,
				"groups" => [
					"id" => "ved_forms_career_empty"
				],
				"fields" => [

					[
						"groupId" => "ved_forms_career_empty",
						"id" => "fio",
						"name" => "fio",
						"type" => "text",
						"label" => "ФИО",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_career_empty",
						"id" => "city",
						"name" => "city",
						"type" => "text",
						"label" => "Город",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_career_empty",
						"id" => "phone",
						"name" => "phone",
						"type" => "tel",
						"inputmode" => "tel",
						"label" => "Телефон",
						"placeholder" => "+375 (__) ___-__-__",
						"value" => "",
						"required" => true,
						"masks" => [
							[
								"id" => "bel",
								"name" => "Беларусь",
								"code" => "+375",
								"mask" => "{+375} (00) 000-00-00",
								"placeholder" => "+375 (__) ___-__-__"
							],
							[
								"id" => "kaz",
								"name" => "Казахстан",
								"code" => "+997",
								"mask" => "{+997} (000) 000-00-00",
								"placeholder" => "+997 (___) ___-__-__"
							]
						],
						"selectField" => [
							"type" => "select",
							"label" => null,
							"value" => "bel",
							"placeholder" => "Страна",
							"options" => [
								[
									"value" => "bel",
									"text" => "Беларусь +375"
								],
								[
									"value" => "kaz",
									"text" => "Казахстан +997"
								]
							]
						],
					],
					[
						"groupId" => "ved_forms_career_empty",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_career_empty",
						"id" => "textarea",
						"name" => "textarea",
						"type" => "textarea",
						"inputmode" => "text",
						"label" => "Комментарий",
						"placeholder" => "",
						"value" => "",
						"required" => true,
						"maxlength"=> 3000,
						"textNote1" => $textNote1,
						"textNote2" => [
							"root" => $textNote2,
							"suffixes" => ["", $suffixes1, $suffixes2]
						]
					],
					[
						"groupId" => "ved_forms_career_empty",
						"id" => "files",
						"name" => "files",
						"type" => "file",
						"label" => "Прикрепить файл",
						"note" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_7',
							'fields-form'], "note"),
						"maxFileSize" => 5242880,
						"allowedExtensions" => [
							"doc" => 'application/msword',
							"docx" => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
							"pdf" => "application/pdf",
						],
						"placeholder" => "",
						"value" => "",
						"required" => false,
						"errorMessages" => [
							"fileSizeError" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_7', 'fields-form'], "fileSizeError"),
							"fileExtensionError" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_7', 'fields-form'], "fileExtensionError")
						]
					],

				]
			],

			"ved_forms_appeal" => [
				"title" => "",
				"title_menu" => "",
				"description" => "",
				"post_type" => "ved_forms_posts",
				"email" => "",
				"title2" => "",
				"subtitle" => "",
				"section_id" => "ved_forms_section_id",
				//"modal" => "/modal/success.php?formID=ved_forms_appeal",
				"modal" => $prefLink . "/wp-json/vedroute/v1/successform/ved_forms_appeal/",
				"success" => "",
				"show_in_menu" => true,
				"groups" => [
					"id" => "ved_forms_appeal"
				],
				"fields" => [
					[
						"groupId" => "ved_forms_appeal",
						"id" => "name",
						"name" => "name",
						"type" => "text",
						"label" => "Адресат обращения (кому направляется)",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_appeal",
						"id" => "fio",
						"name" => "fio",
						"type" => "text",
						"label" => "ФИО",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_appeal",
						"id" => "adress",
						"name" => "adress",
						"type" => "text",
						"label" => "Почтовый адрес",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_appeal",
						"id" => "email",
						"name" => "email",
						"type" => "email",
						"label" => "Электронная почта",
						"placeholder" => "",
						"value" => "",
						"required" => true
					],
					[
						"groupId" => "ved_forms_appeal",
						"id" => "textarea",
						"name" => "textarea",
						"type" => "textarea",
						"inputmode" => "text",
						"label" => "Текст обращения",
						"placeholder" => "",
						"value" => "",
						"required" => true,
						"maxlength"=> 3000,
						"textNote1" => $textNote1,
						"textNote2" => [
							"root" => $textNote2,
							"suffixes" => ["", $suffixes1, $suffixes2]
						]
					],
					[
						"groupId" => "ved_forms_appeal",
						"id" => "files",
						"name" => "files",
						"type" => "file",
						"label" => "Прикрепить файл",
						"note" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_3',
							'fields-form'], "note"),
						"maxFileSize" => 5242880,
						"allowedExtensions" => [
							"doc" => 'application/msword',
							"docx" => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
							"rtf" => 'application/rtf',
							"txt" => "text/plain",
							"pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
							"xml" => "image/svg+xml",
							"rar" => "application/rar",
							"zip" => "application/zip",
							"odt" => "application/vnd.oasis.opendocument.text",
							"pdf" => "application/pdf",
							"jpg|jpeg|jpe" => "image/jpeg",
							"png" => "image/png",
							"svg" => "image/svg+xml"
						],
						"placeholder" => "",
						"value" => "",
						"required" => false,
						"errorMessages" => [
							"fileSizeError" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_3', 'fields-form'], "fileSizeError"),
							"fileExtensionError" => $this->clasVedLangTrans->getFieldValue($this->formPageId, ['fields_3', 'fields-form'], "fileExtensionError")
						]
					],
				]
			],

		];
	}

	/**
	 * По ключу
	 *
	 * @return string[]
	 */
	public function getExtensionsFile($extensions)
	{
		$arrExtensions = $this->extensionsFile();

		if (!empty($extensions)) {

			if ($key = array_search($extensions, $arrExtensions)) {
				return $key;
			} else {
				foreach ($arrExtensions as $kayExt => $item) {
					$pieces = explode("/", $item);
					if (array_search($extensions, $pieces)) {
						return $kayExt;
					}
				}

				return false;
			}

		} else {
			return $arrExtensions;
		}

	}


	public function getExtensionsParam($extensions){

		$arrExtensions = $this->extensionsFile();

		if(!empty($extensions)){
			foreach ($arrExtensions as $key=>$value){
				if($extensions==$key){
					return $value;
				}
			}
		}

		return false;

	}


	/**
	 * Список типов файлов
	 *
	 * @return string[]
	 */
	public function extensionsFile(){
		return [
			"doc" => 'application/msword',
			"docx" => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			"rtf" => 'application/rtf',

			"txt" => "text/plain",
			"txt" => "plain",

			"pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
			"xml" => "image/svg+xml",
			"rar" => "application/rar",
			"zip" => "application/zip",

			"odt" => "application/vnd.oasis.opendocument.text",
			"odt" => "vnd.oasis.opendocument.text",

			"pdf" => "application/pdf",


			"jpeg" => "image/jpeg",
			"png" => "image/png",
			"svg" => "image/svg+xml",

			"xml" => "xml",

			'avi' => 'video/avi',
			'mp4' => 'mp4',
			"xlsx" => "vnd.openxmlformats-officedocument.spreadsheetml.sheet"

		];
	}

}
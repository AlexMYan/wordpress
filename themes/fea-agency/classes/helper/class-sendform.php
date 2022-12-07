<?php

namespace Helper;
require_once($_SERVER["DOCUMENT_ROOT"] . '/wp-load.php');

class SendForm
{

	private $classExtra;
	private $classListForms;
	private $classReCaptcha;

	private $secret;

	private $siteKey;

	private $property;

	//Настройки
	private $clasVedParametrs;
	//переводы
	private $clasVedLangTrans;
	//id страницы настроек с переводами
	private $trPageId;
	//id страницы настроек
	private $settingsPageId;
	//id страницы настроек форм
	private $settingsPageFormId;


	/**
	 * construct
	 */
	public function __construct()
	{
		$this->property = 'SendForm';

		//Вспомогательный класс
		$this->classExtra = new \Helper\Extra();
		//формы
		$this->classListForms = new \Helper\ListForms();
		//recaptcha
		$this->classReCaptcha = new \Helper\ReCaptcha();

		$this->clasVedParametrs = new \Helper\VedParametrs();

		$this->clasVedLangTrans = new \Helper\VedLanguagesTranslate();

		$this->secret = $this->clasVedParametrs->reCaptchaSecretKey();
		$this->siteKey = $this->clasVedParametrs->reCaptchaSiteKey();

		$this->trPageId = \Helper\VedParametrs::LANGUDGE_TRANSLATE_PAGE_ID;

		$this->settingsPageId = \Helper\VedParametrs::SETTINGS_PAGE_ID;

		$this->settingsPageFormId = \Helper\VedParametrs::FORMS_PAGE_ID;
	}

	/**
	 * @return string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Обработка запроса формы
	 *
	 * @param $formId
	 * @param string $pageId // этот параметр отвечает за форму в которой справа выводится текст из страницы
	 * @param array $sendFields
	 * @return array|false
	 */
	public function sendForm($formId, $pageId = "0", $sendFields = [])
	{
		$arrResult = [];
		$arrResult["errors"] = [];
		$strRecaptch = "";
		$subscriptionEmail = "";

		//если была нажата кнопка
		if ($sendFields) {
			if (isset($sendFields["recaptcha"]["status"]) && !empty($sendFields["recaptcha"]["status"])) {

				if ($arrErrorCaptch = $this->classReCaptcha->reCaptchaCheck($this->secret,
					$sendFields["recaptcha"]["status"])) {

					if (isset($arrErrorCaptch["success"]) && !empty($arrErrorCaptch["success"])) {
						$strRecaptch = "";
					} elseif (isset($arrErrorCaptch["error-codes"]) && !empty($arrErrorCaptch["error-codes"])) {

							foreach ($arrErrorCaptch["error-codes"] as $error) {
								//$strRecaptch .= "<br>" . $error;
								$strRecaptch .= $this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms', "errors"],
									"timeout-or-duplicate");
								$arrResult["errors"]["form"][] = [
									$this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms', "errors"],
										"timeout-or-duplicate")
								];

							}
					}

				} else {

					$strRecaptch = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms', "errors"], "captcha");
				}
			} else {

				$strRecaptch = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms', "errors"], "captcha");
			}
		}


		$privacyPolicy = "";
		//достаем из страницы настроек
		if ($arrSettingsPagePB = get_field('footer', $this->settingsPageId)) {
			//смотрим id страницы Политики конф
			if (isset($arrSettingsPagePB["policy"]) && $arrSettingsPagePB["policy"] > 0) {
				//Достаем из страницы с переводами текст
				if ($textCookie = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['popup-blocks'], "policy")) {
					//Есть ли ссылка страницы Политики конф
					if ($link = get_permalink($arrSettingsPagePB["policy"])) {

						$text = $this->clasVedLangTrans->getFieldValue($this->settingsPageFormId, ['total-settings'], "policy");
						if (empty($text)) {
							$text = get_the_title($arrSettingsPagePB["policy"]);
						}

						//формируем строку ссылку
						$strLink = '<a target="_blank" href="' . $link . '">' . $text . '</a>';
						//вставляем в текст
						if ($insertResult = $this->classExtra->insertFindValueInString($textCookie, "{{LINK}}", $strLink)) {
							$privacyPolicy = $insertResult;
						}
					}
				}
			}
		}

		if (!empty($formId)) {
			//достаем поля формы
			$arrFormsFields = $this->classListForms->getForms($formId);


			//естьли такая форма с полями
			if (isset($arrFormsFields[$formId])) {
				//стандартный массив для формы


				$textBtn = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['buttons'], "submit");
				if ($formId == "ved_subscription") {
					$textBtn = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms'], "subscrible");
				}

				//язык из плагина
				$lang = \WPGlobus::Config()->language;

				$prefLink = "";

				//языковой url
				if ($lang != "ru") {
					$prefLink = "/" . $lang;
				}

				$arrResult = [
					"remote" => $prefLink . "/wp-json/vedroute/v1/sendform/" . $formId . "/" . $pageId,
					"title" => $arrFormsFields[$formId]["title"],
					"title2" => $arrFormsFields[$formId]["title2"],
					"subtitle" => $arrFormsFields[$formId]["subtitle"],
					"requireConfirm" => true,
					"requireConfirmText" => $privacyPolicy,
					"buttons" => [[
						"type" => "submit",
						"text" => $textBtn,
						"className" => "btn btn--primary btn--lg",
						"disabled" => false
					]],
					"recaptcha" => [
						"siteKey" => $this->siteKey,
						"theme" => "light",
						//"error" => !empty($strRecaptch) ? $strRecaptch : ""  //закоментировано потому что
						// отображается в самом блоке капчи
					],

				];

				//есть ли поля
				if ($arrFormsFields[$formId]["fields"]) {
					$strResponse = "";
					$arrFileInfo = [];
					$fileName = "";

					//Если форма услуги, то добавлеям заголовок страницы на которой расположена форма
					if ($formId == "ved_forms_services" && $pageId > 0) {
						$strResponse .= "Услуга: " . get_the_title($pageId) . "<br>";
					}

					//это нужно что бы отдать поля в письмо на русском
					$objFieldACF = get_field_object($arrFormsFields[$formId]["acf-key"], $this->settingsPageFormId);

					$keyACF = array_search('fields-form', array_column($objFieldACF["sub_fields"], 'name'));

					$arrName = $objFieldACF["sub_fields"][$keyACF]["sub_fields"];

					//проходим по полям и собираем массив
					foreach ($arrFormsFields[$formId]["fields"] as $key => $field) {


						if ($key == 0) {
							$arrResult["groups"][] = [
								"id" => $formId
							];
						}
						$mask = "";
						$masks = [];
						$selectField = [];
						//телефон + все маски
						if ($field["type"] == "tel") {

							if ($fiedsMask = $this->getListPhone()) {
								$masks = $fiedsMask["masks"];
								$selectField = $fiedsMask["selectField"];
							}
						}

						$note = "";
						if (isset($field["note"])) {
							$note = $field["note"];

						}

						$allowedExtensions = [];
						if (isset($field["allowedExtensions"]) && !empty($field["allowedExtensions"])) {
							$allowedExtensions = $field["allowedExtensions"];

							$note .= " " . $field["allowedExtensions_txt"];
						}


						if ($field["type"] == "file" && isset($sendFields["files"])) {

							if ($arrFileInfo = $this->classExtra->checkFiles($field)) {


								if (isset($arrFileInfo["error"]) && $arrFileInfo["error"] != 0) {

									$arrResult["errors"]["form"] = [
										$arrFileInfo["error"],
									];

								} else {
									$fileName = $arrFileInfo;
								}
							}

						}


						$textNote1 = "";
						$textNote2 = [];

						if ($field["type"] == "textarea") {

							$textNote1 = $field["textNote1"];
							$textNote2 = $field["textNote2"];

							if (!empty($sendFields["textarea"])) {
								$sendFields["textarea"] = mb_substr($sendFields["textarea"], 0, 3000, 'UTF-8');
							}
						}

						$value = $field["value"];
						//проверяем пришедшие поля


						if ($field["required"] && $sendFields && $field["type"] != "file") {
							if (isset($sendFields[$field["id"]]) && !empty($sendFields[$field["id"]])) {
								$value = $sendFields[$field["id"]];

								$strResponse .= $this->getLangTxtRU($arrName, $field["id"], $value, $field["label"]);

							} else {
								$arrResult["errors"]["fields"][] = [
									"name" => $field["id"],
									"message" => [[
										$this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms', "errors"], "required")
									]]
								];
							}
						} elseif (!empty($sendFields[$field["id"]]) && $field["type"] != "file") {
							//$strResponse .= $field["label"] . ": " . $sendFields[$field["id"]] . "<br>";

							$strResponse .= $this->getLangTxtRU($arrName, $field["id"], $sendFields[$field["id"]],
								$field["label"]);

						}

						if ($field["type"] == "email" && !empty($value) && $sendFields) {
							if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
								$subscriptionEmail = $value;
							} else {
								$arrResult["errors"]["fields"][] = [
									"name" => $field["id"],
									"message" => [[
										$this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms', "errors"], "email")
									]]
								];
							}
						}


						$arrResult["fields"][] = [
							"groupId" => $arrResult["groups"][0]["id"],
							"id" => $field["id"],
							"name" => $field["id"],
							"type" => $field["type"],
							"inputmode" => $field["type"],
							"label" => $field["label"],
							"placeholder" => $field["placeholder"],
							"value" => $value,
							"required" => $field["required"],
							"note" => $note,
							"masks" => $masks,
							"selectField" => $selectField,
							"maxFileSize" => isset($field["maxFileSize"]) ? $field["maxFileSize"] : "",
							"allowedExtensions" => $allowedExtensions,
							"errorMessages" => isset($field["errorMessages"]) ? $field["errorMessages"] : "",
							"maxlength" => 3000,
							"textNote1" => $textNote1,
							"textNote2" => $textNote2,
							"files" => null,
						];

					}


					if ($formId == "ved_subscription") {
						if ($this->checkUserAdd($subscriptionEmail)) {
							$arrResult["errors"]["form"][] = [
								$this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms', "errors"], "suscrible")
							];
						}
					}


					if (!isset($arrResult["errors"]["fields"]) && empty($arrResult["errors"]["fields"]) &&
						$sendFields && empty($arrResult["errors"]["form"]) && empty($strRecaptch)) {

						//отправка письма
						if ($this->send($formId, $arrFormsFields[$formId], $subscriptionEmail)) {
							//окно успешной отправки
							$arrResult["modal"] = $arrFormsFields[$formId]["modal"];
							//если форма подписки
							if ($formId == "ved_subscription") {
								//добавляем пользователя
								$this->addUserEmailSubscription($arrFormsFields[$formId], $subscriptionEmail);
							}
							//добавляем запись в письма
							$postID = $this->addPostForm($arrFormsFields[$formId], $strResponse, $fileName);
							//письмо админу
							$this->sendToAdmin($formId, $arrFormsFields[$formId], $strResponse, $postID);

							//флаг успешной отправки для vue
							$arrResult["success"] = true;

							//   return $arrResult;

						} else {
							$arrResult["errors"]["form"][] = [
								$this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms', "errors"],
									"next-send")
							];
						}
						//  return $arrResult;
					}

					return $arrResult;
				}
			}
		}

		return false;
	}

	/**
	 * отправка письма пользователю
	 *
	 * @return bool
	 */
	private function send($formId, $arrFormsFields, $userEmail = "")
	{

		$titleFrom = $this->clasVedLangTrans->getFieldValue($this->trPageId, ['forms'], "message-title");
		if (isset($arrFormsFields["title-from"]) && !empty($arrFormsFields["title-from"])) {
			$titleFrom = $arrFormsFields["title-from"];
		}

		$message = "";
		if (isset($arrFormsFields["success"]) && !empty($arrFormsFields["success"])) {
			$message = $arrFormsFields["success"];
		}


		$email = $userEmail;

		if (wp_mail($email, $titleFrom, $message)) {
			return true;
		} else {

			return false;
		}


	}


	/**
	 * отправка письма админу
	 *
	 * @return bool
	 */
	private function sendToAdmin($formId, $arrFormsFields, $message, $postId)
	{


		$titleFrom = "Форма: ";
		if (isset($arrFormsFields["title"]) && !empty($arrFormsFields["title"])) {
			$titleFrom .= $arrFormsFields["title"];
		}

		$message = str_replace('<br>', "\n", $message);

		//из настроек
		$email = get_option($formId);

		$attachments = [];

		if ($postId > 0) {

			$file = get_field("file", $postId);

			if (isset($file["url"]) && !empty($file["url"])) {
				$parts = parse_url($file["url"]);
				if ($parts["path"]) {
					$bodytag = str_replace("/wp-content", "", $parts["path"]);

					$attachments = array(WP_CONTENT_DIR . $bodytag);
				}
			}
		}

		if (wp_mail($email, $titleFrom, $message, "", $attachments)) {
			return true;
		} else {
			return false;
		}


	}


	/**
	 * Создание записиси
	 *
	 * @param $arrFormFields
	 * @param $message
	 * @param $fileName
	 * @return bool
	 */
	private function addPostForm($arrFormFields, $message, $fileName = [])
	{


		if ($arrFormFields && !empty($arrFormFields["post_type"])) {

			$postTitle = "Сообщение от : " . $this->classExtra->getCurrentNowDateTimeZone();
			//Достаем из настроек
			if (isset($arrFormFields["description"]) && !empty($arrFormFields["description"])) {
				$findme = "{{DATE}}";
				$date = $this->classExtra->getCurrentNowDateTimeZone();
				$pos = strpos($arrFormFields["description"], $findme);
				if ($pos === false) {
				} else {
					$postTitle = str_replace($findme, $date, $arrFormFields["description"]);
				}
			}

			// Создаем массив данных новой записи
			$post_data = array(
				'post_title' => $postTitle,
				'post_content' => strip_tags($message, '<br>'),
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => $arrFormFields["post_type"],
			);
			// Вставляем запись в базу данных
			$post_id = wp_insert_post($post_data);
			if (is_wp_error($post_id)) {
				// return $post_id->get_error_message();
			} else {

				if (!empty($fileName)) {
					$attachId = $this->classExtra->get_image_attach_id($post_id, $fileName);
					if ($attachId > 0) {
						update_field('file', $attachId, $post_id);
					}
				}

				return $post_id;
			}
		}
		return false;

	}

	/**
	 * Добавляем email подписки
	 *
	 * @param $arrFormFields
	 * @param $email
	 * @return bool
	 */
	public function addUserEmailSubscription($arrFormFields, $email)
	{

		/*if ($arrFormFields && !empty($arrFormFields["post_type"])) {

			$post_data = array(
				'post_title'    => 'Подписка на новости на email '.$email,
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type' =>$arrFormFields["post_type"],
			);

			// Вставляем запись в базу данных
			$post_id = wp_insert_post($post_data);
			if (is_wp_error($post_id)) {
				// return $post_id->get_error_message();
			} else {
				update_field('email', $email, $post_id);

				return true;
			}
		}*/


		// Данные переданные в $_POST
		$userdata = [
			'user_login' => $email,
			'user_pass' => $email . "_" . rand(1, 15),
			'user_email' => $email,
			'first_name' => $email,
			'nickname' => $email,
		];

		/**
		 * Проверять/очищать передаваемые поля не обязательно,
		 * WP сделает это сам.
		 */

		$user_id = wp_insert_user($userdata);
		// возврат
		if (!is_wp_error($user_id)) {
			return true;
		}

		return false;
	}

	/**
	 * Проверяем есть ли такой email в списке
	 *
	 * @param $post_type
	 * @param $email
	 * @return bool
	 */
	private function checkEmailAdd($post_type, $email)
	{
		$args = array(
			'posts_per_page' => -1,
			'post_type' => $post_type,
			'orderby' => 'date',
			'order' => 'DESC',
			'meta_query' => array(
				array(
					'key' => 'email',
					'value' => $email,
					'compare' => '=',
				),

			),

		);


		if ($arrResult = $this->classExtra->customWPQueryWithTotal($args)) {
			if ($arrResult["total"] > 0)
				return true;
		}

		return false;
	}


	/**
	 * Проверяем есть ли такой email в списке
	 *
	 * @param $post_type
	 * @param $email
	 * @return bool
	 */
	private function checkUserAdd($email)
	{
		if ($user = get_user_by('email', $email))
			return true;

		return false;
	}

	private function getListPhone()
	{

		$arrResult = [];
		$arrMasks = [];
		$arrSelectField = [];
		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'ved_phone_mask',
			'orderby' => 'menu_order',
			'order' => 'ASC',


		);
		$arrResult = $this->classExtra->customWPQueryWithTotal($args);

		if ($arrResult["total"] > 0) {

			$arrSelectField = [
				"type" => "select",
				"label" => null,
				"value" => get_field("id", $arrResult["posts"][0]->ID),
				"placeholder" => $this->clasVedLangTrans->getFieldValue($this->settingsPageFormId, ['total-settings'], "placeholder-country")
			];

			foreach ($arrResult["posts"] as $key => $post) {

				//	if($key==0) {
				$id = $post->ID;

				$maskID = get_field("id", $id);
				$nameCountry = apply_filters('the_title', $post->post_title);
				$code = get_field("code", $id);
				$mask = get_field("mask", $id);

				$maskZero = str_replace("0", "_", $mask);
				$mask1 = str_replace("{", "", $maskZero);
				$mask2 = str_replace("}", "", $mask1);

				$arrMasks[] = [
					"id" => $maskID,
					"name" => $nameCountry,
					"code" => $code,
					"mask" => $mask,
					"placeholder" => $mask2
				];
				$arrSelectField["options"][] = [
					"value" => $maskID,
					"text" => $nameCountry . " " . $code,
				];

				//	}
			}

			$arrResult["masks"] = $arrMasks;
			$arrResult["selectField"] = $arrSelectField;
		}

		return $arrResult;
	}

	public function getLangTxtRU($arrName, $searchValue, $value, $defName)
	{


		if ($arrName) {
			$keyName = array_search($searchValue, array_column($arrName, 'name'));

			return $arrName[$keyName]["default_value"] . ": " . $value . "<br>";
		} else {
			return $defName . ": " . $value . "<br>";
		}
	}

	/**
	 * Обрезает текст до ближайшего конца слова после
	 * $count символов
	 */
	function SmartCutText($text, $count)
	{

		$text = strip_tags($text);
		while ($text[$count - 1] != ' ' && $count <= strlen($text)) {
			$count++;
		}
		return substr($text, 0, $count);
	}

	function truncate_words($text, $limit, $ellipsis = '...')
	{
		$words = preg_split("/[\n\r\t ]+/", $text, $limit + 1, PREG_SPLIT_NO_EMPTY);
		if (count($words) > $limit) {
			array_pop($words);
			$text = implode(' ', $words);
			$text = $text . $ellipsis;
		}
		return $text;
	}

}
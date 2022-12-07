<?php

namespace Helper;

require_once($_SERVER["DOCUMENT_ROOT"] . '/wp-load.php');

class VedParametrs
{
	//страницы "Языковые переводы"
	public const LANGUDGE_TRANSLATE_PAGE_ID = 4162;
	//Страница "Тендеры"
	public const TENDERS_PAGE_ID = 3372;
	//Страница "Страновые профили"
	public const COUNTRIES_PAGE_ID = 3298;
	//Страница "Деловой календарь"
	public const CALENDAR_PAGE_ID = 3416;
	//Страницы настроек сайта
	public const SETTINGS_PAGE_ID = 5107;
	//Страница "404"
	public const P404_PAGE_ID = 514;
	//Страница "Новости"
	public const NEWS_PAGE_ID = 259;
	//Страница "Проектов"
	public const PROJECTS_PAGE_ID = 162;
    //Страницы с переводами форм
	public const FORMS_PAGE_ID = 3684;
	//Секретный ключ
	private const reCaptchaSecretKey = "6Ld-Bl0iAAAAACOsQgCQD4z0uALXYvSTpgdpysq8";
	//Публичный ключ
	private const reCaptchaSiteKey = "6Ld-Bl0iAAAAAOTwJsLaFhqWuxWp-WpdYSH49fLb";
	//Кол-во постов при пагинации по умолчанию
	public const POST_COUNT_PAGE_DEFAULT = 30;
	//Кол-во символов после чего происходит запись в БД
	public const COUNT_SYMBOLS_SEARCH = 2;
	//Страница контакты
	public const CONTACT_PAGE_ID = 344;


	private $property;

	private $trPageId;

	//переводы
	private $clasVedLangTrans;

	/**
	 * construct
	 */
	public function __construct()
	{
		$this->property = 'VedParametrs';

		$this->clasVedLangTrans = new \Helper\VedLanguagesTranslate();

		$this->trPageId = self::LANGUDGE_TRANSLATE_PAGE_ID;
	}

	/**
	 * Короткое название "Дни недели"
	 *
	 * @return string[]
	 */
	public function shortNameWeek()
	{

		return [
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'short'], "monday"),//"Пн",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'short'], "tuesday"),//"Вт",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'short'], "wednesday"),//"Ср",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'short'], "thursday"),//"Чт",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'short'], "friday"),//"Пт",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'short'], "saturday"),//"Сб",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'short'], "sunday"),//"Вс"
		];
	}

	/**
	 * Полное название "Дней недели"
	 *
	 * @return array
	 */
	public function fullNameWeek()
	{

		return [
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'full'], "monday"),//"Пн",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'full'], "tuesday"),//"Вт",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'full'], "wednesday"),//"Ср",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'full'], "thursday"),//"Чт",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'full'], "friday"),//"Пт",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'full'], "saturday"),//"Сб",
			$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'name-of-week', 'full'], "sunday"),//"Вс"
		];
	}

	/**
	 * @return \string[][]
	 */
	public function nameMonth()
	{

		return [
			1 => [
				"january",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "january"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "january-l"),
			],
			2 => [
				"february",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "february"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "february-l"),
			],
			3 => [
				"march",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "march"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "march-l"),
			],
			4 => [
				"april",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "april"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "april-l"),
			],
			5 => [
				"may",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "may"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "may-l"),
			],
			6 => [
				"june",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "june"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "june-l"),
			],
			7 => [
				"july",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "july"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "july-l"),
			],
			8 => [
				"august",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "august"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "august-l"),
			],
			9 => [
				"september",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "september"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "september-l"),
			],
			10 => [
				"october",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "october"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "october-l"),
			],
			11 => [
				"november",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "november"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "november-l"),
			],
			12 => [
				"december",
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "december"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "december-l"),
			]
		];
	}

	/**
	 * Месяцы
	 *
	 * @return array
	 */
	public function nameMonthOne()
	{

		return [

				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "january"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "february"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "march"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "april"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "may"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "june"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "july"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "august"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "september"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "october"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "november"),
				$this->clasVedLangTrans->getFieldValue($this->trPageId, ['business-calendar', 'months'], "december"),

		];
	}


	/**
	 * @return string
	 */
	public function reCaptchaSecretKey()
	{
		return self::reCaptchaSecretKey;
	}

	/**
	 * @return string
	 */
	public function reCaptchaSiteKey()
	{
		return self::reCaptchaSiteKey;
	}

	/**
	 * Это нужно для версии слабовидящих (vue)
	 *
	 * @return string[]
	 */
	public function langsLocalization()
	{
		return [
			"ru" => "ru-RU",
			"eng" => "en-US",
			"by" => "by-BY",
			"cn" => "zh-ZH",
			"es" => "es-ES",
			"ae" => "ar-AR",
			"por" => "pt-PT"
		];
	}


	public function getLang(){
		//язык из плагина
		$lang = \WPGlobus::Config()->language;

		if($lang=="eng"){
			$lang="en";
		}elseif($lang=="cn"){
			$lang="zh";
		}elseif($lang=="ae"){
			$lang="ar";
		}elseif($lang=="por"){
			$lang="pt";
		}

		return $lang;
	}

	public function videoFormatShowPopup(){
		return [
			"mp4"
		];
	}

	public function pageExcludeSearch(){
		return [
			self::LANGUDGE_TRANSLATE_PAGE_ID,
			self::SETTINGS_PAGE_ID,
			self::FORMS_PAGE_ID,
		];
	}
}

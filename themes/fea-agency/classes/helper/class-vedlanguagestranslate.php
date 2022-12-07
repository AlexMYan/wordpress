<?php

namespace Helper;
//ядро wordpress
require_once($_SERVER["DOCUMENT_ROOT"] . '/wp-load.php');

class VedLanguagesTranslate
{
	private $property;
	//проверяем включен ли плагин
	private $flagACF;


	/**
	 * construct
	 */
	public function __construct()
	{
		$this->property = 'VedLanguagesTranslate';

		$this->flagACF = $this->checkACF();
	}

	/**
	 * Метод достает значение поля из ACF
	 *
	 * @param $pageId
	 * @param $sections //это цепочка групп плагина ACF
	 * @param $code //код поля
	 * @return false|mixed
	 */
	public function getFieldValue($pageId, $sections, $code)
	{

		if ($this->flagACF && !empty($code) && !empty($sections)) {

			//поля страницы
			$arrFields = get_field($sections[0], $pageId);

			if (count($sections) > 1) {

				unset($sections[0]);

				//возвращаем текст
				return $this->searchValue($arrFields, $sections, $code);

			} elseif (count($sections) == 1) {


				if (array_key_exists($code, $arrFields)) {
					return  apply_filters( 'the_title',  $arrFields[$code] ) ;
				}
			}
		}
		return false;
	}

	/**
	 *  рекурсиваный метод ищет по цепочке вкладок плагина АСF значение
	 *
	 * @param $array
	 * @param $arrKey
	 * @param $searchfor
	 * @return mixed|string
	 */
	public function searchValue($array, $arrKey, $searchfor)
	{

		$result = "";
		foreach ($array as $k => $item) {

			if (in_array($k, $arrKey)) {

				if (is_array($array[$k])) {
					$res = $this->searchValue($array[$k], $arrKey, $searchfor);
					$result = $res;
				}

				if (isset($item[$searchfor])) {
					$result = $item[$searchfor];
				}
			}
		}

		return $result;
	}

	/**
	 * Проверяем включен ли плагин ACF
	 *
	 * @return bool
	 */
	public function checkACF()
	{
		if (!class_exists('ACF')) {
			return false;
		}

		return true;
	}


	//for test
	/**
	 * рекурсиваный метод ищет по цепочке вкладок плагина АСF значение
	 *
	 * @param $array //массив полей страницы
	 * @param $arrKey //цепочка
	 * @param $searchfor //то что надо найти
	 * @return false|mixed
	 */
	private function searchValueTest($array, $arrKey, $searchfor)
	{

		foreach ($arrKey as $k => $key) {

			if (array_key_exists($key, $array)) {

				if (array_key_exists($searchfor, $array[$key])) {

					return $array[$key][$searchfor];

				} else {
					unset($arrKey[$k]);


					$this->searchValue($array[$key], $arrKey, $searchfor);
				}

			}
		}
		return false;
	}

	public function getFieldValueTest($pageId, $sections, $code)
	{


		if ($this->flagACF && !empty($code) && !empty($sections)) {

			//поля страницы
			$arrFields = get_field($sections[0], $pageId);

			if (count($sections) > 1) {

				unset($sections[0]);


				//возвращаем текст
				return $this->searchValue3($arrFields, $sections, $code);


			} elseif (count($sections) == 1) {

				if (array_key_exists($code, $arrFields)) {
					return $arrFields[$code];
				}
			}
		}
		return false;
	}
}

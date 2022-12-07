<?php
/**
 * Fea Agency functions and definitions
 *
 * @package WordPress
 * @subpackage Fea Agency
 * @since Fea Agency 1.0
 */


//en-eng - 814
//pt-por - 740
//ar-ae - 796
//zh-cn - 756
/**
 * autoload class
 */
spl_autoload_register(function ($classname) {
	// Regular
	$class = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($classname));
	$classpath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $class . '.php';

	// WordPress
	$parts = explode('\\', $classname);
	$class = 'class-' . strtolower(array_pop($parts));
	$folders = strtolower(implode(DIRECTORY_SEPARATOR, $parts));
	$wppath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $folders . DIRECTORY_SEPARATOR . $class . '.php';

	if (file_exists($classpath)) {
		include_once $classpath;
	} elseif (file_exists($wppath)) {
		include_once $wppath;
	}
});
//Вспомогательный класс для wp
$classExtraWordpress = new \Helper\ExtraWordpress();
//Вспомогательный класс
$classExtra = new \Helper\Extra();
//Массив типов постов
$classListCategorys = new \Helper\ListCategorys();
$arrNewPostType=$classListCategorys->getArrayCategorys();

//Страница настроек
$classMyoptions = new \Helper\MyOptions($arrNewPostType);
//Создание новых типов постов
$classExtra = new \Helper\NewPostType($arrNewPostType);

$classVedSettingsPage = new \Helper\VedRestApi();
//SEO
$classVedSeo= new \Helper\VedSeo();

//для загрузки изображения в настройках
function true_image_uploader_field($args)
{
	// следующая строчка нужна только для использования на страницах настроек
	$value = get_option($args['name']);
	// следующая строчка нужна только для использования в мета боксах
	//$value = $args[ 'value' ];
	$default = get_stylesheet_directory_uri() . '/screenshot.png';

	if ($value && ($image_attributes = wp_get_attachment_image_src($value, array(150, 110)))) {
		$src = $image_attributes[0];
	} else {
		$src = $default;
	}
	echo '
	<div>
		<img data-src="" src="' . $src . '" width="150" />
		<div>
			<input type="hidden" name="' . $args['name'] . '" id="' . $args['name'] . '" value="' . $value . '" />
			<button type="submit" class="upload_image_button button">Загрузить</button>
			<button type="submit" class="remove_image_button button">×</button>
		</div>
	</div>
	';
}

/**
 * Отключаем принудительную проверку новых версий WP, плагинов и темы в админке,
 * чтобы она не тормозила, когда долго не заходил и зашел...
 * Все проверки будут происходить незаметно через крон или при заходе на страницу: "Консоль > Обновления".
 *
 * @see https://wp-kama.ru/filecode/wp-includes/update.php
 * @author Kama (https://wp-kama.ru)
 * @version 1.1
 */
if( is_admin() ){
	// отключим проверку обновлений при любом заходе в админку...
	remove_action( 'admin_init', '_maybe_update_core' );
	remove_action( 'admin_init', '_maybe_update_plugins' );
	remove_action( 'admin_init', '_maybe_update_themes' );

	// отключим проверку обновлений при заходе на специальную страницу в админке...
	remove_action( 'load-plugins.php', 'wp_update_plugins' );
	remove_action( 'load-themes.php', 'wp_update_themes' );

	// оставим принудительную проверку при заходе на страницу обновлений...
	//remove_action( 'load-update-core.php', 'wp_update_plugins' );
	//remove_action( 'load-update-core.php', 'wp_update_themes' );

	// внутренняя страница админки "Update/Install Plugin" или "Update/Install Theme" - оставим не мешает...
	//remove_action( 'load-update.php', 'wp_update_plugins' );
	//remove_action( 'load-update.php', 'wp_update_themes' );

	// событие крона не трогаем, через него будет проверяться наличие обновлений - тут все отлично!
	//remove_action( 'wp_version_check', 'wp_version_check' );
	//remove_action( 'wp_update_plugins', 'wp_update_plugins' );
	//remove_action( 'wp_update_themes', 'wp_update_themes' );

	/**
	 * отключим проверку необходимости обновить браузер в консоли - мы всегда юзаем топовые браузеры!
	 * эта проверка происходит раз в неделю...
	 * @see https://wp-kama.ru/function/wp_check_browser_version
	 */
	add_filter( 'pre_site_transient_browser_'. md5( $_SERVER['HTTP_USER_AGENT'] ), '__return_empty_array' );
}

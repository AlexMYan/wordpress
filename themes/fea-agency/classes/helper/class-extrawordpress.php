<?php
/**
 * This file followings the WordPress naming conventions, using
 * class-{classname}.php
 */

namespace Helper;

require_once($_SERVER["DOCUMENT_ROOT"] . '/wp-load.php');

class ExtraWordpress
{
	private $classExtra;

	/**
	 * construct
	 */
	public function __construct()
	{
		//подключаем скрипты
		add_action('wp_enqueue_scripts', array($this, 'addThemeScripts'));
		//подключаем скрипты для админки
		add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'));
		//Вспомогательный класс
		$this->classExtra = new \Helper\Extra();
		//Позволяет устанавливать миниатюру посту.
		add_theme_support('post-thumbnails');
		//Добавление "Цитаты" для страниц
		add_action('init', array($this, 'addPageExcerpt'));
		//вернуть старый редактор wordpress
		add_filter('use_block_editor_for_post_type', '__return_false', 100);
		//Регистрируем и Добавляем меню
		add_action('init', array($this, 'menusFeaAgency'));
		//Добавляем свой LOGO в настройки темы
		add_action('after_setup_theme', array($this, 'setupFeaAgency'));
		//Для загрузки изображений в настройках админки
		add_action('admin_enqueue_scripts', array($this, 'includeUploadScript'));

		//Добавляем блоки в основную колонку на страницах постов и пост. страниц
		//add_action('add_meta_boxes',  array($this, 'extraAddCustomBox'));

		// Сохраняем данные, когда пост сохраняется
		add_action('save_post', array($this, 'newsiteSavePostdata'));
		//Выводит имя файла, шаблон которого вызывается
		//add_action('wp_head', array($this, 'showTemplate'));
		//показать админ панель
		$this->showAdminBar();
		//отключить сохранение редакции на странице (page)
		add_filter('wp_revisions_to_keep', array($this, 'closeRevisionsToKeep'));
		//разрешаем загрузку новых типов файлов (по умолчанию они запрещены самим wordpress)
		add_filter('upload_mimes', array($this, 'uploadAllowTypes'));
		//Запрещаем создавать записи в которых отсутствует заголовок и циата (не для всех)
		add_action('admin_footer-post.php', array($this, 'wphRequirePostElements'));
		add_action('admin_footer-post-new.php', array($this, 'wphRequirePostElements'));
		//это убирает ошибки плагина Rank Math SEO связанные с плагином WooCommerce (рекомендация саппорта)
		add_filter('action_scheduler_pastdue_actions_check_pre', '__return_false');

	}

	/**
	 * Прерываем сохранение записи если не заполнен заголовок и цитата
	 *
	 * @return void
	 */
	public function wphRequirePostElements()
	{

		//subsidiaries - Дочерние предприятия
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('#submitpost :submit').on('click.edit-post', function (event) {

					var posType = $('#post_type').val();
					if (posType == "subsidiaries2") {
						if (!$('#title').val().length) {
							alert('Необходимо указать заголовок записи.');
							$('#title').focus();
						} else if (!$('#excerpt').val().length) {
							alert('Необходимо указать Текст цитаты записи.');
							$('#excerpt').focus();
						} else {
							return true;
						}
						return false;
					}
				});
			});
		</script>
		<?php
	}

	/**
	 * разрешаем новые типы для загрузки
	 *
	 * @return array
	 */
	public function uploadAllowTypes($mimes)
	{

		// разрешаем новые типы
		$mimes['xml'] = 'application/xml';

		return $mimes;
	}

	/**
	 * Стили для админки
	 *
	 * @return void
	 */
	public function addAdminScripts()
	{
		wp_enqueue_style('admin-styles', get_stylesheet_directory_uri() . '/css/admin.css');
	}

	/**
	 * Показываем админ панель
	 *
	 * @return void
	 */
	public function showAdminBar()
	{
		if (current_user_can('editor') || current_user_can('administrator')) {
			show_admin_bar(true);
		}
	}

	/**
	 * Разрешаем цитаты
	 *
	 * @return void
	 */
	public function addPageExcerpt()
	{
		add_post_type_support('page', array('excerpt'));
	}


	/**
	 * Register navigation menus uses wp_nav_menu.
	 *
	 * @since Fea Agency 1.0
	 */
	public function menusFeaAgency()
	{

		if (function_exists('register_nav_menu')) {
			$locations = array(
					'top' => __('Меню в шапке', 'fea_agency'),
					'bottom1' => __('Меню в подвале', 'fea_agency'),
					'bottom2' => __('Меню в подвале', 'fea_agency'),
					'bottom3' => __('Меню в подвале', 'fea_agency'),
					'bottom4' => __('Меню в подвале', 'fea_agency'),
					'vedsitemap' => __('Карта сайта', 'fea_agency'),
			);

			register_nav_menus($locations);
		}

	}

	/**
	 * Добавляем свой LOGO
	 *
	 * @return void
	 */
	public function setupFeaAgency()
	{
		add_theme_support(
				'custom-logo', array(
						'height' => 180,
						'width' => 180
				)
		);
	}

	/**
	 * подключаем файл стилей темы
	 *
	 * @return void
	 */
	public function addThemeScripts()
	{
		//	wp_enqueue_style( 'style-name', get_template_directory_uri() .'/assets/dist/styles/styles.build.css');
		//   wp_enqueue_style( 'style-name', '/local/assets/dist/styles/styles.build.css');
		//	wp_enqueue_script('newscript',  '/local/assets/dist/scripts/scripts.build.js');
		// подключаем js файл темы
		//	wp_enqueue_script('newscript', get_template_directory_uri() . '/assets/dist/scripts/scripts.build.js');

		$lang = \WPGlobus::Config()->language;

		$param = "ru";
		if ($lang == "by") {
			$param = "ru";
		} elseif ($lang == "eng") {
			$param = "en";
		} elseif ($lang == "cn") {
			$param = "zh-TW";
		} elseif ($lang == "es") {
			$param = "es";
		}elseif ($lang == "ae") {
			$param = "ar";
		}elseif ($lang == "por") {
			$param = "pt-PT";
		}



		wp_enqueue_script('recaptchav2', 'https://www.google.com/recaptcha/api.js?explicit&hl='.$param);

		wp_enqueue_style('custom', get_template_directory_uri() . '/css/custom.css');

		wp_enqueue_script('custom_js', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery'), null, false);

	}

	/**
	 * Для загрузки изображений в админке на страницах настроек
	 *
	 * @return void
	 */
	public function includeUploadScript()
	{
		// у вас в админке уже должен быть подключен jQuery, если нет - раскомментируйте следующую строку:
		// wp_enqueue_script('jquery');
		// дальше у нас идут скрипты и стили загрузчика изображений WordPress
		if (!did_action('wp_enqueue_media')) {
			wp_enqueue_media();
		}
		// само собой - меняем admin.js на название своего файла
		wp_enqueue_script('myuploadscript', get_stylesheet_directory_uri() . '/js/admin.js', array('jquery'), null, false);

	}

	/**
	 * Добавляет дополнительные блоки (meta box) на страницы редактирования/создания постов, постоянных
	 * страниц или произвольных типов записей в админ-панели.
	 *
	 * @return void
	 */
	public function extraAddCustomBox()
	{
		$screens = array('page');
		add_meta_box(
				'newsite_field_sectionid',
				'Дополнительные поля',
				array($this, 'newsiteFieldMetaBoxCallback'),
				$screens
		);
	}

	/**
	 *  HTML код блока
	 *
	 * @param $post
	 * @param $meta
	 * @return void
	 */
	public function newsiteFieldMetaBoxCallback($post, $meta)
	{
		$screens = $meta['args'];

		// Используем nonce для верификации
		wp_nonce_field(plugin_basename(__FILE__), 'newsite_noncename');

		// значение поля
		$value = get_post_meta($post->ID, 'navigation_title_meta_key', 1);

		// Поля формы для введения данных
		echo '<label for="newsite_navigation_title">' . __("Заголовок для навигации", 'newsite_textdomain') . '</label> ';
		echo '<input type="text" id="newsite_navigation_title" name="newsite_navigation_title" value="' . $value . '" />';
	}

	/**
	 * Сохранение поля после редактирования или добавления
	 *
	 * @param $post_id
	 * @return void
	 */
	public function newsiteSavePostdata($post_id)
	{


		// Убедимся что поле установлено.
		if (!isset($_POST['newsite_navigation_title']))
			return;

		// проверяем nonce нашей страницы, потому что save_post может быть вызван с другого места.
		if (!wp_verify_nonce($_POST['newsite_noncename'], plugin_basename(__FILE__)))
			return;

		// если это автосохранение ничего не делаем
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		// проверяем права юзера
		if (!current_user_can('edit_post', $post_id))
			return;

		// Все ОК. Теперь, нужно найти и сохранить данные
		// Очищаем значение поля input.
		$my_data = sanitize_text_field($_POST['newsite_navigation_title']);

		// Обновляем данные в базе данных.
		update_post_meta($post_id, 'navigation_title_meta_key', $my_data);
	}

	/**
	 * В любом месте лучше в header
	 * global $template;
	 * echo basename($template);
	 *
	 * @return void
	 */
	public function showTemplate()
	{
		global $template;
		echo "<div class='template_name'>" . basename($template) . "</div>";
	}

	/**
	 * отключает редакции
	 *
	 * @return int
	 */
	public function closeRevisionsToKeep()
	{
		return 0;
	}


}

?>
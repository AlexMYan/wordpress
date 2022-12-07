<?php

namespace Helper;

class MyOptions
{
	private $classExtra;
	private $classListForms;
	//условно ссылки на страницы в административном меню
	private $page_slug;
	private $page_general_slug;
	private $page_my_site_content_slug;
	private $page_my_site_directory_slug;

	private $page_ved_forms_slug; //формы

	//Куда будут регистрироваться поля на странице администратовного меню
	private $option_group;
	private $option_general_group;
	private $option_my_site_content_group;
	private $option_my_site_directory_group;

	private $option_ved_forms_group;

	private $array_post_type;

	private $options_select;

	private $listForms;


	function __construct($arrPostType)
	{

		//Вспомогательный класс
		$this->classExtra = new \Helper\Extra();
        //формы
		$this->classListForms = new \Helper\ListForms();
		$this->listForms=$this->classListForms ->getForms();


		$this->array_post_type = $arrPostType;
		$this->page_slug = 'my_site';
		$this->page_general_slug = 'my_general_site';
		$this->page_my_site_content_slug = 'my_site_content'; //контент
		$this->page_my_site_directory_slug = 'my_site_directory';//справочники

		$this->page_ved_forms_slug = 'ved_forms';//формы

		$this->option_group = 'my_site_settings';
		$this->option_general_group = 'my_site_general_settings';
		$this->option_my_site_content_group = 'my_site_content_settings';
		$this->option_my_site_directory_group = 'my_site_directory_settings';

		$this->option_ved_forms_group = 'ved_forms_settings';

		$this->options_select = [
			'my_site_content' => "Контент",
			'my_site_directory' => "Справочники",
			"ved_forms"=> "Письма",
		];

		add_action('admin_menu', array($this, 'add'), 5);
		add_action('admin_init', array($this, 'settings'));
		add_action('admin_notices', array($this, 'notice'));

	}

	function add()
	{

		//Для создания в админке пункта меню куда вложим весь контент
		add_menu_page(
			'Контент',
			'Контент',
			'manage_options',
			$this->page_my_site_content_slug,
			array($this, 'displayLinksContent'),
			'dashicons-category',
			20
		);
		add_submenu_page(
			$this->page_my_site_content_slug,
			'Контент',
			'Контент',
			'manage_options',
			$this->page_my_site_content_slug,
			array($this, 'displayLinksContent'),
		);

		//Для создания в админке пункта меню куда вложим справочник
		add_menu_page(
			'Справочники',
			'Справочник',
			'manage_options',
			$this->page_my_site_directory_slug,
			array($this, 'displayLinksDirectory'),
			'dashicons-welcome-write-blog',
			20
		);
		add_submenu_page(
			$this->page_my_site_directory_slug,
			'Справочники',
			'Справочник',
			'manage_options',
			$this->page_my_site_directory_slug,
			array($this, 'displayLinksDirectory'),
		);


		//Для создания в админке пункта меню куда вложим  все формы
		add_menu_page(
			'Формы',
			'Формы',
			'manage_options',
			$this->page_ved_forms_slug,
			array($this, 'displayForms'),
			'dashicons-email-alt',
			20
		);
		add_submenu_page(
			$this->page_ved_forms_slug,
			'Формы',
			'Формы',
			'manage_options',
			$this->page_ved_forms_slug,
			array($this, 'displayForms'),
		);


	}

	function displayForms()
	{
		echo '<div class="wrap">
	<h1>' . get_admin_page_title() . '</h1>
	<form method="post" action="options.php">';

		settings_fields($this->option_ved_forms_group);
		do_settings_sections($this->page_ved_forms_slug);
		submit_button();

		echo '</form></div>';
	}

	function displayGeneral()
	{
		echo '<div class="wrap">
	<h1>' . get_admin_page_title() . '</h1>
	<form method="post" action="options.php">';

		settings_fields($this->option_general_group);
		do_settings_sections($this->page_general_slug);
		submit_button();

		echo '</form></div>';
	}

	function display()
	{

		echo '<div class="wrap">
	<h1>' . get_admin_page_title() . '</h1>
	<form method="post" action="options.php">';

		settings_fields($this->option_group);
		do_settings_sections($this->page_slug);
		submit_button();

		echo '</form></div>';

	}

	function displayLinksContent()
	{
		echo '<div class="wrap">
	<h1>' . get_admin_page_title() . '</h1>
	<form method="post" action="options.php">';

		settings_fields($this->option_my_site_content_group);
		do_settings_sections($this->page_my_site_content_slug);
		submit_button();

		echo '</form></div>';
	}

	function displayLinksDirectory()
	{
		echo '<div class="wrap">
	<h1>' . get_admin_page_title() . '</h1>
	<form method="post" action="options.php">';

		settings_fields($this->option_my_site_directory_group);
		do_settings_sections($this->page_my_site_directory_slug);
		submit_button();

		echo '</form></div>';
	}

	function settings()
	{

		//-----------------forms------------------

		add_settings_section('ved_forms_section_id', '', '', $this->page_ved_forms_slug);

        if($this->listForms){
			foreach ($this->listForms as $keyForm=>$form){

				if($form["show_in_menu"]){
					//для формы на главной
					register_setting($this->option_ved_forms_group, $keyForm, 'string');
					add_settings_field(
						$keyForm,
						$form["title_menu"],
						array($this, 'fieldInputTypeTxt'),
						$this->page_ved_forms_slug,
						$form["section_id"],
						array(
							'label_for' => $keyForm,
							'name' => $keyForm,
						)
					);
				}

			}
		}


		//-----------------Content settings------------------


		add_settings_section('content_settings_section_id', 'Очередность', '', $this->page_my_site_content_slug);
		add_settings_section('directory_settings_section_id', 'Очередность', '', $this->page_my_site_directory_slug);
		if ($this->array_post_type) {
			//Если включен плагин Nested Pages - сортировка работат не будет
			foreach ($this->array_post_type as $key => $postType) {
                //ссылка на раздел
				$name = "<a class='menu-link-new' href='" . get_site_url() . "/wp-admin/edit.php?post_type=" . $postType["slug"]
					. "'>"
					. $postType["name"] . "</a>";


				//Для перемещения разделов в меню (чисто отображение)
				$slugMenu = get_option($key . '_in_main_menu_select');


				if ($slugMenu == "my_site_content") {

					$optionGroup = $this->option_my_site_content_group;
					$catMenu = $this->page_my_site_content_slug;
					$section = "content_settings_section_id";

				} elseif($slugMenu == "ved_forms"){
					$optionGroup = $this->option_ved_forms_group;
					$catMenu = $this->page_ved_forms_slug;
					$section = "ved_forms_section_id";
				}else{
					$optionGroup = $this->option_my_site_directory_group;
					$catMenu = $this->page_my_site_directory_slug;
					$section = "directory_settings_section_id";
				}

				$fields = [
					[
						'group' => 1,
						'label_for' => $key . '_in_main_menu_select',
						'name' => $key . '_in_main_menu_select',
						'uid' => $key . '_in_main_menu_select',
						'label' => "",
						'section' => $section,
						'type' => 'select',
						"options" => $this->options_select,
						'default' => '',
					],
					[
						'group' => 0,
						'label_for' => $key . '_in_main_menu_position',
						'name' => $key . '_in_main_menu_position',
						'uid' => $key . '_in_main_menu_position',
						'label' => $name,
						'section' => $section,
						'type' => 'number',
						"options" => "",
						'default' => '',
					],

				];

				foreach ($fields as $field) {
					register_setting($optionGroup, $field["uid"]);
					add_settings_field(
						$field["uid"],
						$field["label"],
						array($this, 'fieldSelect'),
						$catMenu,
						$field["section"],
						$field
					);
				}


			}

		}
		//------------//-----Content settings------------------

	}


	function fieldSelect($arguments)
	{
		if ($value = get_option($arguments['name'])) {

		} else {
			$value = $arguments['default'];
		}

		switch ($arguments['type']) {
			case 'select': // If it is a select dropdown.
				if (!empty($arguments['options']) && is_array($arguments['options'])) {
					$options_markup = '';
					foreach ($arguments['options'] as $key => $label) {
						/**
						 * %s is not an attribute
						 *
						 * @noinspection HtmlUnknownAttribute
						 */
						$options_markup .= sprintf(
							'<option value="%s" %s>%s</option>',
							esc_attr($key),
							selected($value, $key, false),
							esc_html($label)
						);
					}
					// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
					printf(
						'<select name="%2$s">%3$s</select>',
						esc_html($arguments['group']),
						esc_html($arguments['uid']),
						$options_markup
					);
					// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				break;

			case 'number':
				printf(
					'<input type="number" min="1" id="%s" name="%s" value="%s" />',
					esc_attr($arguments['name']),
					esc_attr($arguments['name']),
					absint($value)
				);
				break;
			case 'string':
				printf(
					'<input type="text"  id="%s" name="%s" value="%s" class="regular-text" />',
					esc_attr($arguments['name']),
					esc_attr($arguments['name']),
					$value
				);
				break;
			case 'textarea':
				printf(
					'<textarea  cols="60" rows="5" type="text" id="%s" name="%s">%s</textarea>',
					esc_attr($arguments['name']),
					esc_attr($arguments['name']),
					$value
				);
				break;
			default:
				break;
		}


	}

	function field($args)
	{
// получаем значение из базы данных
		$value = get_option($args['name']);

		printf(
			'<input type="number" min="1" id="%s" name="%s" value="%s" />',
			esc_attr($args['name']),
			esc_attr($args['name']),
			absint($value)
		);

	}

	function fieldInputTypeTxt($args)
	{
		// получаем значение из базы данных
		$value = get_option($args['name']);

		printf(
			'<input type="text"  id="%s" name="%s" value="%s" class="regular-text" />',
			esc_attr($args['name']),
			esc_attr($args['name']),
			$value
		);

	}

	function fieldTextarea($args)
	{
// получаем значение из базы данных
		$value = get_option($args['name']);

		printf(
			'<textarea  cols="60" rows="5" type="text" id="%s" name="%s">%s</textarea>',
			esc_attr($args['name']),
			esc_attr($args['name']),
			$value
		);

	}


	function notice()
	{

		if (
			isset($_GET['page'])
			&& ($this->page_slug == $_GET['page'] ||
				$this->page_general_slug == $_GET['page'] ||
				$this->page_my_site_content_slug == $_GET['page'] ||
				$this->page_my_site_directory_slug == $_GET['page'] ||
				$this->page_ved_forms_slug  == $_GET['page']
			)
			&& isset($_GET['settings-updated'])
			&& true == $_GET['settings-updated']
		) {
			echo '<div class="notice notice-success is-dismissible"><p>Настройки сохранены!</p></div>';
		}

	}

}

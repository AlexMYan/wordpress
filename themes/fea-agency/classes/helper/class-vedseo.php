<?php
/**
 * This file followings the WordPress naming conventions, using
 * class-{classname}.php
 */

namespace Helper;

require_once($_SERVER["DOCUMENT_ROOT"] . '/wp-load.php');

class VedSeo
{
	private $property;

	private $classExtra;

	/**
	 * construct
	 */
	public function __construct()
	{
		$this->property = 'VedSeo';

		//Вспомогательный класс
		$this->classExtra = new \Helper\Extra();

		// force WP document_title function to run
		add_theme_support( 'title-tag' );
		add_filter( 'pre_get_document_title', [ $this, 'meta_title' ], 1 );
        //description
		add_action( 'wp_head', [$this, 'meta_description' ], 1 );
		//keywords
		add_action( 'wp_head', [ $this, 'meta_keywords' ], 1 );
	}
	/**
	 * @return string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Title
	 *
	 * @param $title
	 * @return string|void
	 */
	public function meta_title( $title = '' ){


		if( $title )
			return $title;

		static $cache; if( $cache ) return $cache;

		$title=get_field("title",$this->getID());

		if(empty($title)){
			$title = $this->classExtra->getNamePostInUrl();
		}

		if($title){

			$title = esc_html( $title );
			$title = capital_P_dangit( $title );

			return $cache = strip_tags($this->classExtra->convertHtmlInCharacters($title));

		}
	}


	/**
	 * Description
	 *
	 * @return mixed|string
	 */
	public function meta_description(){

		// called from `wp_head` hook
		$echo_result = ( func_num_args() === 1 );

		static $cache = null;
		if( isset( $cache ) ){

			if( $echo_result )
				echo $cache;

			return $cache;
		}
		$desc=get_field("description",$this->getID());

		$desc = str_replace( [ "\n", "\r" ], ' ', $desc );
		// remove shortcodes, but leave markdown [foo](URL)
		$desc = preg_replace( '~\[[^\]]+\](?!\()~', '', $desc );

		$cache = $desc
			? sprintf( "<meta name=\"description\" content=\"%s\" />\n", esc_attr( trim( $desc ) ) )
			: '';

		if( $echo_result )
			echo $cache;

		return $cache;
	}

	/**
	 * keywords
	 *
	 * @return void
	 */
	public function  meta_keywords(){

		$out=get_field("keywords",$this->getID());

		echo $out
			? '<meta name="keywords" content="'. esc_attr( $out ) .'" />' . "\n"
			: '';
	}

	/**
	 * id страницы
	 *
	 * @return int
	 */
	public function getID(){

        global $post;

        if( is_404() ){
            $id=  \Helper\VedParametrs::P404_PAGE_ID;
        }else{
            $id=$post->ID;
        }
        return $id;
    }

}
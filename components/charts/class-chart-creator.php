<?php
/**
 * Charts abstraction class
 *
 * Motherclass for chart creation
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 awesome.ug (support@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
 
if ( !defined( 'ABSPATH' ) ) exit;
 
abstract class Questions_ChartCreator{

    /**
     * Title of ChartCreator which will be shown in admin
     * @since 1.0.0
     */
    var $title;

    /**
     * Description of ChartCreator
     * @since 1.0.0
     */
    var $description;

    /**
     * Slug of ChartCreator
     * @since 1.0.0
     */
    var $slug;

    /**
     * Control variable if ChartCreator is already initialized
     * @since 1.0.0
     */
    var $initialized = FALSE;

	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct( $title, $description, $slug ) {

        $this->title = $title;
        $this->description = $description;
        $this->slug = $slug;

        if( is_admin() ):
            add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
        else:
            add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
        endif;
	} // end constructor

    /**
     * Function to register Charts creation module
     * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
     * @since 1.0.0
     */
    public function _register() {

        global $questions_global;

        if ( TRUE == $this->initialized ) {
            return FALSE;
        }

        if ( ! is_object( $questions_global ) ) {
            return FALSE;
        }

        if ( '' == $this->slug ) {
            $this->slug = get_class( $this );
        }

        if ( '' == $this->title ) {
            $this->title = ucwords( get_class( $this ) );
        }

        if ( '' == $this->description ) {
            $this->description = esc_attr__( 'This is a Questions Survey Element.', 'questions-locale' );
        }

        if ( array_key_exists( $this->slug, $questions_global->chart_creators ) ) {
            return FALSE;
        }

        if ( ! is_array( $questions_global->element_types ) ) {
            $questions_global->element_types = array();
        }

        $this->initialized = TRUE;

        return $questions_global->add_charts_creator( $this->slug, $this );
    }

    /**
     * Function to register library files
     */
    public function load_scripts(){
    }
}

/**
 * Register a new Chart creator
 * @param $element_type_class name of the element type class.
 * @return bool|null Returns false on failure, otherwise null.
 */
function qu_register_chart_creator( $chart_creator_class ) {

    if ( ! class_exists( $chart_creator_class ) ) {
        return FALSE;
    }

    // Register the group extension on the bp_init action so we have access
    // to all plugins.
    add_action(
        'init',
        create_function(
            '', '$extension = new ' . $chart_creator_class . ';
			add_action( "init", array( &$extension, "_register" ), 2 ); '
        ), 1
    );
}
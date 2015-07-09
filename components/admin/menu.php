<?php
/**
 * Menus of WP Admin
 *
 * This class Display Adds the menu WP Admin
 *
 * @author awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
 * @version 2015-04-16
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

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Questions_AdminMenu {

    var $notices = array();

    /**
     * Init in WordPress, run on constructor
     *
     * @return null
     * @since 1.0.0
     */
    public static function init() {

        if ( ! is_admin() )
            return NULL;

        add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
        add_action( 'parent_file', array( __CLASS__, 'tax_menu_correction' ) );
    }

    /**
     * Adds the Admin menu.
     * @since 1.0.0
     */
    public static function admin_menu() {

        add_menu_page(
            esc_attr__( 'Surveys', 'questions-locale' ),
            esc_attr__( 'Surveys', 'questions-locale' ),
            'edit_posts',
            'QuestionsAdmin',
            array( 'Questions_AdminSettingsPage', 'show' ),
            '',
            50
        );
        add_submenu_page(
            'QuestionsAdmin',
            esc_attr__( 'Create', 'questions-locale' ),
            esc_attr__( 'Create', 'questions-locale' ),
            'edit_posts',
            'post-new.php?post_type=questions'
        );
        add_submenu_page(
            'QuestionsAdmin',
            esc_attr__( 'Categories', 'questions-locale' ),
            esc_attr__( 'Categories', 'questions-locale' ),
            'edit_posts',
            'edit-tags.php?taxonomy=questions-categories'
        );
        add_submenu_page(
            'QuestionsAdmin',
            esc_attr__( 'Settings', 'questions-locale' ),
            esc_attr__( 'Settings', 'questions-locale' ),
            'edit_posts',
            'QuestionsAdmin',
            array( 'Questions_AdminSettingsPage', 'show' )
        );
    }

    /**
     * Fix for getting correct menu and display
     * @since 1.0.0
     */
    public static function tax_menu_correction( $parent_file ) {

        global $current_screen;
        $taxonomy = $current_screen->taxonomy;

        if ( $taxonomy == 'questions-categories' ) {
            $parent_file = 'QuestionsAdmin';
        }

        return $parent_file;
    }
}

Questions_AdminMenu::init();

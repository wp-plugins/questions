<?php
/**
 * Question post type functions
 *
 * This class adds question post type functions.
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

class Questions_AdminPostType{

    /**
     * Init in WordPress, run on constructor
     *
     * @return null
     * @since 1.0.0
     */
    public static function init() {

        if ( ! is_admin() )
            return NULL;

        add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'enqueue_scripts' ) );

        add_action( 'edit_form_after_title', array( __CLASS__, 'droppable_area' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 10 );

        add_action( 'save_post', array( __CLASS__, 'save_survey' ) );
        add_action( 'delete_post', array( __CLASS__, 'delete_survey' ) );

        add_action( 'wp_ajax_questions_add_members_standard', array( __CLASS__, 'ajax_add_members' ) );
        add_action( 'wp_ajax_questions_invite_participiants', array( __CLASS__, 'ajax_invite_participiants' ) );
        add_action( 'wp_ajax_questions_duplicate_survey', array( __CLASS__, 'ajax_duplicate_survey' ) );
        add_action( 'wp_ajax_questions_delete_results', array( __CLASS__, 'ajax_delete_results' ) );

        add_action( 'admin_notices', array( __CLASS__, 'jquery_messages_area' ) );
    }

    /**
     * Place to drop elements
     *
     * @since 1.0.0
     */
    public static function droppable_area() {

        global $post, $questions_global;

        if ( ! self::is_questions_post_type() )
            return;

        $html = '<div id="questions-content" class="drag-drop">';

        $html .= '<div id="drag-drop-area" class="widgets-holder-wrap">';

        $html .= '<div id="drag-drop-inside">';
        /* << INSIDE DRAG&DROP AREA >> */
        $survey = new Questions_Survey( $post->ID );
        // Running each Element
        foreach ( $survey->elements AS $element ):
            $html .= $element->draw_admin();
        endforeach;
        /* << INSIDE DRAG&DROP AREA >> */
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div id="delete_results_dialog"><h3>' . esc_attr__( 'Attention!', 'questions-locale' ) . '</h3><p>' . esc_attr__(
                'This will erase all Answers who people given to this survey. Do you really want to delete all results of this survey?', 'questions-locale'
            ) . '</p></div>';
        $html .= '<div id="delete_surveyelement_dialog">' . esc_attr__(
                'Do you really want to delete this element?', 'questions-locale'
            ) . '</div>';
        $html .= '<div id="delete_answer_dialog">' . esc_attr__(
                'Do you really want to delete this answer?', 'questions-locale'
            ) . '</div>';
        $html .= '<input type="hidden" id="deleted_surveyelements" name="questions_deleted_surveyelements" value="">';
        $html .= '<input type="hidden" id="deleted_answers" name="questions_deleted_answers" value="">';

        echo $html;
    }

    /**
     * Adding meta boxes
     *
     * @param string $post_type Actual post type
     * @since 1.0.0
     */
    public static function meta_boxes( $post_type ) {

        $post_types = array( 'questions' );

        if ( in_array( $post_type, $post_types ) ):
            add_meta_box(
                'survey-options',
                esc_attr__( 'Options', 'questions-locale' ),
                array( __CLASS__, 'meta_box_survey_options' ),
                'questions',
                'side'
            );
            add_meta_box(
                'survey-functions',
                esc_attr__( 'Survey Functions', 'questions-locale' ),
                array( __CLASS__, 'meta_box_survey_functions' ),
                'questions',
                'side'
            );
            add_meta_box(
                'survey-elements',
                esc_attr__( 'Elements', 'questions-locale' ),
                array( __CLASS__, 'meta_box_survey_elements' ),
                'questions',
                'side',
                'high'
            );
            add_meta_box(
                'survey-timerange',
                esc_attr__( 'Timerange', 'questions-locale' ),
                array( __CLASS__, 'meta_box_survey_timerange' ),
                'questions',
                'side',
                'high'
            );
            add_meta_box(
                'survey-participiants',
                esc_attr__( 'Participiants list', 'questions-locale' ),
                array( __CLASS__, 'meta_box_survey_participiants' ),
                'questions',
                'normal',
                'high'
            );
            add_meta_box(
                'survey-results',
                esc_attr__( 'Results', 'questions-locale' ),
                array( __CLASS__, 'meta_box_survey_results' ),
                'questions',
                'normal',
                'high'
            );
        endif;
    }

    /**
     * Elements for dropping
     * @since 1.0.0
     */
    public static function meta_box_survey_elements() {

        global $questions_global;

        $html = '';

        foreach ( $questions_global->element_types AS $element ):
            $html .= $element->draw_admin();
        endforeach;

        echo $html;
    }

    /**
     *
     */
    public static function meta_box_survey_timerange(){
        global $post;

        $survey_id = $post->ID;

        $start_date = get_post_meta( $survey_id, 'start_date', TRUE );
        $end_date = get_post_meta( $survey_id, 'end_date', TRUE );


        $html = '<label for="start_date">' . esc_attr__( 'When does the survey start?', 'questions-locale' ) . '</label>';
        $html.= '<p><input type="text" id="start_date" name="start_date" value="' . $start_date . '"/></p>';
        $html.= '<label for="end_date">' . esc_attr__( 'When does the survey end?', 'questions-locale' ) . '</label>';
        $html.= '<p><input type="text" id="end_date" name="end_date" value="' . $end_date . '"/></p>';

        echo $html;
    }

    /**
     * Survey participiants box
     * @since 1.0.0
     */
    public static function meta_box_survey_participiants() {

        global $wpdb, $post, $questions_global;

        $survey_id = $post->ID;

        $sql      = $wpdb->prepare(
            "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %s", $survey_id
        );
        $user_ids = $wpdb->get_col( $sql );

        $users = array();

        if ( is_array( $user_ids ) && count( $user_ids ) > 0 ):
            $users = get_users(
                array(
                    'include' => $user_ids,
                    'orderby' => 'ID'
                )
            );
        endif;

        $disabled = '';
        $selected = '';

        $participiant_restrictions = get_post_meta( $survey_id, 'participiant_restrictions', TRUE );

        $restrictions = apply_filters(
            'questions_post_type_participiant_restrictions',
            array(
                'all_visitors'     => esc_attr__(
                    'All visitors of the site can participate',
                    'questions-locale'
                ),
                'all_members'      => esc_attr__(
                    'All members of the site can participate',
                    'questions-locale'
                ),
                'selected_members' => esc_attr__(
                    'Only selected members can participate',
                    'questions-locale'
                ),
                'no_restrictions'     => esc_attr__(
                    'No restrictions',
                    'questions-locale'
                )
            )
        );

        if ( '' == $participiant_restrictions
            && count(
                $users
            ) > 0
        ): // If there are participiants and nothing was selected before
            $participiant_restrictions = 'selected_members';
        elseif ( '' == $participiant_restrictions ): // If there was selected nothing before
            $participiant_restrictions = 'all_visitors';
        endif;

        $html = '<div id="questions_participiants_select_restrictions">';
        $html .= '<select name="questions_participiants_restrictions_select" id="questions-participiants-restrictions-select"' . $disabled . '>';
        foreach ( $restrictions AS $key => $value ):
            $selected = '';
            if ( $key == $participiant_restrictions ) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        endforeach;
        $html .= '</select>';
        $html .= '</div>';

        $options = apply_filters(
            'questions_post_type_add_participiants_options',
            array(
                'all_members' => esc_attr__(
                    'Add all actual Members', 'questions-locale'
                ),
            )
        );

        /*
         * Selected Members section
         */
        $html .= '<div id="questions_selected_members">';

        $disabled = '';
        $selected = '';

        $html .= '<div id="questions_participiants_select">';
        $html .= '<select name="questions_participiants_select" id="questions-participiants-select"' . $disabled . '>';
        foreach ( $options AS $key => $value ):
            $html .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        endforeach;
        $html .= '</select>';
        $html .= '</div>';

        $html .= '<div id="questions-participiants-standard-options" class="questions-participiants-options-content">';
        $html .= '<div class="add"><input type="button" class="questions-add-participiants button" id="questions-add-members-standard" value="'
            . esc_attr__(
                'Add Participiants', 'questions-locale'
            ) . '" /><a href="#" class="questions-remove-all-participiants">'
            . esc_attr__(
                'Remove all Participiants', 'questions-locale'
            ) . '</a></div>';
        $html .= '</div>';

        ob_start();
        do_action( 'questions_post_type_participiants_content_top' );
        $html .= ob_get_clean();

        $html .= '<div id="questions-participiants-status" class="questions-participiants-status">';
        $html .= '<p>' . count( $users ) . ' ' . esc_attr__( 'participiant/s', 'questions-locale' ) . '</p>';
        $html .= '</div>';

        $html .= '<div id="questions-participiants-list">';
        $html .= '<table class="wp-list-table widefat">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>' . esc_attr__( 'ID', 'questions-locale' ) . '</th>';
        $html .= '<th>' . esc_attr__( 'User nicename', 'questions-locale' ) . '</th>';
        $html .= '<th>' . esc_attr__( 'Display name', 'questions-locale' ) . '</th>';
        $html .= '<th>' . esc_attr__( 'Email', 'questions-locale' ) . '</th>';
        $html .= '<th>' . esc_attr__( 'Status', 'questions-locale' ) . '</th>';
        $html .= '<th>&nbsp</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';

        $questions_participiants_value = '';

        if ( is_array( $users ) && count( $users ) > 0 ):

            foreach ( $users AS $user ):
                if ( qu_user_has_participated( $survey_id, $user->ID ) ):
                    $user_css  = ' finished';
                    $user_text = esc_attr__( 'finished', 'questions-locale' );
                else:
                    $user_text = esc_attr__( 'new', 'questions-locale' );
                    $user_css  = ' new';
                endif;

                $html .= '<tr class="participiant participiant-user-' . $user->ID . $user_css . '">';
                $html .= '<td>' . $user->ID . '</td>';
                $html .= '<td>' . $user->user_nicename . '</td>';
                $html .= '<td>' . $user->display_name . '</td>';
                $html .= '<td>' . $user->user_email . '</td>';
                $html .= '<td>' . $user_text . '</td>';
                $html .= '<td><a class="button questions-delete-participiant" rel="' . $user->ID . '">' . esc_attr__(
                        'Delete', 'questions-locale'
                    ) . '</a></th>';
                $html .= '</tr>';
            endforeach;

            $questions_participiants_value = implode( ',', $user_ids );

        endif;

        $html .= '</tbody>';

        $html .= '</table>';

        $html .= '<input type="hidden" id="questions-participiants" name="questions_participiants" value="' . $questions_participiants_value . '" />';
        $html .= '<input type="hidden" id="questions-participiants-count" name="questions-participiants-count" value="' . count(
                $users
            ) . '" />';

        $html .= '</div>';

        $html .= '</div>';

        echo $html;
    }

    /**
     * Showing survey results in admin
     * @since 1.0.0
     */
    public static function meta_box_survey_results(){
        global $wpdb, $post, $questions_global;

        $survey_id = $post->ID;

        $html = do_shortcode( '[survey_results id="' . $survey_id . '"]' );

        echo $html;
    }

    /**
     * Survey options
     * @since 1.0.0
     */
    public static function meta_box_survey_options() {

        global $post;

        $survey_id    = $post->ID;
        $show_results = get_post_meta( $survey_id, 'show_results', TRUE );

        if ( '' == $show_results ) {
            $show_results = 'no';
        }

        $checked_no  = '';
        $checked_yes = '';

        if ( 'no' == $show_results ) {
            $checked_no = ' checked="checked"';
        } else {
            $checked_yes = ' checked="checked"';
        }

        $html = '<div class="questions-options">';
        $html .= '<p><label for="show_results">' . esc_attr__(
                'Show results after finishing survey', 'questions-locale'
            ) . '</label></p>';
        $html .= '<input type="radio" name="show_results" value="yes"' . $checked_yes . '>' . esc_attr__( 'Yes' ) . ' ';
        $html .= '<input type="radio" name="show_results" value="no"' . $checked_no . '>' . esc_attr__( 'No' ) . '<br>';
        $html .= '</div>';

        ob_start();
        do_action( 'questions_survey_options', $survey_id );
        $html .= ob_get_clean();

        echo $html;
    }

    /**
     * Invitations box
     *
     * @since 1.0.0
     */
    public static function meta_box_survey_functions() {

        global $post;

        $questions_invitation_text_template   = qu_get_mail_template_text( 'invitation' );
        $questions_reinvitation_text_template = qu_get_mail_template_text( 'reinvitation' );

        $questions_invitation_subject_template   = qu_get_mail_template_subject( 'invitation' );
        $questions_reinvitation_subject_template = qu_get_mail_template_subject( 'reinvitation' );

        // Dublicate survey
        $html = '<div class="questions-function-element">';
        $html .= '<input id="questions-duplicate-button" name="questions-duplicate-survey" type="button" class="button" value="' . esc_attr__(
                'Dublicate Survey', 'questions-locale'
            ) . '" />';
        $html .= '</div>';

        // Delete results
        $html .= '<div class="questions-function-element">';
        $html .= '<input id="questions-delete-results-button" name="questions-delete-results" type="button" class="button" value="' . esc_attr__(
                'Delete survey results', 'questions-locale'
            ) . '" />';
        $html .= '</div>';

        if ( 'publish' == $post->post_status ):
            $html .= '<div class="questions-function-element">';
            $html .= '<input id="questions-invite-subject" type="text" name="questions_invite_subject" value="' . $questions_invitation_subject_template . '" />';
            $html .= '<textarea id="questions-invite-text" name="questions_invite_text">' . $questions_invitation_text_template . '</textarea>';
            $html .= '<input id="questions-invite-button" type="button" class="button" value="' . esc_attr__(
                    'Invite Participiants', 'questions-locale'
                ) . '" /> ';
            $html .= '<input id="questions-invite-button-cancel" type="button" class="button" value="' . esc_attr__(
                    'Cancel', 'questions-locale'
                ) . '" />';
            $html .= '</div>';

            $html .= '<div class="questions-function-element">';
            $html .= '<input id="questions-reinvite-subject" type="text" name="questions_invite_subject" value="' . $questions_reinvitation_subject_template . '" />';
            $html .= '<textarea id="questions-reinvite-text" name="questions_reinvite_text">' . $questions_reinvitation_text_template . '</textarea>';
            $html .= '<input id="questions-reinvite-button" type="button" class="button" value="' . esc_attr__(
                    'Reinvite Participiants', 'questions-locale'
                ) . '" /> ';
            $html .= '<input id="questions-reinvite-button-cancel" type="button" class="button" value="' . esc_attr__(
                    'Cancel', 'questions-locale'
                ) . '" />';

            $html .= '</div>';
        else:
            $html .= '<p>' . esc_attr__(
                    'You can invite Participiants to this survey after the survey is published.', 'questions-locale'
                ) . '</p>';
        endif;

        echo $html;
    }

    /**
     * Saving data
     *
     * @param int $post_id
     * @since 1.0.0
     */
    public static function save_survey( $post_id ) {
        global $questions_global, $wpdb;

        if ( !array_key_exists( 'questions', $_REQUEST ) ) {
            return;
        }

        if ( array_key_exists( 'questions-duplicate-survey', $_REQUEST ) ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! array_key_exists( 'post_type', $_POST ) ) {
            return;
        }

        if ( 'questions' != $_POST[ 'post_type' ] ) {
            return;
        }

        $survey_elements                  = $_POST[ 'questions' ];
        $survey_deleted_surveyelements    = $_POST[ 'questions_deleted_surveyelements' ];
        $survey_deleted_answers           = $_POST[ 'questions_deleted_answers' ];
        $survey_participiant_restrictions = $_POST[ 'questions_participiants_restrictions_select' ];
        $survey_show_results              = $_POST[ 'show_results' ];
        $questions_participiants          = $_POST[ 'questions_participiants' ];
        $start_date                       = $_POST[ 'start_date' ];
        $end_date                         = $_POST[ 'end_date' ];

        /**
         * Saving Restrictions
         */
        update_post_meta( $post_id, 'participiant_restrictions', $survey_participiant_restrictions );

        /**
         * Saving if results have to be shown after participating
         */
        update_post_meta( $post_id, 'show_results', $survey_show_results );

        /**
         * Saving start and end date
         */
        update_post_meta( $post_id, 'start_date', $start_date );
        update_post_meta( $post_id, 'end_date', $end_date );

        $survey_deleted_surveyelements = explode( ',', $survey_deleted_surveyelements );

        /**
         * Deleting deleted answers
         */
        if ( is_array( $survey_deleted_surveyelements ) && count( $survey_deleted_surveyelements ) > 0 ):
            foreach ( $survey_deleted_surveyelements AS $deleted_question ):
                $wpdb->delete(
                    $questions_global->tables->questions,
                    array( 'id' => $deleted_question )
                );
                $wpdb->delete(
                    $questions_global->tables->answers,
                    array( 'question_id' => $deleted_question )
                );
            endforeach;
        endif;

        $survey_deleted_answers = explode( ',', $survey_deleted_answers );

        /*
         * Deleting deleted answers
         */
        if ( is_array( $survey_deleted_answers ) && count( $survey_deleted_answers ) > 0 ):
            foreach ( $survey_deleted_answers AS $deleted_answer ):
                $wpdb->delete(
                    $questions_global->tables->answers,
                    array( 'id' => $deleted_answer )
                );
            endforeach;
        endif;

        /*
         * Saving elements
         */
        foreach ( $survey_elements AS $key => $survey_question ):
            if ( 'widget_surveyelement_XXnrXX' == $key ) {
                continue;
            }

            $question_id = (int) $survey_question[ 'id' ];
            $question    = '';
            $sort        = (int) $survey_question[ 'sort' ];
            $type        = $survey_question[ 'type' ];

            if ( array_key_exists( 'question', $survey_question ) ) {
                $question = qu_prepare_post_data( $survey_question[ 'question' ] );
            }

            $answers  = array();
            $settings = array();

            if ( array_key_exists( 'answers', $survey_question ) ) {
                $answers = $survey_question[ 'answers' ];
            }

            if ( array_key_exists( 'settings', $survey_question ) ) {
                $settings = $survey_question[ 'settings' ];
            }

            // Saving question
            if ( '' != $question_id ):
                // Updating if question already exists
                $wpdb->update(
                    $questions_global->tables->questions,
                    array(
                        'question' => $question,
                        'sort'     => $sort,
                        'type'     => $type
                    ),
                    array(
                        'id' => $question_id
                    )
                );
            else:

                // Adding new question
                $wpdb->insert(
                    $questions_global->tables->questions,
                    array(
                        'questions_id' => $post_id,
                        'question'     => $question,
                        'sort'         => $sort,
                        'type'         => $type
                    )
                );

                $question_id  = $wpdb->insert_id;
            endif;

            do_action( 'questions_save_survey_after_saving_question', $survey_question, $question_id );

            /*
             * Saving answers
             */
            if ( is_array( $answers ) && count( $answers ) > 0 ):
                foreach ( $answers AS $answer ):
                    $answer_id   = (int) $answer[ 'id' ];
                    $answer_text = qu_prepare_post_data( $answer[ 'answer' ] );
                    $answer_sort = (int) $answer[ 'sort' ];

                    $answer_section = '';
                    if ( array_key_exists( 'section', $answer ) ) {
                        $answer_section = $answer[ 'section' ];
                    }

                    if ( '' != $answer_id ):
                        $wpdb->update(
                            $questions_global->tables->answers,
                            array(
                                'answer'  => $answer_text,
                                'section' => $answer_section,
                                'sort'    => $answer_sort
                            ),
                            array(
                                'id' => $answer_id
                            )
                        );
                    else:
                        $wpdb->insert(
                            $questions_global->tables->answers,
                            array(
                                'question_id' => $question_id,
                                'answer'      => $answer_text,
                                'section'     => $answer_section,
                                'sort'        => $answer_sort
                            )
                        );
                        $answer_id = $wpdb->insert_id;
                    endif;

                    do_action( 'questions_save_survey_after_saving_answer', $survey_question, $answer_id );
                endforeach;
            endif;

            /*
             * Saving question settings
             */
            if ( is_array( $settings ) && count( $settings ) > 0 ):
                foreach ( $settings AS $name => $setting ):
                    $sql   = $wpdb->prepare( "SELECT COUNT(*) FROM {$questions_global->tables->settings} WHERE question_id = %d AND name = %s",  $question_id, $name );
                    $count = $wpdb->get_var( $sql );

                    if ( $count > 0 ):
                        $wpdb->update(
                            $questions_global->tables->settings,
                            array(
                                'value' => qu_prepare_post_data( $settings[ $name ] )
                            ),
                            array(
                                'question_id' => $question_id,
                                'name'        => $name
                            )
                        );
                    else:
                        $wpdb->insert(
                            $questions_global->tables->settings,
                            array(
                                'name'        => $name,
                                'question_id' => $question_id,
                                'value'       => qu_prepare_post_data( $settings[ $name ] )
                            )
                        );

                    endif;
                endforeach;
            endif;

        endforeach;

        $questions_participiant_ids = explode( ',', $questions_participiants );

        $sql = "DELETE FROM {$questions_global->tables->participiants} WHERE survey_id = %d";
        $sql = $wpdb->prepare( $sql, $post_id );
        $wpdb->query( $sql );

        if ( is_array( $questions_participiant_ids ) && count( $questions_participiant_ids ) > 0 ):
            foreach ( $questions_participiant_ids AS $user_id ):
                $wpdb->insert(
                    $questions_global->tables->participiants,
                    array(
                        'survey_id' => $post_id,
                        'user_id'   => $user_id
                    )
                );
            endforeach;
        endif;

        do_action( 'save_questions', $post_id );

        do_action( 'questions_save_survey', $post_id );

        // Preventing duplicate saving
        remove_action( 'save_post', array( __CLASS__, 'save_survey' ), 50 );
    }

    /**
     * Delete survey
     *
     * @param int $survey_id
     * @since 1.0.0
     */
    public static function delete_survey( $survey_id ) {

        global $wpdb, $questions_global;

        $sql      = $wpdb->prepare(
            "SELECT id FROM {$questions_global->tables->questions} WHERE questions_id=%d", $survey_id
        );
        $elements = $wpdb->get_col( $sql );

        /*
         * Answers & Settings
         */
        if ( is_array( $elements ) && count( $elements ) > 0 ):
            foreach ( $elements AS $question_id ):
                $wpdb->delete(
                    $questions_global->tables->answers,
                    array( 'question_id' => $question_id )
                );

                $wpdb->delete(
                    $questions_global->tables->settings,
                    array( 'question_id' => $question_id )
                );

                do_action( 'questions_delete_element', $question_id, $survey_id );
            endforeach;
        endif;

        /*
         * Questions
         */
        $wpdb->delete(
            $questions_global->tables->questions,
            array( 'questions_id' => $survey_id )
        );

        do_action( 'questions_delete_survey', $survey_id );

        /*
         * Response Answers
         */
        $sql       = $wpdb->prepare(
            "SELECT id FROM {$questions_global->tables->respond_answers} WHERE questions_id=%d", $survey_id
        );
        $responses = $wpdb->get_col( $sql );

        if ( is_array( $responses ) && count( $responses ) > 0 ):
            foreach ( $responses AS $respond_id ):
                $wpdb->delete(
                    $questions_global->tables->respond_answers,
                    array( 'respond_id' => $respond_id )
                );

                do_action( 'questions_delete_responds', $respond_id, $survey_id );
            endforeach;
        endif;

        /*
         * Responds
         */
        $wpdb->delete(
            $questions_global->tables->responds,
            array( 'questions_id' => $survey_id )
        );

        /*
         * Participiants
         */
        $wpdb->delete(
            $questions_global->tables->participiants,
            array( 'survey_id' => $survey_id )
        );
    }

    /**
     * Adding user by AJAX
     *
     * @since 1.0.0
     */
    public static function ajax_add_members() {

        $users = get_users(
            array(
                'orderby' => 'ID'
            )
        );

        $return_array = array();

        foreach ( $users AS $user ):
            $return_array[ ] = array(
                'id'            => $user->ID,
                'user_nicename' => $user->user_nicename,
                'display_name'  => $user->display_name,
                'user_email'    => $user->user_email,
            );
        endforeach;

        echo json_encode( $return_array );

        die();
    }

    /**
     * Invite participiants AJAX
     *
     * @since 1.0.0
     */
    public static function ajax_invite_participiants() {

        global $wpdb, $questions_global;

        $return_array = array(
            'sent' => FALSE
        );

        $survey_id        = $_POST[ 'survey_id' ];
        $subject_template = $_POST[ 'subject_template' ];
        $text_template    = $_POST[ 'text_template' ];

        $sql      = "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %d";
        $sql      = $wpdb->prepare( $sql, $survey_id );
        $user_ids = $wpdb->get_col( $sql );

        if ( 'reinvite' == $_POST[ 'invitation_type' ] ):
            $user_ids_new = '';
            if ( is_array( $user_ids ) && count( $user_ids ) > 0 ):
                foreach ( $user_ids AS $user_id ):
                    if ( ! qu_user_has_participated( $survey_id, $user_id ) ):
                        $user_ids_new[ ] = $user_id;
                    endif;
                endforeach;
            endif;
            $user_ids = $user_ids_new;
        endif;

        $post = get_post( $survey_id );

        if ( is_array( $user_ids ) && count( $user_ids ) > 0 ):
            $users = get_users(
                array(
                    'include' => $user_ids,
                    'orderby' => 'ID',
                )
            );

            $content = str_replace( '%site_name%', get_bloginfo( 'name' ), $text_template );
            $content = str_replace( '%survey_title%', $post->post_title, $content );
            $content = str_replace( '%survey_url%', get_permalink( $post->ID ), $content );

            $subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject_template );
            $subject = str_replace( '%survey_title%', $post->post_title, $subject );
            $subject = str_replace( '%survey_url%', get_permalink( $post->ID ), $subject );

            foreach ( $users AS $user ):
                if ( '' != $user->data->display_name ) {
                    $display_name = $user->data->display_name;
                } else {
                    $display_name = $user->data->user_nicename;
                }

                $user_nicename = $user->data->user_nicename;
                $user_email    = $user->data->user_email;

                $subject_user = str_replace( '%displayname%', $display_name, $subject );
                $subject_user = str_replace( '%username%', $user_nicename, $subject_user );

                $content_user = str_replace( '%displayname%', $display_name, $content );
                $content_user = str_replace( '%username%', $user_nicename, $content_user );

                qu_mail( $user_email, $subject_user, stripslashes( $content_user ) );
            endforeach;

            $return_array = array(
                'sent' => TRUE
            );
        endif;

        echo json_encode( $return_array );

        die();
    }

    /**
     * Dublicating survey AJAX
     *
     * @since 1.0.0
     */
    public static function ajax_duplicate_survey() {

        $survey_id = $_REQUEST[ 'survey_id' ];
        $survey    = get_post( $survey_id );

        if ( 'questions' != $survey->post_type ) {
            return;
        }

        $survey        = new questions_PostSurvey( $survey_id );
        $new_survey_id = $survey->duplicate( TRUE, FALSE, TRUE, TRUE, TRUE, TRUE );

        $post = get_post( $new_survey_id );

        $response = array(
            'survey_id'  => $new_survey_id,
            'post_title' => $post->post_title,
            'admin_url'  => site_url( '/wp-admin/post.php?post=' . $new_survey_id . '&action=edit' )
        );

        echo json_encode( $response );

        die();
    }

    /**
     * Deleting survey results
     *
     * @since 1.0.0
     */
    public static function ajax_delete_results() {

        $survey_id = $_REQUEST[ 'survey_id' ];
        $survey    = get_post( $survey_id );

        if ( 'questions' != $survey->post_type ) {
            return;
        }

        $survey        = new questions_PostSurvey( $survey_id );
        $new_survey_id = $survey->delete_results();

        $response = array(
            'survey_id'  => $survey_id,
            'deleted' => TRUE
        );

        echo json_encode( $response );

        die();
    }

    /**
     * Cheks if we are in correct post type
     *
     * @return boolean $is_questions_post_type
     * @since 1.0.0
     */
    private static function is_questions_post_type() {

        global $post;

        // If there is no post > stop adding scripts
        if ( ! isset( $post ) ) {
            return FALSE;
        }

        // If post type is wrong > stop adding scripts
        if ( 'questions' != $post->post_type ) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Adds the message area to the edit post site
     * @since 1.0.0
     */
    public static function jquery_messages_area(){
        $max_input_vars = ini_get( 'max_input_vars' );
        $html = '<div id="questions-messages" style="display:none;"><p class="questions-message">Das ist eine Nachricht</p></div><input type="hidden" id="max_input_vars" value ="' . $max_input_vars . '">'; // Updated, error, notice
        echo $html;
    }

    /**
     * Enqueue admin scripts
     *
     * @since 1.0.0
     */
    public static function enqueue_scripts() {

        if ( ! self::is_questions_post_type() )
            return;

        $translation_admin = array(
            'delete'                              => esc_attr__( 'Delete', 'questions-locale' ),
            'yes'                                 => esc_attr__( 'Yes', 'questions-locale' ),
            'no'                                  => esc_attr__( 'No', 'questions-locale' ),
            'just_added'                          => esc_attr__( 'just added', 'questions-locale' ),
            'invitations_sent_successfully'       => esc_attr__( 'Invitations sent successfully!', 'questions-locale' ),
            'invitations_not_sent_successfully'   => esc_attr__( 'Invitations could not be sent!', 'questions-locale' ),
            'reinvitations_sent_successfully'     => esc_attr__(
                'Renvitations sent successfully!', 'questions-locale'
            ),
            'reinvitations_not_sent_successfully' => esc_attr__(
                'Renvitations could not be sent!', 'questions-locale'
            ),
            'deleted_results_successfully'       => esc_attr__(
                'Survey results deleted successfully!', 'questions-locale'
            ),
            'duplicate_survey_successfully'       => esc_attr__(
                'Survey duplicated successfully!', 'questions-locale'
            ),
            'edit_survey'                         => esc_attr__( 'Edit Survey', 'questions-locale' ),
            'added_participiants'                 => esc_attr__( 'participiant/s', 'questions-locale' ),
            'max_fields_near_limit'				  => esc_attr__( 'You are under 50 form fields away from reaching PHP max_num_fields!', 'questions-locale' ),
            'max_fields_over_limit'				  => esc_attr__( 'You are over the limit of PHP max_num_fields!', 'questions-locale' ),
            'max_fields_todo'					  => esc_attr__( 'Please increase the value by adding <code>php_value max_input_vars [NUMBER OF INPUT VARS]</code> in your htaccess or contact your hoster. Otherwise your form can not be saved correct.', 'questions-locale' ),
            'of'								  => esc_attr__( 'of', 'questions-locale' ),

            'dateformat'                          => esc_attr__( 'yy/mm/dd', 'questions-locale' ),
            'min_sun'                             => esc_attr__( 'Su', 'questions-locale' ),
            'min_mon'                             => esc_attr__( 'Mo', 'questions-locale' ),
            'min_tue'                             => esc_attr__( 'Tu', 'questions-locale' ),
            'min_wed'                             => esc_attr__( 'We', 'questions-locale' ),
            'min_thu'                             => esc_attr__( 'Th', 'questions-locale' ),
            'min_fri'                             => esc_attr__( 'Fr', 'questions-locale' ),
            'min_sat'                             => esc_attr__( 'Sa', 'questions-locale' ),
            'january'                             => esc_attr__( 'January', 'questions-locale' ),
            'february'                            => esc_attr__( 'February', 'questions-locale' ),
            'march'                               => esc_attr__( 'March', 'questions-locale' ),
            'april'                               => esc_attr__( 'April', 'questions-locale' ),
            'may'                                 => esc_attr__( 'May', 'questions-locale' ),
            'june'                                => esc_attr__( 'June', 'questions-locale' ),
            'july'                                => esc_attr__( 'July', 'questions-locale' ),
            'august'                              => esc_attr__( 'August', 'questions-locale' ),
            'september'                           => esc_attr__( 'September', 'questions-locale' ),
            'october'                             => esc_attr__( 'October', 'questions-locale' ),
            'november'                            => esc_attr__( 'November', 'questions-locale' ),
            'december'                            => esc_attr__( 'December', 'questions-locale' ),
        );

        wp_enqueue_script( 'admin-questions-post-type', QUESTIONS_URLPATH . '/components/admin/includes/js/admin-questions-post-type.js' );
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_script( 'jquery-ui-datepicker');
        wp_enqueue_script( 'admin-widgets' );
        wp_enqueue_script( 'wpdialogs-popup' );

        wp_enqueue_style( 'jquery-style', QUESTIONS_URLPATH . '/components/admin/includes/css/datepicker.css' );

        wp_localize_script( 'admin-questions-post-type', 'translation_admin', $translation_admin );

        if ( wp_is_mobile() ) {
            wp_enqueue_script( 'jquery-touch-punch' );
        }
    }
}

Questions_AdminPostType::init();

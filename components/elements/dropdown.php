<?php
/**
 * @package    WordPress
 * @subpackage Questions
 * @author     Sven Wagener
 * @copyright  2015, awesome.ug
 * @link       http://awesome.ug
 * @license    http://www.opensource.org/licenses/gpl-2.0.php GPL License
 */

// No direct access is allowed
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Questions_SurveyElement_Dropdown extends Questions_SurveyElement {

	public function __construct( $id = NULL ) {

		$this->slug        = 'Dropdown';
		$this->title       = esc_attr__( 'Dropdown', 'questions-locale' );
		$this->description = esc_attr__( 'Add a question which can be answered within a dropdown field.', 'questions-locale' );
		$this->icon        = QUESTIONS_URLPATH . '/assets/images/icon-dropdown.png';

		$this->preset_of_answers  = TRUE;
		$this->preset_is_multiple = TRUE;
		$this->answer_is_multiple = FALSE;
		$this->is_displayable     = TRUE;

		$this->answer_syntax          = '<option value="%s" /> %s</option>';
		$this->answer_selected_syntax = '<option value="%s" selected="selected" /> %s</option>';
		$this->answer_params          = array( 'value', 'answer' );

		$this->create_answer_syntax = '<p><input type="text" name="%s" value="%s" /></p>';
		$this->create_answer_params = array( 'name', 'answer' );

		parent::__construct( $id );
	}

	public function input_html() {

		if ( ! is_array( $this->answers ) && count( $this->answers ) == 0 ) {
			return '<p>' . esc_attr__(
				'You don´t entered any answers. Please add some to display answers here.', 'questions-locale'
			) . '</p>';
		}

		$html = '<select name="' . $this->get_input_name() . '">';
		$html .= '<option value="please-select"> - ' . esc_attr__( 'Please select', 'questions-locale' ) . ' -</option>';

		foreach ( $this->answers AS $answer ):
			$checked = '';

			if ( $this->response == $answer[ 'text' ] ) {
				$checked = ' selected="selected"';
			}

			$html .= '<option value="' . $answer[ 'text' ] . '" ' . $checked . '/> ' . $answer[ 'text' ] . '</option>';
		endforeach;

		$html .= '</select>';

		return $html;
	}

	public function settings_fields() {

		$this->settings_fields = array(
			'description' => array(
				'title'       => esc_attr__( 'Description', 'questions-locale' ),
				'type'        => 'text',
				'description' => esc_attr__( 'The description will be shown after the question.', 'questions-locale' ),
				'default'     => ''
			),
		);
	}

	public function validate( $input ) {

		$error = FALSE;

		if ( 'please-select' == $input ):
			$this->validate_errors[ ] = sprintf( esc_attr__( 'Please select a value.', 'questions-locale' ) );
			$error                    = TRUE;
		endif;

		if ( $error ):
			return FALSE;
		endif;

		return TRUE;
	}

}

qu_register_survey_element( 'Questions_SurveyElement_Dropdown' );







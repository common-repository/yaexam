<?php

namespace YaExam\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Writer as Writer;
use YaExam\Tables\YAEXAM_Table_Question;
use YaExam\Tables\YAEXAM_Table_Question_Category;
class YAEXAM_Admin_Questions {
	
	private static $errors   = array();

	private static $messages = array();
	
	public static function output() {

		$tab = isset($_GET['tab']) ? yaexam_clean(wp_unslash($_GET['tab'])) : 'questions';

		$message = self::update();

		switch ( $tab ) {
			case 'categories':

				if( isset($_GET['id']) ) {

					self::_edit_category( yaexam_clean_int($_GET['id']) );

				}else{

					self::_categories();
				}
				
				break;
			case 'import':

				self::_import();
				
				break;
			default:
				
				if( isset($_GET['id']) ) {
					
					self::_edit_question( yaexam_clean_int($_GET['id']), $message );
					
				}else{
					
					self::_questions();
				}
				
				break;
		}

	}

	public static function update() {

		$tab = isset($_POST['tab']) ? yaexam_clean(wp_unslash($_GET['tab'])) : 'questions';

		switch ( $tab ) {
			case 'categories':
				
				return self::_update_category();

				break;
			
			default:
				
				return self::_update_question();

				break;
		}

	}

	public static function startImporting( $file ) {

		$spreadsheet 	= IOFactory::load($file['tmp_name']);
		$sheetData 		= $spreadsheet->getActiveSheet()->toArray();

		$status = true;

		if( is_array($sheetData) && count($sheetData) > 1 ) {

			array_shift( $sheetData );

			$sheetData = array_values($sheetData);

			foreach( $sheetData as $index => &$line ) {
				
					$post_data = [
						'answer_type' 	=> $line[0],
						'category' 		=> $line[1],
						'title' 		=> $line[2],
						'content'       => $line[3],
						'explanation'	=> $line[4],
						'score'			=> $line[5],
						'order_type'	=> $line[6],
						'video'			=> $line[8]
					];
					
	
					$answers = array();
					$answer_start_index = 10;
	
					for( $i = $answer_start_index; $i < count($line); $i++ ){
	
						if( $line[$i] ) {
							
							$answers[] = array( 'content' => $line[$i], 'image' => '' );
						}
					}
	
					if( $post_data['answer_type'] == 'single' ) {
	
						$post_data['answers_single'] = $answers;

						$post_data['answers_single_is-correct'] = $line[9];

						$post_data['single_params'] = array(
							'columns' => $line[7]
						);
		
					}elseif ( $post_data['answer_type'] == 'multiple' ) {

						$post_data['answers_multiple'] = $answers;
						
						$post_data['answers_multiple_is-correct'] = explode(',', $line[9]);

						$post_data['multiple_params'] = array(
							'columns' => $line[7]
						);
					}

					$line['status'] = self::save($post_data);
						
					$status = $status && $line['status'];
				
			}
		}

		return ['status' => $status, 'items' => $sheetData];
	}

	public static function save( $post ) {

		do_action('yaexam_admin_before_update_question', $post);
		
		$table = new YAEXAM_Table_Question();
		
		$data = [
			'category_id' => $post['category'],
			'title' => $post['title'],
			'content' => $post['answer_content'],
			'score' => $post['score'],
			'video' => $post['video'],
			'answer_type' => $post['answer_type'],
			'explanation' => $post['explanation'],
			'answers' => json_encode( $post['answers'] ),
			'params' => json_encode( $post['params'], JSON_UNESCAPED_SLASHES ),
		];

		if( $post['id'] ) {

			$data['id'] = $post['id'];
			
		}

		return $table->save($data);
	}

	public static function _import() {

		include 'views/html-admin-question-import.php';
	}

	public static function _update_question() {
		
		if( isset($_POST['action']) && $_POST['action'] == 'remove' ) {

			$ids = isset($_POST['questions_checkall']) ? yaexam_clean_int($_POST['questions_checkall']) : [];

			if( $ids ) {

				yaexam_remove_questions( $ids );
			}
		}

		if( isset($_POST['answer_type']) ) {

			$id = isset($_POST['id']) ? yaexam_clean_int($_POST['id']) : false;

			$answer_type = isset($_POST['answer_type']) ? yaexam_clean($_POST['answer_type']) : 'single';
			$params = is_array($_POST[$answer_type . '_params']) ? $_POST[$answer_type . '_params'] : [];
			$params = wp_parse_args($params, ['columns' => 1]);

			$answers = [];

			switch ( $answer_type ) {

				case 'multiple':
					
					$answers		=	isset($_POST['answers_multiple']) ? $_POST['answers_multiple'] : [];
					$ans_id_correct	=	isset($_POST['answers_multiple_is-correct']) ? yaexam_clean_int($_POST['answers_multiple_is-correct']) : [];

					// assign correct answer
					if($answers){
						foreach($answers as $id => &$value){

							$value['content'] = str_replace('\"', '"', wp_kses_post($value['content']));

							if(in_array($id, $ans_id_correct)){
								$value['is_correct']	=	1;
							
							}else{
								$value['is_correct']	=	-1;
							}
						}
					}

					break;

				case 'single':

					$answers		=	isset($_POST['answers_single']) ? $_POST['answers_single'] : [];
					$ans_id_correct	=	isset($_POST['answers_single_is-correct']) ? yaexam_clean_int($_POST['answers_single_is-correct']) : 0;

					// assign correct answer
					if($answers){
						foreach($answers as $aid => &$value){

							$value['content'] = str_replace('\"', '"', wp_kses_post($value['content']));

							if($aid == $ans_id_correct){
								$value['is_correct']	=	1;
							}else{
								$value['is_correct']	=	-1;
							}
						}
					}


					break;
			}

			if( $answers ) {

				foreach($answers as $aid => &$ans){
					
					if( isset($ans['image']) && $ans['image'] ) {

						$ans['image_url']	=	wp_get_attachment_image_src( yaexam_clean_int($ans['image']), 'full' );

					}else{

						$ans['image_url'] = '';
					}

				}
			}

			return self::save([
				'id' 				=> $id,
				'category' 			=> yaexam_clean_int($_POST['category']),
				'title' 			=> yaexam_clean($_POST['title']),
				'score' 			=> yaexam_clean_int($_POST['score']),
				'video' 			=> esc_url_raw($_POST['video']),
				'explanation' 		=> yaexam_clean(wp_unslash($_POST['explanation'])),
				'params' 			=> $params,
				'answer_content'    => wp_kses_post($_POST['content']),
				'answer_type' 		=> $answer_type,
				'answers'			=> $answers,
			]);
			
		}
	}

	public static function _update_category() {

		if( isset($_POST['submit-category']) ) {

			$table = new YAEXAM_Table_Question_Category();

			if( isset($_POST['id']) && $_POST['id'] ) {

				$id = $table->save([
					'id' => yaexam_clean_int($_POST['id']),
					'name' => yaexam_clean(wp_unslash($_POST['name'])),
					'content' => yaexam_clean(wp_unslash($_POST['content'])),
				]);

				
			}else{

				$id = $table->save([
					'name' => yaexam_clean(wp_unslash($_POST['name'])),
					'content' => yaexam_clean(wp_unslash($_POST['content'])),
				]);

				wp_redirect(admin_url('admin.php?page=em-questions&tab=categories&id=' . $id));
				exit;
			}
		}
	}
	
	public static function _questions() {

		$categories = yaexam_get_question_categories();
		
		$category = 0;

		if( isset($_GET['action']) && ($_GET['action'] == 'remove') ) {

			$ids = isset($_GET['questions_checkall']) ? yaexam_clean_int($_GET['questions_checkall']) : [];

			if( $ids ) {

				yaexam_remove_questions( $ids );
			}
		}

		if( isset($_GET['action']) && $_GET['action'] == 'filter' && $_GET['category'] ) {

			$category = isset($_GET['category']) ? yaexam_clean_int($_GET['category']) : 0;
		}
	
		$page = isset($_GET['p']) ? yaexam_clean_int($_GET['p']) : 1;
		
		$results = [];

		$paginated = yaexam_get_paginated_questions(['page' => $page, 'category' => $category]);

		$results = $paginated['data'];

		include 'views/html-admin-questions.php';
	}

	public static function _edit_question( $id, $message ) {

		global $wpdb;

		if( isset($_GET['action']) && ($_GET['action'] == 'remove') ) {

			$id = isset($_GET['id']) ? yaexam_clean_int($_GET['id']) : false;

			if( $id ) {

				yaexam_remove_questions( $id );

				wp_redirect(admin_url('admin.php?page=em-questions&tab=questions'));
				exit;
			}
		}

		$questions_tbl	=	$wpdb->prefix . 'yaexam_questions q';
		
		$question = [
			'title' => '', 
			'content' => '', 
			'answers' => [],
			'answer_type' => 'single'
		];

		$answers = [];
		$params  = ['columns' => 1];

		if( $id ){

			$question = $wpdb->get_row($wpdb->prepare('SELECT q.* FROM ' . $questions_tbl . 
				' WHERE q.id = %d', $id), ARRAY_A);

			$answers = json_decode( $question['answers'], true );
			$params  = json_decode( $question['params'], true );

		}
		
		$answer_type = $question['answer_type'];

		$answer_types	=	 apply_filters('yaexam_question_types', [
			'single'		=> __( 'Single choice', 'yaexam' ),
			'multiple'		=> __( 'Multiple choices', 'yaexam' ),
		]);

		include 'views/html-admin-question.php';
	}

	public static function _categories() {

		$results = yaexam_get_question_categories();

		include 'views/html-admin-question-categories.php';
	}

	public static function _edit_category( $id ) {

		global $wpdb;

		if( isset($_GET['action']) && $_GET['action'] == 'remove' ) {
			
			$ids = isset($_GET['id']) ? yaexam_clean_int($_GET['id']) : false;
			
			if( $ids ) {

				yaexam_remove_categories( $ids );

				wp_redirect(admin_url('admin.php?page=em-questions&tab=categories'));
				exit;
			}
		}

		$table	=	$wpdb->prefix . 'yaexam_question_categories';

		$category = [
			'name' => '', 
			'content' => '', 
		];

		if( $id ){

			$category = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $table . 
				' WHERE id = %d', $id), ARRAY_A);

		}
		
		include 'views/html-admin-question-category.php';
	}
}
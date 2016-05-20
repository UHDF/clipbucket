<?php

//require '../includes/admin_config.php';
$userquery->admin_login_check();
// Controle de permission probablement non fonctionnel sur les plugins
//$userquery->login_check('member_moderation');
$pages->page_redir();

/* Assigning page and subpage */
if(!defined('MAIN_PAGE')){
	define('MAIN_PAGE', 'Speaker');
}
if(!defined('SUB_PAGE')){
	define('SUB_PAGE', 'Add Speaker');
}

function load_speaker_fields($input=NULL) {
	global $LANG,$Cbucket;

	$default = array();

	if(isset($input)){
		$default = $input;
	}else{
		//return false;
	}
	/**
	 * this function will create initial array for speaker fields
	 * this will tell
	 * array(
	 *       title [text that will represents the field]
	 *       type [One of the following values : textfield, password,texarea, checkbox,radiobutton, dropbox]
	 *       name [name of the fields, input NAME attribute]
	 *       id [id of the fields, input ID attribute]
	 *       value [value of the fields, input VALUE attribute]
	 *       size
	 *       class [CSS class of the field]
	 *       label
	 *       extra_tags [Extra tags added as is to the field]
	 *       hint_1 [hint before field]
	 *       hint_2 [hint after field]
	 *       anchor_before [anchor before field]
	 *       anchor_after [anchor after field]
	 *      )
	 */

	if(empty($default))
		$default = $_POST;
		
	if(empty($default))
		$default = $_POST;
		
	/*$username = (isset($default['username'])) ? $default['username'] : "";
	$email = (isset($default['email'])) ? $default['email'] : "";*/
	//var_dump($_POST);
	//die();

	$my_fields = array
	(
			'firstname' => array(
					'title'=> lang('user_fname'),
					'type'=> "textfield",
					'name'=> "firstname",
					'id'=> "firstname",
					'value'=> "",
					//'hint_1'=> lang('user_allowed_format'),
					//'hint_2'=> lang('user_allowed_format'),
					'db_field'=>'firstname',
					'required'=>'yes',
					// 'syntax_type'=> 'username',
					//'validate_function'=> 'username_check',
					//'function_error_msg' => lang('user_contains_disallow_err'),
					//'db_value_check_func'=> 'user_exists',
					//'db_value_exists'=>false,
					//'db_value_err'=>lang('usr_uname_err2'),
					//'min_length'	=> config('min_username'),
					//'max_length' => config('max_username'),
			),
			'lastname' => array(
					'title'=> lang('user_lname'),
					'type'=> "textfield",
					'name'=> "lastname",
					'id'=> "lastname",
					'value'=> "",
					//'hint_1'=> lang('user_allowed_format'),
					//'hint_2'=> lang('user_allowed_format'),
					'db_field'=>'lastname',
					'required'=>'yes',
					// 'syntax_type'=> 'username',
					//'validate_function'=> 'username_check',
					//'function_error_msg' => lang('user_contains_disallow_err'),
					//'db_value_check_func'=> 'user_exists',
					//'db_value_exists'=>false,
					//'db_value_err'=>lang('usr_uname_err2'),
					//'min_length'	=> config('min_username'),
					//'max_length' => config('max_username'),
			),
			'slug' => array(
					'title'=> lang('Slug'),
					'type'=> "textfield",
					'name'=> "slug",
					'id'=> "slug",
					'value'=> "",
					'db_field'=>'slug',
					'required'=>'yes',
					//'invalid_err'=>lang('usr_cpass_err'),
					//'extra_tags'=>'readonly',
					//'syntax_type'=> 'email',
					//'db_value_check_func'=> 'email_exists',
					//'db_value_exists'=>false,
					//'db_value_err'=>lang('usr_email_err3')
			),
/*		'country'	=> array(
					'title'=> lang('country'),
					'type' => 'dropdown',
					'value' => $Cbucket->get_countries(iso2),
					'id'	=> 'country',
					'name'	=> 'country',
					'checked'=> $dcountry,
					'db_field'=>'country',
					'required'=>'yes',
			),
			'gender' => array(
					'title' => lang('gender'),
					'type' => 'radiobutton',
					'name' => 'gender',
					'class' => 'radio',
					'id' => 'gender',
					'value' => array('Male'=>lang('male'),'Female'=>lang('female')),
					'sep'=> '&nbsp;',
					'checked'=>'Male',
					'db_field'=>'sex',
					'required'=>'yes',
			),
				
			'cat'		=> array('title'=> lang('Category'),
					'type'=> 'dropdown',
					'name'=> 'category',
					'id'=> 'category',
					'value'=> array('category', isset($default['category'])),
					'db_field'=>'category',
					'checked'=> isset($default['category']),
					'required'=>'yes',
					'invalid_err'=>lang("Please select your category"),
					'display_function' => 'convert_to_categories',
					'category_type'=>'user',
			)*/
	);

	return $my_fields;
		
}
function load_speaker_fields2($input=NULL) {
	global $LANG,$Cbucket;
	$default = array();

	if(isset($input)){
		$default = $input;
	}else{
		//return false;
	}
	/**
	 * this function will create initial array for user fields
	 * this will tell
	 * array(
	 *       title [text that will represents the field]
	 *       type [One of the following values : textfield, password,texarea, checkbox,radiobutton, dropbox]
	 *       name [name of the fields, input NAME attribute]
	 *       id [id of the fields, input ID attribute]
	 *       value [value of the fields, input VALUE attribute]
	 *       size
	 *       class [CSS class of the field]
	 *       label
	 *       extra_tags [Extra tags added as is to the field]
	 *       hint_1 [hint before field]
	 *       hint_2 [hint after field]
	 *       anchor_before [anchor before field]
	 *       anchor_after [anchor after field]
	 *      )
	 */

	if(empty($default))
		$default = $_POST;

	if(empty($default))
		$default = $_POST;

	/*$username = (isset($default['username'])) ? $default['username'] : "";
	 $email = (isset($default['email'])) ? $default['email'] : "";*/
	//var_dump($_POST);
	//die();

	$my_fields = array
	(
			'description' => array(
					'title'=> lang('role'),
					'type'=> "textfield",
					'name'=> "description",
					'id'=> "description",
					'value'=> "",
					//'hint_1'=> lang('user_allowed_format'),
					//'hint_2'=> lang('user_allowed_format'),
					'db_field'=>'description',
					'required'=>'yes',
					// 'syntax_type'=> 'username',
					//'validate_function'=> 'username_check',
					//'function_error_msg' => lang('user_contains_disallow_err'),
					//'db_value_check_func'=> 'user_exists',
					//'db_value_exists'=>false,
					//'db_value_err'=>lang('usr_uname_err2'),
					//'min_length'	=> config('min_username'),
					//'max_length' => config('max_username'),
			),
	);
	return $my_fields;
}

function validate_form_fields($array=NULL) {
	$fields= load_speaker_fields($array);
	if($array==NULL)
		$array = $_POST;
	if(is_array($_FILES))
		$array = array_merge($array,$_FILES);
	
	validate_cb_form($fields,$array);
}

if(isset($_POST['add_speaker'])){
	global $db;
	validate_form_fields($_POST);
	if(!error()) {
		$firstname=$_POST['firstname'];
		$lastname=$_POST['lastname'];
		$req=" firstname = '".$firstname."' AND lastname='".$lastname."'";
		$res=$db->select(tbl('speaker'),'id',$req,false,false,false);
		if (count($res)>0)
			e(lang("speaker_still_exists"));
		else {
			$db->insert(tbl('speaker'),
					array('firstname','lastname','slug','photo'),
					array($firstname,$lastname,$_POST['slug'],'')
			 );

			$res=$db->select(tbl('speaker'),'id',$req,false,false,false);
			$id=$res[0]['id'];
			$desc=$_POST['description'];
			for ($i=0; $i<count($desc); $i++){
				$db->insert(tbl('speakerfunction'),
					array('description','speaker_id'),
					array($desc[$i],$id)
				);
			}
			e(lang("new_speaker_added"),"m");
			$_POST = '';
				
		}
	}
	
}

//error_reporting(E_ERROR & E_WARNING & E_STRING);
//ini_set('display_errors', True);
template_files('add_speaker.html',UL_SPEAKER_ADMIN_DIR);
?>
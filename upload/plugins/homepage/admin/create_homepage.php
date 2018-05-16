<?php
$userquery->admin_login_check();
$pages->page_redir();

require_once 'homepage_form.php';

define('SUB_PAGE', lang('chp_title'));
subtitle(lang('chp_createTitle'));
template_files('create_homepage.html', CHP_ADMIN_DIR);
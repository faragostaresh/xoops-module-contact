<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * Contact module
 *
 * @copyright   The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      Kazumi Ono (aka Onokazu)
 * @author      Trabis <lusopoemas@gmail.com>
 * @author      Hossein Azizabadi (AKA Voltan)
 * @version     $Id$
 */

include 'header.php';
$xoopsOption['template_main'] = 'contact_index.html';

include XOOPS_ROOT_PATH."/header.php";

global $xoopsConfig, $xoopsOption, $xoopsTpl, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger; 

$op = $contact_handler->Contact_CleanVars($_POST, 'op', 'form', 'string');
$department = $contact_handler->Contact_CleanVars($_GET, 'department', '', 'string');

switch($op) {
	case 'form': default:
	   $obj = $contact_handler->create();
		$form = $obj->Contact_ContactForm($department);
		$xoopsTpl->assign('form', $form->render());
		$xoopsTpl->assign('breadcrumb', '<a href="' . XOOPS_URL . '">' . _YOURHOME . '</a>  &raquo; ' . $xoopsModule->name());
		$xoopsTpl->assign('info', xoops_getModuleOption('contact_info','contact'));
		break;
	
	case 'save':
	
		if (empty($_POST['submit']) | !$GLOBALS['xoopsSecurity']->check()) {
			redirect_header ( XOOPS_URL, 3, _MD_CONTACT_MES_ERROR );
			exit();
		} else {
			
			// check captcha	
		   xoops_load("captcha");
			$xoopsCaptcha = XoopsCaptcha::getInstance();
			if ( !$xoopsCaptcha->verify() ) {
				redirect_header ( "javascript:history.go(-1)", 1, $xoopsCaptcha->getMessage() );
				exit();
			}
	      
	      // check email
	      if ( !$contact_handler->Contact_CleanVars($_POST, 'contact_mail', '', 'mail') ) {
			   redirect_header ( "javascript:history.go(-1)", 1, _MD_CONTACT_MES_NOVALIDEMAIL );
			   exit();
		   }
         
         // Info Processing
         $contact = $contact_handler->Contact_InfoProcessing($_POST);

         // insert in DB
	      if($saveinfo = true) {
				$obj = $contact_handler->create();
				$obj->setVars ( $contact );
				if(!$contact_handler->insert ( $obj )) {
					redirect_header ( "index.php", 3, _MD_CONTACT_MES_NOTSAVE );
					exit();
				}	
			}
			
			// send mail can seet message
			if($sendmail = true) {
				$message = $contact_handler->Contact_SendMail($contact);
			} else if ($saveinfo = true) {
				$message = _MD_CONTACT_MES_SAVEINDB;
			} else {
			   $message = _MD_CONTACT_MES_SENDERROR;
			}	
			
			redirect_header ( XOOPS_URL, 3, $message );
      }
      
		break;
}	

include XOOPS_ROOT_PATH."/footer.php";
?>
<?php
class GBloggerlistModuleFrontController extends ModuleFrontController
{
 public function initContent()
 {	 
	 parent::initContent();
 	 $this->module->init();	
	 $this->context->smarty->assign(
	 		array(
	 				'paginator' => $this->module->_Paginator,
	 				'gblogger_posts'=>$this->module->_Posts,
	 		)
	 );
	 $this->setTemplate('gblogger_list.tpl');
 }

}
<?php
class GBloggertagModuleFrontController extends ModuleFrontController
{
 public function initContent()
 {	 
	 parent::initContent();
 	 $this->module->init();	
	 $this->context->smarty->assign(
	 		array(
	 				'tag' => $this->module->_Tag,
	 				'paginator' => $this->module->_Paginator,
	 				'gblogger_posts'=>$this->module->_Posts,
	 		)
	 );
	 $this->setTemplate('gblogger_tag.tpl');
 }

}
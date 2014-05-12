<?php
class GBloggershowModuleFrontController extends ModuleFrontController
{
 public function initContent()
 {
	 parent::initContent();
	 $this->module->init();
	 $this->context->smarty->assign(
	 		array(
	 				'post' => $this->module->_Posts,
	 				'paginator' => $this->module->_Paginator,
	 				'gblogger_link_to_list' => $this->module->gblogger_link_to_list,
	 		)
	 );
	 $this->setTemplate('gblogger_show.tpl');
 }
}

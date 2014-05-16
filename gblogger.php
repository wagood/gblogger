<?php
/**
 * GBlogger module
 *
 * @author Wagood
 * @copyright Wagood
 *
 * @todo add search block
 * @todo add archive block
 * @todo update sitemap
 * @todo add comments
 * 
 * @todo changes for prestshop v.1.6 with bootstrap
 * @todo how it will work with multishop configuration?
 * 
 */

if (!defined('_PS_VERSION_'))
  exit;

require_once(_PS_MODULE_DIR_ . "gblogger/defines.php");

class GBlogger extends Module
{
    public $_Posts;
    public $_Paginator;
    public $_Tag;

  public function __construct()
  {
    $this->name = MODULE_NAME;
    $this->tab = 'front_office_features';
    $this->version = '0.1';
    $this->author = 'Wagood';
    $this->need_instance = 0;     
  
    parent::__construct();
  
    $this->displayName = $this->l('Google Blogger Bridge');
    $this->description = $this->l('Bridge between Google blogger service and Prestashop.');
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
  	
  	$this->gblogger_blog_id = null; 
  	$this->gblogger_maxResults = null;
  	$this->gblogger_maxResults_list = null;
  	$this->_bloggerServicePosts = null;
  	$this->gblogger_link_to_list = null;
   	$this->_Posts = null;
   	$this->_Paginator = null;
   	$this->_Tag = null;
   	$this->controller = null;	
  	$this->gblogger_last_use = -1; // 1 = left, 2 = right, -1 = disable
	$this->gblogger_tags_use = -1; // 1 = left, 2 = right, -1 = disable
	$this->gblogger_rewrite_use = 0; // 1 = enable, 0 = disable
	
	$this->initialize();
  }
  
  public function init() {  	
  	$this->controller = Tools::getValue('controller');
    $this->initDatas();
  }
  
  public function install()
  {  	 
  	if (!parent::install() || 
  			!$this->registerHook('leftColumn') ||
  			!$this->registerHook('rightColumn') ||
  			!$this->registerHook('header') ||
  			!$this->registerHook('actionHtaccessCreate') ||
  			!Configuration::updateValue('GBLOGGER_BLOG_ID', '') ||
  			!Configuration::updateValue('GBLOGGER_MAXRESULTS', '5') ||
  			!Configuration::updateValue('GBLOGGER_MAXRESULTS_LIST', '5') ||
  			!Configuration::updateValue('GBLOGGER_LAST_USE', '-1') ||  			
  			!Configuration::updateValue('GBLOGGER_TAGS_USE', '-1') ||
            !Configuration::updateValue('GBLOGGER_COMMENTS_USE', '-1') ||
            !Configuration::updateValue('GBLOGGER_REWRITE_USE', '0')
  	)
  		return false;
  	return true;
  }
  
  public function uninstall()
  {
  	return (parent::uninstall() && Configuration::deleteByName('GBLOGGER_BLOG_ID') 
  		&& Configuration::deleteByName('GBLOGGER_MAXRESULTS')
  		&& Configuration::deleteByName('GBLOGGER_MAXRESULTS_LIST')
  		&& Configuration::deleteByName('GBLOGGER_LAST_USE')
  		&& Configuration::deleteByName('GBLOGGER_TAGS_USE')
        && Configuration::deleteByName('GBLOGGER_COMMENTS_USE')
  		&& Configuration::deleteByName('GBLOGGER_REWRITE_USE')
  	);
  }

  public function initialize()
  {
  	require_once _PS_MODULE_DIR_ . 'gblogger/libs/google-api-php-client/src/Google_Client.php';
	require_once _PS_MODULE_DIR_ . 'gblogger/libs/google-api-php-client/src/contrib/Google_BloggerService.php';
	require_once _PS_MODULE_DIR_ . 'gblogger/libs/paginator/paginator.php';
	
	$this->gblogger_blog_id = Configuration::get('GBLOGGER_BLOG_ID');
	$this->gblogger_maxResults = (int)Configuration::get('GBLOGGER_MAXRESULTS');
	$this->gblogger_last_use = (int)Configuration::get('GBLOGGER_LAST_USE');
	$this->gblogger_tags_use = (int)Configuration::get('GBLOGGER_TAGS_USE');
    $this->gblogger_tags_use = (int)Configuration::get('GBLOGGER_COMMENTS_USE');
    $this->gblogger_rewrite_use = (int)Configuration::get('GBLOGGER_REWRITE_USE') & Configuration::get('PS_REWRITING_SETTINGS');
	
	$this->gblogger_link_to_list = $this->getLinkList();
	
		$client = new Google_Client();
		$client->setAccessType('online'); // default: offline
		$client->setApplicationName($this->name); //name of the application
		$client->setDeveloperKey(GOOGLE_API_KEY); // API key (at bottom of page)
		$_bloggerService = new Google_BloggerService($client);
		$this->_bloggerServicePosts = $_bloggerService->posts;		
  }
  
  public function initDatas() {
  	
  	$this->_Posts = NULL;
  	$this->_Paginator = NULL;
  	$this->_Tag = NULL;
  	
  	switch ($this->controller) {
  		case 'tag' :
  			// Check for internal rewrite rules
  			if (!$this->gblogger_rewrite_use) 
  				$text = Tools::getValue('text');
  			else {
  				$query = Tools::getValue('query');
  				// parse string like [tag].html
  				$_pattern = '/(.*).html/';

				if(preg_match($_pattern, $query, $matches))
					$text = $matches[1];  				
  			} 
  			  			
  			if (!isset($text) || empty($text))
  				break;
  			
  			$this->_Tag = $text;
  				
  		 	$gblogger_maxResults_list = (int)Configuration::get('GBLOGGER_MAXRESULTS_LIST');
  		 	
  		 	try {
	 			$data = $this->_bloggerServicePosts->listPosts($this->gblogger_blog_id, array('fetchBodies'=>false, 'labels'=>$text, 'fields' => 'items/id'));
  		 	} catch (Google_Exception $err) {}
  		 	
	 		$_total = sizeof($data->items);
	 
	 		$pages = new Paginator($gblogger_maxResults_list,'page');
	 		$pages->set_total( $_total );
	 
	 		$this->_Posts = array();
	 		$_start = $_finish = $pages->get_start();
	 		$_finish += $gblogger_maxResults_list;
	 
	 		for ($i=$_start;$i<$_finish&&$i<$_total;$i++){
	 			try {
	 				$post = $this->_bloggerServicePosts->get($this->gblogger_blog_id, $data->items[$i]->id);
	 				list($post->published, $s) = explode('T', $post->published);
	 				$post->url = $this->getLinkPost($post->id, $post->url);

	 				$post->tags = array();
	 				foreach ($post->labels as $label) 	 					
	 					$post->tags[] = array('link' => $this->getLinkTag($label),
	 										'name' => $label);	 					
	 	
	 				$this->_Posts[] = $post;
	 			} catch (Google_Exception $err) {}
	 		}
	 		$this->_Paginator = $pages->page_links($this->getLinkTag($text).($this->gblogger_rewrite_use?'?':'&'));
  			$this->context->smarty->assign('meta_title', $this->l('All pages with tag ').$this->_Tag);
  			$this->context->smarty->assign('meta_keywords', $this->_Tag);
	 	break;
  		case 'list' :
  		 	$gblogger_maxResults_list = (int)Configuration::get('GBLOGGER_MAXRESULTS_LIST');
  		 	
  		 	try {
	 			$data = $this->_bloggerServicePosts->listPosts($this->gblogger_blog_id, array('fetchBodies'=>false, 'fields' => 'items/id'));
  		 	} catch (Google_Exception $err) {}
  		 	
	 		$_total = sizeof($data->items);
	 
	 		$pages = new Paginator($gblogger_maxResults_list,'page');
	 		$pages->set_total( $_total );
	 
	 		$this->_Posts = array();
	 		$_start = $_finish = $pages->get_start();
	 		$_finish += $gblogger_maxResults_list;
	 
	 		for ($i=$_start;$i<$_finish&&$i<$_total;$i++){
	 			try {
	 				$post = $this->_bloggerServicePosts->get($this->gblogger_blog_id, $data->items[$i]->id);
	 				list($post->published, $s) = explode('T', $post->published);
	 				$post->url = $this->getLinkPost($post->id, $post->url);
                    $post->comments = $post->replies->totalItems;
	 				$post->tags = array();
	 				foreach ($post->labels as $label) 	 					
	 					$post->tags[] = array('link' => $this->getLinkTag($label),
	 										'name' => $label);	 					
	 	
	 				$this->_Posts[] = $post;
	 			} catch (Google_Exception $err) {}
	 		}
	 		$this->_Paginator = $pages->page_links($this->gblogger_link_to_list.($this->gblogger_rewrite_use?'?':'&'));	 			 		
  		break;
  		case 'show' :
  			// Check for internal rewrite rules
  			if (!$this->gblogger_rewrite_use) 
  				$id = Tools::getValue('id');
  			else {
  				$query = Tools::getValue('query');
  				// parse string like [id]-[linkrewrite].html
  				$_pattern = '/[0-9]+/';

				if(preg_match($_pattern, $query, $matches))
					$id = $matches[0];  				
  			} 
  			
	 		if (!isset($id) || empty($id))
	 			break;
	 		
	 		try {
	 			$post = $this->_bloggerServicePosts->get($this->gblogger_blog_id, $id);
	 			list($post->published, $s) = explode('T', $post->published);
	 			$post->tags = array();
	 			foreach ($post->labels as $label) 	 					
	 				$post->tags[] = array('link' => $this->getLinkTag($label),
	 									'name' => $label);	 					
	 			$this->_Posts = $post;
	 			$this->context->smarty->assign('meta_title', $post->title);
	 			$this->context->smarty->assign('meta_keywords', implode(',' , $post->labels));
	 			} catch (Google_ServiceException $err) {}
  		break;
  		default:
  		break;	
  	}  	 
  }

  public function getLinkList () 
  {
  	$url = $this->context->link->getModuleLink(MODULE_NAME, 'list');
  	if (!$this->gblogger_rewrite_use)
  		return $url;
  	
  	// parse something like http://site.com/module/gblogger/list
  	// to http://site.com/gblogger/list
  	$url = str_replace("module/", "", $url);
  	return $url;  	
  }
  
  public function getLinkTag ($label, $suffix = '.html')
  {
  	$url = $this->context->link->getModuleLink(MODULE_NAME, 'tag', array('text' => $label));
  	if (!$this->gblogger_rewrite_use)
  		return $url;
  	
  	// parse something like http://site.com/module/gblogger/tag?text=[tag]
  	// to http://site.com/gblogger/tag/[tag].html
  	$url = $this->context->link->getModuleLink(MODULE_NAME, 'tag/'.$label.$suffix);
  	$url = str_replace("module/", "", $url);
  	return $url;  	
  }
  
  public function getLinkPost ($id , $link_rewrite = null) 
  {
  	// $link_rewrite like http://blog.kpyto.asia/2014/03/7-e-ceros-motion-s.html
  	$url = $this->context->link->getModuleLink(MODULE_NAME, 'show', array('id' => $id));
  	if (!$this->gblogger_rewrite_use)
  		return $url;
  	
  	// parse something like http://site.com/module/gblogger/show?id=3465180581591682476
  	// to http://site.com/gblogger/show/3465180581591682476[-link_rewrite].html
  	if (isset($link_rewrite)) {
  		$link_rewrite = parse_url($link_rewrite);
  		$link_rewrite = pathinfo($link_rewrite['path']);
  		$link_rewrite = $link_rewrite['filename'];
  		$link_rewrite = '-'.$link_rewrite.'.html';
  	} else {
  		$link_rewrite = '.html';
  	}
  	
  	//$link_rewrite = isset($link_rewrite)?'-'.$link_rewrite.'.html':'.html';
  	$url = $this->context->link->getModuleLink(MODULE_NAME, 'show/'.$id.$link_rewrite);
  	$url = str_replace("module/", "", $url);
  	return $url;  	
  }
  
  public function createRewriteRule () {
  	// do nothing if SEO URL is not used   	
  	if (!$this->gblogger_rewrite_use) 
  		return false;
  	
  	$path = _PS_ROOT_DIR_.'/.htaccess';
  	 
  	// Check for old entry and delete all
  	if (!file_exists($path))
  		return false;
  	
  	$htaccess_content = file_get_contents($path);
  	  	 
  	$_pattern = "/#START ".MODULE_NAME."(.*?)#END ".MODULE_NAME."/s";
  	
  	if(preg_match_all($_pattern, $htaccess_content, $matches))
  	{
  		foreach ($matches[0] as $match)
  			$htaccess_content = str_replace($match, '' ,$htaccess_content);
  	}
  	   	 
  	// Create new entries
  	$htaccess = "\n";
	$htaccess .= "#START ".MODULE_NAME."\n"; 	  	
  	$htaccess .= "RewriteRule ^".MODULE_NAME."/(show|tag|search)/(.*) %{ENV:REWRITEBASE}index.php?fc=module&module=".MODULE_NAME."&controller=$1&query=$2 [QSA,L]\n";
  	$htaccess .= "RewriteRule ^".MODULE_NAME."/(list)?(.*) %{ENV:REWRITEBASE}index.php?fc=module&module=".MODULE_NAME."&controller=$1&$2 [QSA,L]\n";
	$htaccess .= "#END ".MODULE_NAME."\n";  	  
  	
  	$_pattern = 'RewriteRule ^api/?(.*)$ %{ENV:REWRITEBASE}webservice/dispatcher.php?url=$1 [QSA,L]';  	
  	$htaccess_content = str_replace($_pattern , $_pattern.$htaccess, $htaccess_content);

  	// create backup copy of .htaccess
  	@copy($path , $path.'.'.MODULE_NAME);
  	
  	// .htaccess is writeable?
  	if (!$write_fd = fopen($path, 'w'))
  		return false;
  	
  	// Write .htaccess data
  	@fwrite($write_fd, $htaccess_content);
  	@fclose($write_fd);
  	 
  }
  
  public function hookActionHtaccessCreate($params){
  	$this->createRewriteRule();
  }
  
  public function hookDisplayLeftColumn($params)
  {
  	$display = '';
    if ($this->gblogger_last_use == 1) {
  		$display .= $this->showBlockLast();
  	}
  	
  	if ($this->gblogger_tags_use == 1) {
  		$display .= $this->showBlockTags();
  	}
  	 
  	return $display;  	
  }
  
  public function hookDisplayRightColumn($params)
  {
  	$display = '';
    if ($this->gblogger_last_use == 2) {
  		$display .= $this->showBlockLast();
  	}
  	
  	if ($this->gblogger_tags_use == 2) {
  		$display .= $this->showBlockTags();
  	}
  	
  	return $display;
  }
    	
  public function showBlockLast()
  {
  	try {
  		$gblogger_data = $this->_bloggerServicePosts->listPosts($this->gblogger_blog_id, array('fetchBodies'=>false, 'maxResults'=>$this->gblogger_maxResults));
  	} catch (Google_Exception $err) {}
  	 
  	$this->_Posts = array();
  	foreach ($gblogger_data->items as $post) {
  		list($post->published, $s) = explode('T', $post->published);
  		$post->url = $this->getLinkPost($post->id, $post->url);
  		$this->_Posts[] = $post;
  	}
  	
  	$this->context->smarty->assign(
  			array(
  					'gblogger_posts' => $this->_Posts,
  					'gblogger_link_to_list' => $this->gblogger_link_to_list,
  			)
  	);
  	return $this->display(__FILE__, 'gblogger_last.tpl');
  }
  
  public function showBlockTags()
  {
  	try {
  		$data = $this->_bloggerServicePosts->listPosts($this->gblogger_blog_id, array('fetchBodies'=>false, 'fields' => 'items(id,labels)'));
  	} catch (Google_Exception $err) {}
  		 
  	$tags = array();
  	$max = $min = -1;
  	foreach ($data->items as $post)
  		foreach ($post->labels as $labels){
  		(!isset($tags[$labels]) || empty($tags[$labels]))? $tags[$labels] = 1:$tags[$labels]++;
  		if ($tags[$labels] > $max)
  			$max = $tags[$labels];
  		if ($tags[$labels] < $min || $min == -1)
  			$min = $tags[$labels];
  	}
  	$coef=($min == $max)?$max:(GBLOGGER_TAGS_MAX_LEVEL - 1) / ($max - $min);
  		 
  	$_tags = array();
  	foreach ($tags AS $label=>$times) {
  		$_tags[] = array('class' =>'gblogger_tag_level'.(int)(($times - $min) * $coef + 1), 
  						'name' => $label,
  						'link' => $this->getLinkTag($label)
  				);
  	}
  		 
  	$this->context->smarty->assign('tags', $_tags);
  		 
	return $this->display(__FILE__, 'gblogger_tags.tpl');
  } 
  
  public function hookDisplayHeader()
  {
  	$this->context->controller->addCSS($this->_path.'css/pagination.css', 'all');
  	if ($this->gblogger_tags_use > 0)
  		$this->context->controller->addCSS($this->_path.'css/tags.css', 'all');
  }

  public function getContent()
  {
  	global $currentIndex, $cookie;
  	$output = null;
  
  	if (Tools::isSubmit('submit'.$this->name))
  	{
  		$gblogger_blog_id = strval(Tools::getValue('GBLOGGER_BLOG_ID'));  		
  		if (!$gblogger_blog_id  || empty($gblogger_blog_id) || !Validate::isGenericName($gblogger_blog_id))
  			$output .= $this->displayError( $this->l('Invalid Configuration value GBLOGGER_BLOG_ID') );
  		else
  			Configuration::updateValue('GBLOGGER_BLOG_ID', $gblogger_blog_id);
  		
  		$gblogger_maxResults = intval(Tools::getValue('GBLOGGER_MAXRESULTS'));  		
  		if (!$gblogger_maxResults  || empty($gblogger_maxResults) || !Validate::isInt($gblogger_maxResults))
  			$output .= $this->displayError( $this->l('Invalid Configuration value GBLOGGER_MAXRESULTS') );
  		else
  			Configuration::updateValue('GBLOGGER_MAXRESULTS', $gblogger_maxResults);

  		$gblogger_maxResults_list = intval(Tools::getValue('GBLOGGER_MAXRESULTS_LIST'));
  		if (!$gblogger_maxResults_list  || empty($gblogger_maxResults_list) || !Validate::isInt($gblogger_maxResults_list))
  			$output .= $this->displayError( $this->l('Invalid Configuration value GBLOGGER_MAXRESULTS_LIST') );
  		else
  			Configuration::updateValue('GBLOGGER_MAXRESULTS_LIST', $gblogger_maxResults_list);

  		$gblogger_last_use = intval(Tools::getValue('GBLOGGER_LAST_USE'));
  		if (!isset($gblogger_last_use) || empty($gblogger_last_use ) || !Validate::isInt($gblogger_last_use))
  			$output .= $this->displayError( $this->l('Invalid Configuration value GBLOGGER_LAST_USE') );
  		else
  			Configuration::updateValue('GBLOGGER_LAST_USE', $gblogger_last_use );

  		$gblogger_tags_use = intval(Tools::getValue('GBLOGGER_TAGS_USE'));
  		if (!isset($gblogger_tags_use) || empty($gblogger_tags_use ) || !Validate::isInt($gblogger_tags_use))
  			$output .= $this->displayError( $this->l('Invalid Configuration value GBLOGGER_TAGS_USE') );
  		else
  			Configuration::updateValue('GBLOGGER_TAGS_USE', $gblogger_tags_use );

        // validate and update GBLOGGER_COMMENTS_USE values
        $gblogger_comments_use = intval(Tools::getValue('GBLOGGER_COMMENTS_USE'));
        if (!Validate::isBool($gblogger_comments_use))
            $output .= $this->displayError( $this->l('Invalid Configuration value GBLOGGER_COMMENTS_USE') );
        else
            Configuration::updateValue('GBLOGGER_COMMENTS_USE', $gblogger_comments_use );

  		$gblogger_rewrite_use = intval(Tools::getValue('GBLOGGER_REWRITE_USE'));
  		if (!Validate::isBool($gblogger_rewrite_use))
  			$output .= $this->displayError( $this->l('Invalid Configuration value GBLOGGER_REWRITE_USE') );
  		else {
  			Configuration::updateValue('GBLOGGER_REWRITE_USE', $gblogger_rewrite_use );
  			if($gblogger_rewrite_use) {
  				$this->gblogger_rewrite_use = (int)Configuration::get('GBLOGGER_REWRITE_USE') & Configuration::get('PS_REWRITING_SETTINGS');
  				$this->createRewriteRule();
  			}
  		}
  		$output .= $this->displayConfirmation($this->l('Settings updated'));
  	}
  	return $output.$this->displayForm();
  }
  
  public function displayForm()
  {
  	$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
  
  	$_use = array(
  					array('id_option' => -1, 'name' => $this->l('Disabled')),
  					array('id_option' => 1, 'name' => $this->l('Show in the LEFT block')),
  					array('id_option' => 2, 'name' => $this->l('Show in the RIGHT block')),
  			);

      $_enable = array(
  					array('id_option' => 0, 'name' => $this->l('Disabled')),
  					array('id_option' => 1, 'name' => $this->l('Enabled')),
  			);
  	
  	$fields_form[0]['form'] = array(
  			'legend' => array(
  					'title' => $this->l('Gblogger Settings'),
  			),
  			'input' => array(
  					array(
  							'type' => 'text',
  							'label' => $this->l('Google blogger ID value'),
  							'name' => 'GBLOGGER_BLOG_ID',
  							'size' => 25,
  							'required' => true
  					),
  					array(
  							'type' => 'text',
  							'label' => $this->l('Maximum number of posts in preview'),
  							'name' => 'GBLOGGER_MAXRESULTS',
  							'size' => 3,
  							'required' => true
  					),
  					array(
  							'type' => 'text',
  							'label' => $this->l('Maximum number of posts in list'),
  							'name' => 'GBLOGGER_MAXRESULTS_LIST',
  							'size' => 3,
  							'required' => true
  					),
  					array(
  							'type' => 'select',
  							'label' => $this->l('Show Block with Last posts'),
  							'name' => 'GBLOGGER_LAST_USE',
  							'required' => true,
  							'options' => array(
  									'query' => $_use,
  									'id' => 'id_option',
  									'name' => 'name',
  							)
  					),
					array(
  							'type' => 'select',
  							'label' => $this->l('Show Block with Tags list'),
  							'name' => 'GBLOGGER_TAGS_USE',
  							'required' => true,
  							'options' => array(
  									'query' => $_use,
  									'id' => 'id_option',
  									'name' => 'name',
  							)
  					),
                array(
                    'type' => 'select',
                    'label' => $this->l('Show Comments'),
                    'name' => 'GBLOGGER_COMMENTS_USE',
                    'required' => true,
                    'options' => array(
                        'query' => $_enable,
                        'id' => 'id_option',
                        'name' => 'name',
                    )
                ),
  					array(
  							'type' => 'select',
  							'label' => $this->l('URL Rewriting'),
  							'name' => 'GBLOGGER_REWRITE_USE',
  							'required' => true,
  							'options' => array(
  									'query' => $_enable,
  									'id' => 'id_option',
  									'name' => 'name',
  							)
				  						
  					)
			),  		
  			'submit' => array(
  					'title' => $this->l('Save'),
  					'class' => 'button'
  			)
  	);
  
  	$helper = new HelperForm();
  
  	// Module, t    oken and currentIndex
  	$helper->module = $this;
  	$helper->name_controller = $this->name;
  	$helper->token = Tools::getAdminTokenLite('AdminModules');
  	$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
  
  	// Language
  	$helper->default_form_language = $default_lang;
  	$helper->allow_employee_form_lang = $default_lang;
  
  	// Title and toolbar
  	$helper->title = $this->displayName;
  	$helper->show_toolbar = true;        // false -> remove toolbar
  	$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
  	$helper->submit_action = 'submit'.$this->name;
  	$helper->toolbar_btn = array(
  			'save' =>
  			array(
  					'desc' => $this->l('Save'),
  					'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
  					'&token='.Tools::getAdminTokenLite('AdminModules'),
  			),
  			'back' => array(
  					'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
  					'desc' => $this->l('Back to list')
  			)
  	);
  
  	// Load current value
  	$helper->fields_value['GBLOGGER_BLOG_ID'] = Configuration::get('GBLOGGER_BLOG_ID');
  	$helper->fields_value['GBLOGGER_MAXRESULTS'] = Configuration::get('GBLOGGER_MAXRESULTS');
  	$helper->fields_value['GBLOGGER_MAXRESULTS_LIST'] = Configuration::get('GBLOGGER_MAXRESULTS_LIST');
  	$helper->fields_value['GBLOGGER_LAST_USE'] = Configuration::get('GBLOGGER_LAST_USE');
  	$helper->fields_value['GBLOGGER_TAGS_USE'] = Configuration::get('GBLOGGER_TAGS_USE');
    $helper->fields_value['GBLOGGER_COMMENTS_USE'] = Configuration::get('GBLOGGER_COMMENTS_USE');
    $helper->fields_value['GBLOGGER_REWRITE_USE'] = Configuration::get('GBLOGGER_REWRITE_USE');

  	 
  	return $helper->generateForm($fields_form);
  }
  
}
?>
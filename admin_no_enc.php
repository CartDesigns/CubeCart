<?php
if(!defined('CC_DS')) die('This file can not be accessed directly.');

//=====[ Classes ]====================================================================================================

final class CubeCart_Licence {
	private $_debug;
	private $_errors		= false;
	private $_key_data		= false;
	private $_key_file		= 'includes/extra/key.php';
	private $_licence_key	= false;
	private $_messages		= false;
	private $_secret_key	= false;
	private $_fallback		= false;

	final public function __construct($licence_key = null, $secret_key, &$errors, &$debug) {
		$this->_errors		=& $errors;
		$this->_licence_key	= $licence_key;
		$this->_secret_key	= $secret_key;

		if (isset($_GET['keydel'])) {
			$this->delete_key();
		}
		$key = (file_exists($this->_key_file)) ? file_get_contents($this->_key_file, false) : $this->fetch_key();
		$this->_key_data = $this->read_key($key);
	}
	final public function __destruct() {
	}
	final public function data() {
		return $this->_key_data;
	}

	final private function check_hash($hash = null, $contents = null) {
		if (!empty($hash) && !empty($contents)) {
			if ($hash === hash_hmac('whirlpool', $contents, $this->_secret_key)) {
				return true;
			}
		}
		return false;
	}
	final public function delete_key() {
		if (!empty($this->_key_file) && file_exists($this->_key_file)) {
			clearstatcache();
			return (bool)unlink($this->_key_file);
		}
		return false;
	}
	final private function fetch_key() {
		global $glob;

		$licence_server = ($this->_fallback) ? 'cp2.cubecart.com' : 'cp.cubecart.com';

		$data	= array(
			'domain'	=> $_SERVER['SERVER_NAME'],
			'licence'	=> $this->_licence_key,
			'ip_address'=> $_SERVER['SERVER_ADDR'],
			'version'	=> '5.0.0'
		);

		$request_data	= http_build_query($data, null, '&');
		$request = new Request($licence_server, '/licence/authenticate');
		$request->setUserAgent('CubeCart');
		$request->skiplog(true);
		$request->setSSL();
		$request->setData($request_data);
		$response = $request->send();

		if($response) {
			$this->write_key($response);
			$this->send_stats();
			return $response;
		} elseif(!$this->_fallback) {
			$this->_fallback = true;
			$this->fetch_key();
		} else {
			$this->_errors[0] = 'Unable to contact licensing server.';
			return false;
		}

	}
	final public function messages() {
		return (!empty($this->_messages)) ? $this->_messages : false;
	}
	final private function read_key($input = null) {
		if (!empty($input)) {
			$input	= str_replace("\n", '', $input);
			if (preg_match('#^\-{5}BEGIN PUBLIC KEY\-{5}(.*)\-{5}END PUBLIC KEY\-{5}#', $input, $match)) {
				$result	= base64_decode($match[1]);
				$hash	= substr($result, 0, 128);
				$data	= substr($result, 128);
			} else {
				$hash	= null;
				$data	= base64_decode($input);
			}

			if (is_null($hash) || $this->check_hash($hash, $data)) {
				try {
					$xml	= new SimpleXMLElement(base64_decode($data));
					if (isset($xml->messages) && !$GLOBALS['session']->has('licence-messages')) {
						foreach ($xml->messages->message as $message) {
							$this->_messages[] = (string)$message;
						}
					}
					if (isset($xml->errors)) {
						foreach ($xml->errors->error as $error) {
							$this->_errors[(int)$error->attributes()->code] = (string)$error;
						}
						$this->delete_key();
					} else {
						if ($this->_licence_key === (string)$xml->{'licence-key'}) {
							$data	= array(
								'licence-created'	=> (string)$xml->{'licence-created'},
								'licence-expires'	=> (string)$xml->{'licence-expires'},
								'licence-update-at'	=> (string)$xml->{'licence-update-at'},
							);
							if (isset($xml->{'allowed-domains'})) {
								foreach ($xml->{'allowed-domains'}->domain as $domain) {
									$domains[] = preg_replace('#^www\.#i', '',$domain);
								}
								if (!in_array(preg_replace('#^www\.#i', '', $_SERVER['SERVER_NAME']), $domains)) {
									$this->_errors[4] = 'Domain does not match those permitted.';
									$this->delete_key();
									return false;
								}
							}
							if ($data['licence-update-at'] <= gmdate('Y-m-d H:i:s')) {
								return $this->read_key($this->fetch_key());
							} else {
								$this->write_key($input);
								return $data;
							}
						} else {
							// Licence key has been changed - Reauthorize the software
							return $this->read_key($this->fetch_key());
						}
					}
				} catch (Exception $e) {
					$this->_errors[1] = 'Unable to read key data';
				}
			} else {
				$this->_errors[3] = 'Key hash is incorrect. File may have been tampered with.';
				$this->delete_key();
			}
		}
		return false;
	}

	final private function write_key($contents = null) {
		if (!empty($contents)) {
			if (file_put_contents($this->_key_file, $contents)) {
				return true;
			} else {
				$this->_errors[2] = 'Unable to write key file. Please make sure the includes/extra folder is writable.';
			}
		}
		return false;
	}
	final private function send_stats() {

		###// Locale
		$country = getCountryFormat($GLOBALS['config']->get('config', 'store_country'), 'numcode', 'iso');
		$state = (is_numeric($GLOBALS['config']->get('config', 'store_zone'))) ? getStateFormat($GLOBALS['config']->get('config', 'store_zone'), 'id', 'name') : $GLOBALS['config']->get('config', 'store_zone');

		###// inventory
		$customers 	= $GLOBALS['db']->count('CubeCart_customer','customer_id');
		$products 	= $GLOBALS['db']->count('CubeCart_inventory','product_id');
		$documents 	= $GLOBALS['db']->count('CubeCart_documents','doc_id');
		$categories = $GLOBALS['db']->count('CubeCart_category','cat_id');

		###// sales
		$pending_sales = $GLOBALS['db']->query('SELECT SUM(`total`) as `total_sales` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` = 1;');
		$processed_sales = $GLOBALS['db']->query('SELECT SUM(`total`) as `total_sales` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary` WHERE `status` IN (2,3);');
		$total_sales = $GLOBALS['db']->query('SELECT SUM(`total`) as `total_sales` FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_order_summary`;');
		$orders = $GLOBALS['db']->count('CubeCart_order_summary','cart_order_id');

		###// modules
		$modules = $GLOBALS['db']->select('CubeCart_modules',array('folder','module'),array('status'=>1),'`module`, `folder` ASC');

		$install_date = $GLOBALS['db']->select('CubeCart_history','MIN(time) AS `install_date`');
		
		###// Collect anonymous usage stats #####
		$xml = new XML(true);
		$xml->startElement('storestats',array('identifier' => md5($GLOBALS['storeURL'])));
			$xml->writeElement('install_date',$install_date[0]['install_date']);
			$xml->writeElement('version',CC_VERSION);
			$xml->startElement('locale');
				$xml->writeElement('country',$country);
				$xml->writeElement('state',$state);
			$xml->endElement();
			$xml->startElement('inventory');
				$xml->writeElement('customers',$customers);
				$xml->writeElement('products',$products);
				$xml->writeElement('documents',$documents);
				$xml->writeElement('categories',$categories);
			$xml->endElement();
			$xml->startElement('sales');
				$xml->writeElement('currency',$GLOBALS['config']->get('config', 'default_currency'));
				$xml->writeElement('orders',$orders);
				$xml->startElement('turnover');
					$xml->writeElement('processed',$processed_sales[0]['total_sales']);
					$xml->writeElement('pending',$pending_sales[0]['total_sales']);
					$xml->writeElement('total',$total_sales[0]['total_sales']);
				$xml->endElement();
			$xml->endElement();
			if($modules) {
			$xml->startElement('modules');
				$pastmodule = null;
				foreach($modules as $module) {
					if(is_null($pastmodule) && $module['module']!==$pastmodule) {
						$xml->startElement($module['module']);
					} elseif(isset($pastmodule) && $module['module']!==$pastmodule) {
						$xml->endElement();
						$xml->startElement($module['module']);
					}
					$xml->writeElement($module['folder'],'true');
					$pastmodule = $module['module'];
				}
				$xml->endElement();
			$xml->endElement();
			}
		$xml->endElement();

		$request_data = "encdata=".$xml->getDocument();
		$request = new Request('merchstats.cubecart.com', '/post/index.php');
		$request->setUserAgent('CubeCart');
		$request->skiplog(true);
		$request->setSSL();
		$request->setData($request_data);
		$request->send();
	}
}

//=====[ Functions ]====================================================================================================

/**
 * Check branding
 *
 * @param bool $htmlout
 * @return string
 */
function branding($htmlout) {
	global $glob;
	
	## Decide if copyright remains or not
	if (preg_match("#^([0-9]{6})+[-]+([0-9])+[-]+([0-9]{4})$#", $GLOBALS['config']->get('config','lkv'))) {
		$copyRightBody	= '';
		$copyRightTitle	= '';
		$copyRightLogo 	= '<span id="logo"><a href="?"><img src="images/general/px.gif" alt="'.$GLOBALS['language']->account['title_acp'].'" width="156" height="31" /></a></span>';
		$copyRightLogoLrg = '';
		$htmlout = str_replace('CubeCart Version', 'Script Version', $htmlout);
	} else {
		$copyRightBody	= '<div style="text-align: center; margin: 10px; font-size: 80%;"><a href="http://www.cubecart.com" target="_blank" title="eCommerce Software by CubeCart">eCommerce Software</a> by CubeCart<br />Copyright Devellion Limited '.date('Y', time()).'. All rights reserved.</div>';
		$copyRightTitle	= ' - (Powered by CubeCart)';
		$copyRightLogo = '<span id="logo"><a href="?"><img src="'.$glob['adminFolder'].'/skins/default/images/cc_logo.png" alt="'.$GLOBALS['language']->account['title_acp'].'" /></a></span>';
		$copyRightLogoLrg 	= '<a href="?"><img src="'.$glob['adminFolder'].'/skins/default/images/ccAdminLogoLrg.png" alt="eCommerce Software by CubeCart" /></a>';
	}
	$htmlout = preg_replace(
		array('/(\<\/body\>)/i','/(\<\/title\>)/i','/(\<div id="header"\>)/i','/(\<div id="logo"\>)/i'),
		array($copyRightBody.'$1', $copyRightTitle.'$1', '$1'.$copyRightLogo, '$1'.$copyRightLogoLrg),
		$htmlout
	);
	return $htmlout;
}

//=====[ Load ]====================================================================================================
include 'controllers'.CC_DS.'controller.admin.pre_session.inc.php';

###// BEGIN LICENCE VALIDATION #####
$errors = $debug_data = '';
$software_license_key = trim($GLOBALS['config']->get('config', 'license_key'));

if(!empty($software_license_key)) {

	$licence = new CubeCart_Licence($software_license_key, '79890de1999faee9717c5b8acedd3e47', $errors, $debug_data);
	$messages = $licence->messages();	

}

if (!empty($errors)) {
	$random_string = md5(rand(0,99));
		
	if(isset($_POST['new_licence_key'])) {
		$GLOBALS['config']->set('config','license_key',preg_replace('/[^[:alnum:]-]/','',$_POST['new_licence_key']));
		
		if(empty($_POST['new_licence_key'])) {
			$GLOBALS['gui']->setNotify('Your software licence key has been removed. You are now using CubeCart Lite with restrictions.');
		} else {
			$GLOBALS['gui']->setNotify('Your software licence key has been changed.');
		}
		httpredir('?'.$random_string);
	}

	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Sat, 19 Sep 2009 00:00:00 GMT');
	foreach ($errors as $code => $error) {
		$vars[] = array('message' => $error);
	}
	$GLOBALS['smarty']->assign('RANDOM_STRING', $random_string);
	$GLOBALS['smarty']->assign('ERRORS', $vars);
	$GLOBALS['smarty']->assign('LICENSE_KEY', $GLOBALS['config']->get('config', 'license_key'));
	$GLOBALS['smarty']->display('templates/licence.error.php');
	exit;
}
unset($licence);

// Validate CRK
if(isset($_POST['lkv']) && $GLOBALS['config']->get('config','lkv') !== $_POST['lkv']) {
	$data	= array(
		'domain'		=> $_SERVER['SERVER_NAME'],
		'copyright_key'	=> $_POST['lkv'],
		'ip_address'	=> $_SERVER['SERVER_ADDR'],
		'version'		=> '5.0.0'
	);
	$request_data	= http_build_query($data, null, '&');
	$request = new Request('cp.cubecart.com', '/licence/authenticate');
	$request->setUserAgent('CubeCart');
	$request->skiplog(true);
	$request->setSSL();
	$request->setData($request_data);
	$response = $request->send();

	if($response) {
		$response = json_decode($response,true);
		if(isset($response['success']) && !empty($response['success'])) {
			// Set post var to add it to config reload
			$_POST['config']['lkv'] = $response['success'];
			$GLOBALS['gui']->setNotify('Copyright removal key has been assigned successfully.');
		} else {
			$GLOBALS['gui']->setError($response['fail']);
		}
	} else {
		$GLOBALS['gui']->setError('Failed to connect to licensing server to validate copyright removal key.');
	}
} elseif(isset($_POST['lkv'])) {
	$_POST['config']['lkv'] = $_POST['lkv'];
}

#################################################

if (!empty($messages) && !$GLOBALS['session']->has('licence-messages')) {
	foreach ($messages as $message) {
		$GLOBALS['gui']->setNotify($message);
	}
	$GLOBALS['session']->set('licence-messages', true);
	unset($messages);
}

if(empty($software_license_key)) {
	
	$order_limit = 250;
	$customer_limit = 100;
	$administrator = 1;
	
	$limit['orders'] = $GLOBALS['db']->count('CubeCart_order_summary', 'cart_order_id');
	$limit['customers'] = $GLOBALS['db']->count('CubeCart_customer', 'customer_id');
	$limit['administrators'] = $GLOBALS['db']->count('CubeCart_admin_users', 'admin_id');
	
	$install_source = $GLOBALS['config']->get('config', 'install_source');
	$purchase_url = empty($install_source) ? 'http://www.cubecart.com/pricing' : 'http://www.cubecart.com/r/'.$install_source;
		
	$GLOBALS['smarty']->assign('TRIAL_LIMITS', array('orders' => $order_limit, 'customers' => $customer_limit, 'administrator' => $administrator, 'url' => $purchase_url));

} 

$feed_access_key = $GLOBALS['config']->get('config','feed_access_key');
$feed_access_key = (!$feed_access_key) ? '' : $feed_access_key;

if (Admin::getInstance()->is() || ($_GET['_g']=='products' && $_GET['node']=='export' && !empty($_GET['format']) && $_GET['access']==$feed_access_key && !empty($feed_access_key))) {
	if(empty($software_license_key)) {
		
		if($GLOBALS['config']->get('config','skin_folder') !== $GLOBALS['config']->get('config','skin_folder_mobile')) {
			$GLOBALS['config']->set('config','skin_folder_mobile',$GLOBALS['config']->get('config','skin_folder'));
			$GLOBALS['config']->set('config','skin_style_mobile',$GLOBALS['config']->get('config','skin_style'));
		}
		
		if($_GET['_g'] !== 'settings' && ($limit['orders'] > $order_limit || $limit['customers'] > $customer_limit || $limit['administrators']>$administrator)) {
			$_GET['_g'] = 'upgrade';
			$_GET['node'] = 'index';
			$GLOBALS['gui']->setNotify("You have exceeded the maximum amount of customers/orders/administrators to continue using CubeCart Lite.");
		} else {
			switch($_GET['_g']) {
				case 'statistics':
				case 'reports':
					$_GET['_g'] = 'upgrade';
				break;
				case 'modules':
					if(in_array($_GET['type'],array('social','plugins'))) {
						$GLOBALS['db']->update('CubeCart_modules',array('status' => 0),'`module` IN (\'social\',\'plugins\')');
						$_GET['_g'] = 'upgrade';
					}
				break;
				case 'settings':
				case 'products':
					if(in_array($_GET['node'],array('giftCertificates','coupons','export','import','hooks'))) {
						$_GET['_g'] = 'upgrade';
						unset($_GET['node']);
					}
					if($_GET['node'] == 'giftCertificates') {
						$gc = $GLOBALS['config']->get('gift_certs');
						$gc['status'] = 0;
						$GLOBALS['config']->set('gift_certs', '', $gc);
					}
					if($_GET['node']=='admins' && $_GET['action'] == 'add') {
						$GLOBALS['gui']->setNotify("Only one store administrator is allowed in CubeCart Lite.");
						$_GET['_g'] = 'upgrade';
						unset($_GET['node']);
					}
				break;
			}
			if($_GET['_g'] == 'upgrade') {
				$GLOBALS['gui']->setNotify("Sorry but the feature you requested is not available in CubeCart Lite. Please enter a software license key to unlock all features.");
			}
		}
	} elseif($GLOBALS['config']->get('config','skin_folder') == $GLOBALS['config']->get('config','skin_folder_mobile')) {
		$GLOBALS['config']->set('config','skin_folder_mobile','mobile');
		$GLOBALS['config']->set('config','skin_style_mobile','blue');
	}
	include 'controllers'.CC_DS.'controller.admin.session.true.inc.php';
} else {
	include 'controllers'.CC_DS.'controller.admin.session.false.inc.php';
	$htmlout = $GLOBALS['smarty']->fetch('templates/'.$global_template_file['session_false']);
	echo branding($htmlout);
	exit;
}
// Render the completed page
if (!isset($suppress_output) || !$suppress_output) {
	$GLOBALS['gui']->displayCommon(true);
	$htmlout = $GLOBALS['smarty']->fetch('templates/'.$global_template_file['session_true']);
		
	echo (isset($_GET['_g']) && $_GET['_g']=='documents') ? $htmlout : branding($htmlout);
}
<?php
/**
* To change this license header, choose License Headers in Project Properties
*  
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://app.betaout.com for more information.
* 
*  @author    betaout;
*  @copyright 2010-2017 Betaout SA
*  @license   https://app.betaout.com
*/

if (!defined('_PS_VERSION_')) {
    exit();
}

class BetaOut extends Module
{
    /* Put your code here  */

    protected $error = false;

    public function __construct()
    {
        $this->initContext();
        $this->name = 'betaout';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.5';
        $this->author = 'betaout';
        $this->bootstrap = true;
        $this->module_key = '47643b9631806e9e73e6a609c7ba78ec';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->_current_dir = dirname(__FILE__);
        

        parent::__construct();

        $this->displayName = $this->l('Beta Out');
        $this->description = $this->l('This is the module related to BetaOut.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');
    }

    public function install()
    {
        return (parent::install() && $this->registerHook('productfooter') &&
                $this->registerHook('actionCartSave') &&
                $this->registerHook('actionAuthentication') &&
                $this->registerHook('createaccount') &&
                $this->registerHook('orderConfirmation') &&
                $this->registerHook('backBeforePayment') &&
                $this->registerHook('productTab') &&
                $this->registerHook('header') &&
                $this->registerHook('actionBeforeCartUpdateQty'));
    }

    private function initContext()
    {
        if (class_exists('Context')) {
            $this->context = Context::getContext();
        }
    }

    public function getContent()
    {
        $html = '';

        /*
         * Get the properties from the module, like the Api Key and the Priject ID;
         */
        if (Tools::isSubmit('submitBetaoutData')) {
            $project_id=(int)Tools::getValue('PS_BETAOUT_PROJECTID');
            if ($project_id>9999 && $project_id<100000) {
            
                Configuration::updateValue('PS_BETAOUT_PROJECTID', Tools::getValue('PS_BETAOUT_PROJECTID'));
                if (Tools::strlen(Tools::getValue('PS_BETAOUT_APIKEY'))==42) {
                    Configuration::updateValue('PS_BETAOUT_APIKEY', Tools::getValue('PS_BETAOUT_APIKEY'));
                    $html .= $this->displayConfirmation($this->l('Configuration Updated'));
                } else {
                    $html .= $this->displayError($this->l('Configuration Not Update'));
                    $html .= $this->displayError($this->l('Check API KEY Must Be IN String 42 Charector'));
                }
            } else {
                $html .= $this->displayError($this->l('Configuration Not Update'));
                $html .= $this->displayError($this->l('Check Project ID Must Be IN Integer 5 Number'));
            }
        }

        $html .= $this->displayBetaout();
        $html .= $this->renderForm();
        
        return $html;
    }

    private function displayBetaout()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    

    /*
     * Set the Form for the module, like the icon and the title input Api key and project ID;
     */

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('ProjectId'),
                        'name' => 'PS_BETAOUT_PROJECTID',
                        'desc' => $this->l('Please enter your projectId.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('APIKEY'),
                        'name' => 'PS_BETAOUT_APIKEY',
                        'desc' => $this->l('Please enter your apikey.'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        if (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')) {
            $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        } else {
            $helper->allow_employee_form_lang = 0;
        }
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBetaoutData';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->currentIndex .= '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $t_value = Tools::getValue('PS_BETAOUT_PROJECTID', Configuration::get('PS_BETAOUT_PROJECTID'));
        return array(
            'PS_BETAOUT_PROJECTID' => $t_value,
            'PS_BETAOUT_APIKEY' => Tools::getValue('PS_BETAOUT_APIKEY', Configuration::get('PS_BETAOUT_APIKEY'))
        );
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    /*
     * Implement HookHeader Function;
     * Set the properties of the betaout module in header like Identify;
     * Set head.tpl and identifyusers.tpl;
     */

    public function hookHeader($params)
    {
        $add_id=Address::getFirstCustomerAddressId((int)$this->context->customer->id);
        $phone=new Address($add_id);
        $smarty = $this->context->smarty;
        $cookie = $this->context->cookie;
        $project_id = Configuration::get('PS_BETAOUT_PROJECTID');
        $api_key = Configuration::get('PS_BETAOUT_APIKEY');
        $smarty->assign('projectId', $project_id);
        $smarty->assign('apiKey', $api_key);
        if (!empty($this->context->customer->email)) {
            $cookie->__set('_bOutEm', $this->context->customer->email);
        }
        $smarty->assign('undefind', $params);
        $smarty->assign('email', $this->context->customer->email);
        $smarty->assign('c_id', $this->context->customer->id);
        $smarty->assign('phone', $phone->phone_mobile);
        $betaout_head = $this->display(__FILE__, 'head.tpl'). $this->display(__FILE__, 'identifyusers.tpl');
        
        if ($this->context->customer->logged) {
            $this->betaoutIdentify($phone->phone_mobile, $cookie);
        }
        return $betaout_head;
    }
   
    public function betaoutIdentify($phone, $cookie)
    {
        $identity=array("customer_id"=>$this->context->customer->id,
                        "email"=>$this->context->customer->email);
        if (!empty($phone)) {
            $identity=  array_merge($identity, array("phone"=>$phone));
        }
       
        $info=  Tools::jsonEncode($identity);
       
        $cookie->__set('_ampIdent', $info);
    }

    /*
     * Implement HookOrderConfirmation Function;
     * Get detail's of order, Like total product, Order Id, Price, Coopan, etc;
     * Send detail on betaout via CURL;
     */

    public function hookorderConfirmation($params)
    {
        $cookie = $this->context->cookie;
        $order = $params['objOrder'];
        $cookies = $params['cookie'];
        $products = $params['objOrder']->getProducts();
        $productarray = array();
        $currency = new CurrencyCore($cookies->id_currency);

        $i = $j = 0;

        // Get Total Product's and Product's Detail;

        foreach ($products as $value) {

            $id_image = Product::getCover($value['product_id']);
            // get Image by id;
            if (count($id_image) > 0) {
                $image = new Image($id_image['id_image']);
                // get image full URL;
                $image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
            }
            $productarray[$i]['name'] = $value['product_name'];
            $productarray[$i]['sku'] = $value['product_id'];
            $productarray[$i]['price'] = $value['price'];
            $productarray[$i]['id'] = $value['product_id'];
            $productarray[$i]['quantity'] = $value['product_quantity'];
            $productarray[$i]['categories'] = $this->getCategoryNamesByProduct($value['product_id'], true);
            $productarray[$i]['image_url'] = $image_url;

            $i++;
        }

        $discount = $order->getDiscounts();
        $coopan_name = '';
                
        foreach ($discount as $dis) {
            $coopan_name = $coopan_name . $dis['name'] . ', ';
            $j++;
        }

        //Get Order Detail;

        $orderinfo = array('order_id' => $order->id,
            'total' => $order->total_paid,
            'shipping' => $order->total_shipping,
            'tax' => $order->total_paid_tax_incl - $order->total_paid_tax_excl,
            'discount' => $order->total_discounts_tax_excl,
            'revenue' => $order->total_paid_tax_excl,
//            'shoppingCartNo'=>$this->context->cart->id,
            'coupon' => $coopan_name,
            'status' => $order->current_state,
            'payment_method' => $order->module,
            'currency' => $currency->iso_code
        );

        $parray = array('products' => $productarray,
                            'identifiers'=>  $this->betaoutGetIdentification(),
                            'order_info' => $orderinfo,
                            'activity_type' => 'purchase'
                        );
        $default = $this->betaoutGetDefaults();
        $betaoutparams = array_merge($default, $parray);
        $requesturl = 'https://api.betaout.com/v2/ecommerce/activities';
        $this->betaoutMakeRequest($requesturl, $betaoutparams);

        $cookie->__set('xlcn_consumable_cart', null);
    }

    /*
     * Implement hookActionBeforeCartUpdateQty Function;
     * Track Cart Update and Add To Cart Event;
     */

    public function hookactionBeforeCartUpdateQty($params)
    {
        $cookie = $this->context->cookie;
        $this->context->smarty->assign('undefind', $params);
        $cookie->__set('xlcn_betaout_cart', true);
    }

    /*
     * Implement hookActionCartSave Function;
     * Track Cart Event Like Add to cart, Removed From cart, Total Product's In cart;
     * Track Cart Update;
     */

    public function hookActionCartSave($params)
    {
        $cookie = $this->context->cookie;
        $cart = $params['cart'];
        $cookies = $params['cookie'];
        $product_id = $productarray = $pro_qty = $padd = $productd = array();
        if (is_object($cart)) {
            $currency = new CurrencyCore($cookies->id_currency);

            $products = $cart->getProducts();
            $cartinfo = array('total' => $cart->getOrderTotal(),
                    'revenue'=>$cart->getOrderTotal(),
                    'currency'=>$currency->iso_code);
            foreach ($products as $value) {
                $i = $value['id_product'];
                $id_image = Product::getCover($value['id_product']);
                // get Image by id;
                if (count($id_image) > 0) {
                    $image = new Image($id_image['id_image']);
                    // get image full URL;
                    $image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
                }


                $productarray[$i]['name'] = $value['name'];
                $productarray[$i]['sku'] = $value['id_product'];
                $productarray[$i]['price'] = $value['price'];
                $productarray[$i]['id'] = $value['id_product'];
                $productarray[$i]['categories'] = $this->getCategoryNamesByProduct($value['id_product'], true);
                $productarray[$i]['quantity'] = $value['cart_quantity'];
                
                array_push($product_id, $value['id_product']);
                $pro_qty[$i] = $value['cart_quantity'];
            }

            $prev_cookie = $cookie->__get('xlcn_consumable_cart');
            $cookie->__set('xlcn_consumable_cart', implode(',', $product_id));

            $added = array_diff($product_id, explode(',', $prev_cookie));

            $parray = explode(',', $prev_cookie);
            if ($cookie->__get('xlcn_consumable_cart') == null) {
                 $carray = null;
            } else {
                $carray =  explode(',', $cookie->__get('xlcn_consumable_cart'));
            }

            if ((count($parray)) > (count($carray))) {
                $deleted = array_diff(explode(',', $prev_cookie), $product_id);
            } else {
                $deleted = array();
            }
                
            $adcount = count($added);
            $deletecount = count($deleted);

            // Track Add to Cart Event;
            if ($adcount) {
                foreach ($added as $id) {
                    $padd[$id] = $productarray[$id];
                    $parray = array('products' => $padd,
                            'identifiers'=>  $this->betaoutGetIdentification(),
                            'cart_info' => $cartinfo,
                            'activity_type' => 'add_to_cart'
                        );

                    $default = $this->betaoutGetDefaults();
                    $betaoutparams = array_merge($default, $parray);
                    $requesturl = 'https://api.betaout.com/v2/ecommerce/activities';
                    $this->betaoutMakeRequest($requesturl, $betaoutparams);
                }
            }

            //Track Removed From Cart Detail;
            //Get Delated Product Detail;

            if ($deletecount && !empty($prev_cookie)) {

                foreach ($deleted as $id) {
                    $product_object = new Product((int) $id, false, 1);
                    $i = $id;
                    $id_image = Product::getCover($id);
                    // get Image by id;
                    if (count($id_image) > 0) {
                        $image = new Image($id_image['id_image']);
                        // get image full URL;
                        $image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
                    }

                    $productd[$i]['name'] = $product_object->name;
                    $productd[$i]['sku'] = $product_object->id;
                    $productd[$i]['price'] = $product_object->price;
                    $productd[$i]['id'] = $product_object->id;
                    $productd[$i]['categories'] = $this->getCategoryNamesByProduct($id, true);
                    $productd[$i]['image_url'] = $image_url;
                    $productd[$i]['quantity'] = $product_object->quantity;

                    $parray = array('products' => $productd,
                            'identifiers'=>  $this->betaoutGetIdentification(),
                            'cart_info' => $cartinfo,
                            'activity_type' => 'remove_from_cart'
                        );
                    $default = $this->betaoutGetDefaults();
                    $betaoutparams = array_merge($default, $parray);
                    $requesturl = 'https://api.betaout.com/v2/ecommerce/activities';
                    $this->betaoutMakeRequest($requesturl, $betaoutparams);
                }
            }

            //Track Update Cart Event;
            //Send current cart Status;
            $flage = false;
            if (!empty($prev_cookie) && ($cookie->__get('xlcn_betaout_cart') == true)) {
                $flage = true;
            }

            if ($adcount == 0 && $deletecount == 0 && $flage) {
                $pq = array();
                $pq = unserialize($cookie->__get('pro_qty'));
                foreach ($products as $value) {
                    if ($value['cart_quantity'] != $pq[$value['id_product']]) {
                        $padd[$value['id_product']] = $productarray[$value['id_product']];
                        $parray = array('products' => $productarray,
                            'identifiers'=>  $this->betaoutGetIdentification(),
                            'cart_info' => $cartinfo,
                            'activity_type' => 'update_cart'
                        );

                        $default = $this->betaoutGetDefaults();
                        $betaoutparams = array_merge($default, $parray);
                        $requesturl = 'https://api.betaout.com/v2/ecommerce/activities';
                        $this->betaoutMakeRequest($requesturl, $betaoutparams);
                    }
                }
            }

            $cookie->__set('pro_qty', serialize($pro_qty));
            $cookie->__set('xlcn_betaout_cart', false);
        }
    }

    /*
     * Implement hookProductTab Function;
     * Track View Product Event;
     * Send View product detail on betaout vie curl;
     */

    public function hookProductTab($params)
    {
        $cookie = $this->context->cookie;
        $value = $params['product'];
        $productarray = array();
        $i = 0;
        //ExportO;
        $currency = new CurrencyCore($cookie->id_currency);
        if (is_object($value)) {

            $id_image = Product::getCover($value->id);
            // get Image by id;
            if (count($id_image) > 0) {
                $image = new Image($id_image['id_image']);
                // get image full URL;
                $image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.jpg';
            }

            $productarray[$i]['name'] = $value->name;
            $productarray[$i]['sku'] = $value->id;
            $productarray[$i]['price'] = $value->price;
            $productarray[$i]['id'] = $value->id;
            $productarray[$i]['currency'] = $currency->iso_code;
            $productarray[$i]['categories'] = $this->getCategoryNamesByProduct($value->id, true);
            $productarray[$i]['specialPrice'] = $value->price;
            $productarray[$i]['status'] = $value->available_for_order;
            $productarray[$i]['image_url'] = $image_url;

            $parray = array('products' => $productarray,
                'identifiers' => $this->betaoutGetIdentification(),
                'activity_type'=>'view' );
            $default = $this->betaoutGetDefaults();

            $betaoutparams = array_merge($default, $parray);
            $requesturl = 'https://api.betaout.com/v2/ecommerce/activities';
            $this->betaoutMakeRequest($requesturl, $betaoutparams);
        }
    }

    /*
     * Get product Category in Array;
     */

    private function getCategoryNamesByProduct($id, $array = true)
    {
        
          $c_categorie = Product::getProductCategoriesFull($id, $this->context->cookie->id_lang);
        if (!is_array($c_categorie)) {
            if ($array) {
                return array();
            } else {
                return '[]';
            }
        }
        $_categories = array_reverse($c_categorie);
        $categories = array();
        if ($array) {
            foreach ($_categories as $key) {
                $ncategory = new Category($key['id_category'], $this->context->cookie->id_lang);
                if ($ncategory->is_root_category) {
                    array_push($categories, array(
                      'cat_name' =>$key['name']!=null?$key['name']:"",
                      'cat_id' => $key['id_category']!=null?$key['id_category']:"",
                      'parent_cat_id' => 0
                      ));
                } else {
                    array_push($categories, array(
                      'cat_name' => $key['name'],
                      'cat_id' => $key['id_category'],
                      'parent_cat_id' => $ncategory->id_parent
                        ));
                }
            }
        } else {
            $categories = '[';
            $c = 0;
            foreach ($c_categorie as $category) {
                $c++;
                 $categories .= '"' . $category['n'] . '",';
                if ($c == 5) {
                    break;
                }
            }
             $categories = rtrim($categories, ',');
             $categories .= ']';
        }

        return $categories;
    }

    public function getIdentity()
    {
        $mo = new Mobile_Detect();
        $md = $mo->getHttpHeaders();
        $ar = explode(';', $md['HTTP_COOKIE']);
        $det = array();
        foreach ($ar as $val) {
            $record1 = explode('=', $val);
            $det[trim($record1[0])] = trim($record1[1]);
        }
        return $det;
    }

    public function betaoutGetIdentification()
    {
        $cookie = $this->context->cookie;
        $id = array();
        $id = $this->getIdentity();
        $array=array();
        if (!empty($cookie->__get('_ampIdent'))) {
            $ident=  Tools::jsonDecode($cookie->__get('_ampIdent'), true);
            $array=  array_merge($array, array("customer_id"=>$ident['customer_id'],
               "email"=>$ident['email']));
            if (!empty($ident['phone'])) {
                $array=  array_merge($array, array("phone"=>$ident['phone']));
            }
         
        } else {
            $array=  array_merge($array, array("token"=>  isset($id['_ampUITN'])?$id['_ampUITN']:""));
        }
        return $array;
    }
    public function betaoutGetDefaults()
    {
        $properties = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'useragent' => $_SERVER['HTTP_USER_AGENT']
        );

        $properties = array_merge($properties, array(
            'apiKey' => Configuration::get('PS_BETAOUT_APIKEY'),
            'project_id' => Configuration::get('PS_BETAOUT_PROJECTID'),
        ));
        // Let other modules alter the defaults.
        return $properties;
    }

    /*
     * Implement CURL Function for send data on betaout;
     */

    public function betaoutMakeRequest($requesturl, $params)
    {
        try {
            $data_string = Tools::jsonEncode($params);

            $ch = curl_init($requesturl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
        } catch (Exception $e) {
            return $e;
        }

        return $result;
    }
}

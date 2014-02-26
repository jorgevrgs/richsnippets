<?php
    /*
     * 2007-2012 PrestaShop
     *
     * NOTICE OF LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to license@prestashop.com so we can send you a copy immediately.
     *
     * DISCLAIMER
     *
     * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
     * versions in the future. If you wish to customize PrestaShop for your
     * needs please refer to http://www.prestashop.com for more information.
     *
     *  @author PrestaShop SA <contact@prestashop.com>
     *  @copyright  2007-2012 PrestaShop SA
     *  @version  Release: $Revision: 6844 $
     *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     *  International Registered Trademark & Property of PrestaShop SA
     */
    
    if (!defined('_PS_VERSION_'))
    exit;
    
    class RichSnippets extends PaymentModule
    {
        public function __construct()
        {
            $this->name = 'richsnippets';
            $this->tab = 'seo';
            $this->author = 'jorgevrgs';
            $this->version = '0.1';
            $this->need_instance = 1;
            $this->module_key = '';
            
            parent::__construct();
            
            $this->displayName = 'Rich Snippets';
            $this->description = 'Add meta data for rich snippets';
            
            $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
            
            $this->ps_versions_compliancy = array('min' => '1.5.0.1', 'max' => '1.6.0.0');
            
            //$this->dependencies = array('blockcart');
            
            $this->initContext();
        }
        
        //Install
        public function install()
        {
            if (Shop::isFeatureActive())
                Shop::setContext(Shop::CONTEXT_ALL);
            
            return (parent::install() AND
                    $this->registerHook('header') AND
                    $this->registerHook('productfooter')
                    );
        }
        
        
        // Retrocompatibility 1.4/1.5
        private function initContext()
        {
            if (class_exists('Context'))
                $this->context = Context::getContext();
            else
            {
                global $smarty, $cookie;
                $this->context = new StdClass();
                $this->context->smarty = $smarty;
                $this->context->cookie = $cookie;
            }
        }
        
        //Uninstall
        public function uninstall()
        {
            if (!parent::uninstall())
                return false;
            return true;
        }
        
        public function displayBlock($display = 'header')
        {
            $id_lang = $this->context->language->id;
            $id_shop = $this->context->shop->id;
            $link = $this->context->link;
            
            if ($link->protocol_content == 'https://')
                $rs_base_dir = 'https://';
            else
                $rs_base_dir = 'http://';

            
            $product = new Product((int)Tools::getValue('id_product'), false, $id_lang, $id_shop);
            
            if (Validate::isLoadedObject($product))
            {
                $manufacturer = new Manufacturer($product->id_manufacturer, $id_lang);
                
                //image of the product
                $id_image = Product::getCover((int)Tools::getValue('id_product'));
                if (sizeof($id_image) > 0) {
                    $image = new Image($id_image['id_image']);
                }

                $this->context->smarty->assign(array(
                                                     'product' => $product,
                                                     'image' => $image,
                                                     'manufacturer' => $manufacturer,
                                                     ));
                // Muestra archivo tpl
                if ($display == 'header')
                    return $this->display(__FILE__, 'header.tpl');
                elseif ($display == 'footerproduct')
                        return $this->display(__FILE__, 'footerproduct.tpl');
            }
            else
            {
               /* $rs_logo = array();
                $rs_logo = array(
                                 'facebook' => array(
                                                     'large' => $this->_path.'img/logo-facebook-600-315.jpg',
                                                     'thumbnail' => $this->_path.'img/logo-facebook-500-500.jpg',
                                                     ),
                                 'twitter' => array(
                                                    'large' => $this->_path.'img/logo-twitter-500-500.jpg',
                                                    'thumbnail' => $this->_path.'img/logo-twitter-120-120.jpg',
                                                    ),
                                 );

                $this->context->smarty->assign(array(
                                                     'rs_logo' => $rs_logo,
                                                     ));*/
                return $this->display(__FILE__, 'header.tpl');
            }

        }
        
        public function hookDisplayHeader($params)
        {
            return $this->displayBlock('header');
        }
        
        public function HookDisplayFooterProduct($params)
        {
            return $this->displayBlock('footerproduct');
        }
        
    }
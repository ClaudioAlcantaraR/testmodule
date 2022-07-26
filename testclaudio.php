<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    PrestaShop SA <claudio.dev29@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Testclaudio extends Module
{
    /* Requisitos de prestashop para que funcione el modulo */

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'testclaudio';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Claudio Alcantara';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Campo extra en el formulario de registro y verificación de edad mayor a 18');
        $this->description = $this->l('Prueba tecnica para Bengala Spain: Claudio Alcántara');

        $this->confirmUninstall = $this->l('¿Estas seguro que quieres desinstalar este modulo?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /* Registramos todos los ganchos cuando instalamos */

    public function install()
    {
        Configuration::updateValue('TESTCLAUDIO_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('additionalCustomerFormFields') &&
            $this->registerHook('actionObjectCustomerUpdateAfter') &&
            $this->registerHook('actionObjectCustomerAddAfter') &&
            $this->modifyCustomerTable();
    }

    /* Registramos la funcion que elimina nuestra tabla en la BD */

    public function uninstall()
    {
        Configuration::deleteByName('TESTCLAUDIO_LIVE_MODE');

        return parent::uninstall() &&
        $this->uninstallModifyCustomerTable();
    }

    /* Modificamos la tabla ps_customer para añadir una nueva tabla llamada `extra_value` que guardará el valor de nuestro campo extra */

    protected function modifyCustomerTable()
    {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'customer` ADD `extra_value` text');
        return true;    
    }

    /* Configuramos que se elimina nuestra tabla para evitar conflictos cuando el módulo vuelva a instalarse */

    private function uninstallModifyCustomerTable()
    {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'customer` DROP `extra_value`');
        return true;
    }

    protected function readModuleValues()
    {
        /* Buscamos el id de cada usuario en la BD con el objeto getContext() */

        $id_customer = Context::getContext()->customer->id;

        /* 
        * Consultamos la BBDD y concatenamos el id dinamico
        * Retornamos el enlace a la BD usando la alternativa _PS_USE_SQL_SLAVE_ ya que la consulta es de lectura (read).
        */

        $sql = 'SELECT c.`extra_value`'.' FROM `'. _DB_PREFIX_.'customer` c '.' WHERE c.id_customer = '.(int)$id_customer;
        return  Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    protected function writeModuleValues($id_customer)
    {
        /**
        * Utilizando el método Tools::getValue recuperamos el valor de extra_value y lo almacenamos en una variable
        * Realizamos la consulta sql para modificar la tabla customer con nuestro nuevo campo según se vaya insertando
        */
        $extra_value = Tools::getValue('extra_value');
        $sql = 'UPDATE `'._DB_PREFIX_.'customer` c '.' SET  c.`extra_value` = "'.pSQL($extra_value).'"'.' WHERE c.id_customer = '.(int)$id_customer;
        Db::getInstance()->execute($sql);
    }

    /**
    * Gancho: AdditionalCustomerFormFields. 
    */

    public function hookAdditionalCustomerFormFields($params)
    {
        $module_fields = $this->readModuleValues();
        $extra_value_service = Tools::getValue('extra_value');
 
        if (isset($module_fields['extra_value']))
        {
            $extra_value_service = $module_fields['extra_value'];
        }

        // Configuramos y cremos el nuevo campo 
        $extra_fields = array();
        $extra_fields['extra_value'] = (new FormField)
            ->setName('extra_value')
            ->setType('text')
            ->setValue($extra_value_service)
            ->setLabel($this->l('Campo extra'));

        return $extra_fields;
    }

    // Actualizamos los datos  
    public function hookactionObjectCustomerUpdateAfter($params)
    {
        $id = (int)$params['object']->id;
        $this->writeModuleValues($id);
    }

    // Obtención de los datos ( $_POST ) y guardar en la BD.
    public function hookactionObjectCustomerAddAfter($params)
    {
        $id = (int)$params['object']->id;
        $this->writeModuleValues($id);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitTestclaudioModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTestclaudioModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'TESTCLAUDIO_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'TESTCLAUDIO_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'TESTCLAUDIO_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'TESTCLAUDIO_LIVE_MODE' => Configuration::get('TESTCLAUDIO_LIVE_MODE', true),
            'TESTCLAUDIO_ACCOUNT_EMAIL' => Configuration::get('TESTCLAUDIO_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'TESTCLAUDIO_ACCOUNT_PASSWORD' => Configuration::get('TESTCLAUDIO_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
<?php
/*
  $Id: payment.php,v 1.37 2003/06/09 22:26:32 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class Payment {
    public $modules, $selected_module;

// class constructor
    public function __construct($module = '') 
    {
        global $PHP_SELF;

        if (defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED)) {

            $this->modules = explode(';', MODULE_PAYMENT_INSTALLED);
            $include_modules = [];

            if (tep_not_null($module) && in_array($module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) {
                $this->selected_module = $module; 
                $include_modules[] = array('class' => $module, 'file' => $module . '.php');

            } else {
                foreach ($this->modules as $value) {
                    $class = substr($value, 0, strrpos($value, '.'));
                    $include_modules[] = array('class' => $class, 'file' => $value);
                }
            }

            foreach ($include_modules as $include) { 
                include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $include['file']);
                include(DIR_WS_MODULES . 'payment/' . $include['file']);
                $GLOBALS[$include['class']] = new $include['class']; 
            }

// if there is only one payment method, select it as default because in
// checkout_confirmation.php the $_SESSION['payment'] variable is being assigned the
// $_POST['payment'] value which will be empty (no radio button selection possible)
            if (tep_count_payment_modules() == 1 && (!isset($GLOBALS[$_SESSION['payment']]) || (isset($GLOBALS[$_SESSION['payment']]) && !is_object($GLOBALS[$_SESSION['payment']])))) {
                $_SESSION['payment'] = $include_modules[0]['class']; 
            }

            if (tep_not_null($module) && in_array($module, $this->modules) && isset($GLOBALS[$module]->form_action_url) ) {
                $this->form_action_url = $GLOBALS[$module]->form_action_url;
            }
        }
    }

// class methods
/* The following method is needed in the checkout_confirmation.php page
   due to a chicken and egg problem with the payment class and order class.
   The payment modules needs the order destination data for the dynamic status
   feature, and the order class needs the payment module title.
   The following method is a work-around to implementing the method in all
   payment modules available which would break the modules in the contributions
   section. This should be looked into again post 2.2.
*/
    public function update_status()
    {
        if (is_array($this->modules)) {
            if (is_object($GLOBALS[$this->selected_module])) {
                if (method_exists($GLOBALS[$this->selected_module], 'update_status')) {
                    $GLOBALS[$this->selected_module]->update_status();
                }
            }
        }
    }

    public function javascript_validation()
    {
        $js = '';
        if (is_array($this->modules)) {
            $js = '<script type="text/javascript">' . "\n"
            . 'function check_form() {' . "\n"
            . '  var error = 0;' . "\n"
            . '  var error_message = "' . JS_ERROR . '";' . "\n"
            . '  var payment_value = null;' . "\n"
            . '  if (document.checkout_payment.payment.length) {' . "\n"
            . '    for (var i=0; i<document.checkout_payment.payment.length; i++) {' . "\n"
            . '      if (document.checkout_payment.payment[i].checked) {' . "\n"
            . '        payment_value = document.checkout_payment.payment[i].value;' . "\n"
            . '      }' . "\n"
            . '    }' . "\n"
            . '  } else if (document.checkout_payment.payment.checked) {' . "\n"
            . '    payment_value = document.checkout_payment.payment.value;' . "\n"
            . '  } else if (document.checkout_payment.payment.value) {' . "\n"
            . '    payment_value = document.checkout_payment.payment.value;' . "\n"
            . '  }' . "\n"
            . "\n";

            foreach ($this->modules as $value) {
                $class = substr($value, 0, strrpos($value, '.'));
                if ($GLOBALS[$class]->enabled) {
                    $js .= $GLOBALS[$class]->javascript_validation();
                }
            }

            $js .= "\n" 
            . '  if (payment_value == null) {' . "\n"
            . '    error_message += "' . JS_ERROR_NO_PAYMENT_MODULE_SELECTED . '\\n";' . "\n"
            . '    error = 1;' . "\n"
            . '  }' . "\n\n"
            . '  if (document.checkout_payment.conditions.checked == false) {' . "\n"
            . '    error = 1;' . "\n"
            . '    error_message += "' . ERROR_CONDITIONS_NOT_ACCEPTED . '\n"; ' . "\n"
            . '  }' . "\n"
            . '  if (error == 1) {' . "\n"
            . '    alert(error_message);' . "\n"
            . '    return false;' . "\n"
            . '  } else {' . "\n"
            . '    return true;' . "\n"
            . '  }' . "\n"
            . '}' . "\n"
            . '</script>' . "\n";
        }

        return $js;
    }

    public function selection()
    {
        $selection_array = [];

        if (is_array($this->modules)) {
            foreach ($this->modules as $value) {
                $class = substr($value, 0, strrpos($value, '.'));
                if ($GLOBALS[$class]->enabled) {
                    $selection = $GLOBALS[$class]->selection();
                    if (is_array($selection)) {
                        $selection_array[] = $selection;
                    }
                }
            }
        }
        return $selection_array;
    }

    public function pre_confirmation_check()
    {
        if (is_array($this->modules)) {
            if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
                $GLOBALS[$this->selected_module]->pre_confirmation_check();
            }
        }
    }

    public function confirmation()
    {
        if (is_array($this->modules)) {
            if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
                return $GLOBALS[$this->selected_module]->confirmation();
            }
        }
    }

    public function process_button()
    {
        if (is_array($this->modules)) {
            if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
                return $GLOBALS[$this->selected_module]->process_button();
            }
        }
    }

    public function before_process()
    {
        if (is_array($this->modules)) {
            if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
                return $GLOBALS[$this->selected_module]->before_process();
            }
        }
    }

    public function after_process()
    {
        if (is_array($this->modules)) {
            if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
                return $GLOBALS[$this->selected_module]->after_process();
            }
        }
    }

    public function get_error()
    {
        if (is_array($this->modules)) {
            if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
                return $GLOBALS[$this->selected_module]->get_error();
            }
        }
    }
}

<?php
/*
  $Id: MessageStack.php,v 1.1 2003/05/19 19:45:42 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License

  Example usage:

  $messageStack = new MessageStack();
  $messageStack->add('general', 'Error: Error 1', 'error');
  $messageStack->add('general', 'Error: Error 2', 'warning');
  if ($messageStack->size('general') > 0) echo $messageStack->output('general');
*/

class MessageStack extends TableBox
{
    private $messages = [];

    public function __construct()
    {

        if (isset($_SESSION['messageToStack']) && is_array($_SESSION['messageToStack'])) {
            for ($i=0, $n=sizeof($_SESSION['messageToStack']); $i<$n; $i++) {
                $this->add($_SESSION['messageToStack'][$i]['class'], $_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
            }
            unset($_SESSION['messageToStack']);
        }
    }

    private function reset()
    {
        $this->messages = [];
    }

// class methods
    public function add($class, $message, $type = 'error')
    {
        if ($type == 'error') {
            $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => tep_image(DIR_WS_ICONS . 'error.gif', ICON_ERROR) . '&nbsp;' . $message);
        } elseif ($type == 'warning') {
            $this->messages[] = array('params' => 'class="messageStackWarning"', 'class' => $class, 'text' => tep_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . '&nbsp;' . $message);
        } elseif ($type == 'success') {
            $this->messages[] = array('params' => 'class="messageStackSuccess"', 'class' => $class, 'text' => tep_image(DIR_WS_ICONS . 'success.gif', ICON_SUCCESS) . '&nbsp;' . $message);
        } else {
            $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => $message);
        }
    }

    public function add_session($class, $message, $type = 'error')
    {
        if (!isset($_SESSION['messageToStack'])) {
            $_SESSION['messageToStack'] = [];
        }
        $_SESSION['messageToStack'][] = array('class' => $class, 'text' => $message, 'type' => $type);
    }

    public function output($class)
    {
        $this->set_param('data_parameters', 'class="messageBox"');
        $output = [];
        for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
            if ($this->messages[$i]['class'] == $class) {
                $output[] = $this->messages[$i];
            }
        }
        return $this->get_box($output);
    }

    public function size($class=false)
    {
        if ($class==false) {
            $count = sizeof($this->messages);
        } else {
            $count = 0;
            foreach ($this->messages as $message) {
                if ($message['class'] == $class) {
                    $count++;
                }
            }
        }
        return $count;
    }
}

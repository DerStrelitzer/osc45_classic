<?php
/*
  $Id: MessageStack.php,v 1.6 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

  Example usage:

  $messageStack = new MessageStack();
  $messageStack->add('Error: Error 1', 'error');
  $messageStack->add('Error: Error 2', 'warning');
  if ($messageStack->size > 0) echo $messageStack->output();
*/

class MessageStack extends TableBlock
{
    public 
        $size = 0,
        $errors = [];

    public function __construct()
    {
        if (isset($_SESSION['messageToStack']) && is_array($_SESSION['messageToStack'])) {
            foreach (array_keys($_SESSION['messageToStack']) as $key) {
                $this->add($_SESSION['messageToStack'][$key]['text'], $_SESSION['messageToStack'][$key]['type']);
            }
            unset($_SESSION['messageToStack']);
        }
    }

    public function add($message, $type = 'error')
    {
        if ($type == 'error') {
            $this->errors[] = [
                'params' => 'class="messageStackError"', 
                'text' => tep_image(DIR_WS_ICONS . 'error.gif', ICON_ERROR) . '&nbsp;' . $message
            ];
        } elseif ($type == 'warning') {
            $this->errors[] = [
                'params' => 'class="messageStackWarning"', 
                'text' => tep_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . '&nbsp;' . $message
            ];
        } elseif ($type == 'success') {
            $this->errors[] = [
                'params' => 'class="messageStackSuccess"', 
                'text' => tep_image(DIR_WS_ICONS . 'success.gif', ICON_SUCCESS) . '&nbsp;' . $message
            ];
        } else {
            $this->errors[] = [
                'params' => 'class="messageStackError"', 
                'text' => $message
            ];
        }
        $this->size++;
    }

    public function add_session($message, $type = 'error')
    {
        if (!isset($_SESSION['messageToStack'])) {
            $_SESSION['messageToStack'] = [];
        }
        $_SESSION['messageToStack'][] = [
            'text' => $message, 
            'type' => $type
        ];
    }

    private function reset()
    {
        $this->errors = [];
        $this->size = 0;
    }

    public function output()
    {
        $this->set_param('data_parameters', 'class="messageBox"');
        return $this->get_block($this->errors);
    }
}

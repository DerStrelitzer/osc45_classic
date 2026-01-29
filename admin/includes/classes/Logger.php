<?php
/*
  $Id: Logger.php,v 1.3 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class Logger
{
    public $timer_start, $timer_stop, $timer_total;

// class constructor
    public function __construct()
    {
        $this->timer_start();
    }

    public function timer_start()
    {
        if (defined("PAGE_PARSE_START_TIME")) {
            $this->timer_start = PAGE_PARSE_START_TIME;
        } else {
            $this->timer_start = microtime();
        }
    }

    public function timer_stop($display = 'nein')
    {
        $this->timer_stop = microtime();

        $time_start = explode(' ', $this->timer_start);
        $time_end = explode(' ', $this->timer_stop);

        $this->timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

        $this->write(getenv('REQUEST_URI'), $this->timer_total . 's');

        if ($display == 'ja') {
            return $this->timer_display();
        }
    }

    public function timer_display()
    {
        return '<span class="smallText">Parse Time: ' . $this->timer_total . 's</span>';
    }

    public function write($message, $type)
    {
        error_log(date(DATE_TIME_FORMAT) . ' [' . $type . '] ' . $message . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }
}

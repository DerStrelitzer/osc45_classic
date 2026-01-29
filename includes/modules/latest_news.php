<?php
/*
  latest_news.php v1.1.4 (by J0J0)
  Mod by Ingo <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2005 osCommerce
  Copyright (c) 2002 Will Mays

  Released under the GNU General Public License
*/

if (!function_exists('eval_buffer')) {
    function eval_buffer($string)
    {
        ob_start();
        eval("$string[2];");
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
}

if (!function_exists('eval_print_buffer')) {
    function eval_print_buffer($string)
    {
        ob_start();
        eval("print $string[2];");
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
}

if(!function_exists('eval_html')) {
    function eval_html($string)
    {
        $string = preg_replace_callback("/(<\?=)(.*?)\?>/si", "eval_print_buffer", $string);
        return preg_replace_callback("/(<\?php|<\?)(.*?)\?>/si", "eval_buffer", $string);
    }
}

$latest_news_query = tep_db_query('SELECT news_id, headline, content, date_added from ' . TABLE_LATEST_NEWS . " WHERE status = '1' and language = '" . (int)$GLOBALS['languages_id']. "' ORDER BY date_added DESC LIMIT " . LATEST_NEWS_MAX_DISPLAY);

if (tep_db_num_rows($latest_news_query)) { 
?>
<!-- latest_news //-->
<?php

    $contents = [
        [
            [
                'params' => 'width="100%" class="infoBoxHeading"',
                'text' => TABLE_HEADING_LATEST_NEWS
            ]
        ]
    ];
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);
    
    $news_box_contents = [];
    $row = 0;
    while ($latest_news = tep_db_fetch_array($latest_news_query)) {

        $news_box_contents[$row] = [
            'align'  => 'left',
            'params' => 'class="smallText" valign="top"',
            'text'   => '<a href="' . tep_href_link(FILENAME_NEWS, 'news_id='.$latest_news['news_id']) .'#newsid'. $latest_news['news_id'] .'">' .
                    '<b>' . eval_html($latest_news['headline']) . '</b> - <i>' . tep_date_long($latest_news['date_added']) . '</i></a><br />' . nl2br(eval_html($latest_news['content'])) . '<br />'
        ];

        $row++;
    }

    $content = new TableBox();
    $content->set_param('cellpadding', 4);
    $content->set_param('parameters', 'class="infoBoxContents"');
    $content_box_contents = [['text' => $content->get_box($news_box_contents)]];
          
    $content_box = new TableBox();
    $content_box->set_param('cellpadding', 1);
    $content_box->set_param('parameters', 'class="infoBox"');
    $content_box->get_box($content_box_contents, true);
?>
<!-- latest_news_eof //-->
<?php
}

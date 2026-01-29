<?php
/*
  latest_news.php v1.1.4i by Ingo <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/


$latest_news_query = tep_db_query("SELECT news_id, headline, date_added, content from " . TABLE_LATEST_NEWS . " WHERE status = '1' and language = '". (int)$GLOBALS['languages_id']. "' order by date_added desc limit 1");

if (tep_db_num_rows($latest_news_query)) {
?>
<!-- latest_news_box //-->
          <tr>
            <td>
<?php

    $contents = [
        [
            [
                'params' => 'width="100%" class="infoBoxHeading"',
                'text' => '<a href="' . tep_href_link(FILENAME_NEWS) . '">' . TABLE_HEADING_LATEST_NEWS . '</a>'
            ]
        ]
    ];
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);

    $news_box_text = '';
    while ($latest_news = tep_db_fetch_array($latest_news_query)) {
        if ($news_box_text != '') {
            $news_box_text .= '<br /><br />';
        }
        $news_box_text  .= '<a href="' . tep_href_link(FILENAME_NEWS, 'news_id='.$latest_news['news_id']) .'#newsid'. $latest_news['news_id'] .'">'
                  . '<b>'.tep_date_short($latest_news['date_added']) .'</b><br />' . "\n"
                  . strip_tags($latest_news['headline']) . '</a><br />' . "\n"
                  . ingo_cut_description($latest_news['content'], LATEST_NEWS_BOX_LENGTH) . "\n";

    }
    //$news_box_text = '<marquee direction="up" scrollAmount="2" onmouseover="scrollAmount=0" onmouseout="scrollAmount=2" height="' . floor(BOX_WIDTH*1.2) .'"><div align="center">' .
    //$news_box_text . '</div></marquee>';

    $info_box_contents = [
        [
            'text' => $news_box_text
        ]
    ];

    new InfoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- latest_news_box //-->
<?php
}

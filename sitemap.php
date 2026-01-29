<?php
/*
  $Id: sitemap.php 2005/09/29 by Ingo

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(basename($PHP_SELF), '', 'NONSSL'));

$categorytree = new CategoryTree;

$this_head_include = '<style type="text/cass">
ul {margin-top:0;margin-bottom:0;padding:0 0 0 15px;}
li {margin:0;padding:0;}
li:hover {background-color:#ccc}
.paddinglr {padding: 2px 10px;}
</style>';
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$heading_image = 'table_background_browse.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_INFORMATION; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td> 
          <div class="divbox">
            <table cellspacing="0" cellpadding="0" border="0" width="100%">
              <tr>
                <td width="50%" class="main paddinglr" valign="top"><?php echo $categorytree->buildtree(); ?></td>
                <td width="50%" class="main paddinglr" valign="top">
                  <ul>
                    <li><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . HEADER_TITLE_MY_ACCOUNT . '</a>'; ?>

                      <ul>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . ACCOUNT_INFORMATION . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . ACCOUNT_ADDRESS_BOOK . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL') . '">' . ACCOUNT_PASSWORD . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . ACCOUNT_ORDERS . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . ACCOUNT_NEWSLETTERS . '</a>'; ?></li>
                      </ul>
                    </li>
                    <li>
                      <ul>                    
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . BOX_HEADING_SHOPPING_CART . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . HEADER_TITLE_CHECKOUT . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCED_SEARCH) . '">' . BOX_SEARCH_ADVANCED_SEARCH . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_PRODUCTS_NEW) . '">' . BOX_HEADING_WHATS_NEW . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_SPECIALS) . '">' . BOX_HEADING_SPECIALS . '</a>'; ?></li>
                        <li><?php echo '<a href="' . tep_href_link(FILENAME_REVIEWS) . '">' . BOX_HEADING_REVIEWS . '</a>'; ?></li>
                      <!-- li><?php echo BOX_HEADING_INFORMATION; ?></li -->
                      </ul>
                    </li>
                  </ul>
                </td>
              </tr>
            </table> 
          </div>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td> 
          <div class="divbox">
            <div style="text-align:right;margin:2px 12px"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></div>
          </div>
        </td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');

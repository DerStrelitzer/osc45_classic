<?php
/*
  $Id: product_info.php,v 1.97 2003/07/01 14:34:54 hpdl Exp $

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

if (!isset($_GET['products_id'])) {
    $_GET['products_id']='';
}

$product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
$product_check = tep_db_fetch_array($product_check_query);

// Ingo's meta-tags beginn
$meta_key = $title_path . ' '; $meta_desc = ''; $the_price = '';
function clean_word($word='') {
    $replace = array ( '"' => ' ',   ',' => ' ',   '.' => ' ',   ':' => ' ',   ';' => ' ',  '?' => ' ',  '-' => ' ', '(' => ' ', ')' => ' ', '/' => ' ', '[' => ' ', ']' => ' ', chr(13) =>'', chr(10) =>'' );
    return trim(preg_replace('/\\s+/', ' ', preg_replace('/[' . preg_quote('",.:;?-()/[]' . "\r\n\t", '/') . ']+/', ' ', $word)));
}

if ($product_check['total'] > 0) {

    $product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
    $product_info = tep_db_fetch_array($product_info_query);
    
    $products_link_name = ingo_make_link($product_info['products_id'], 'p', $product_info['products_name']);
    if (strpos($_SERVER['REQUEST_URI'], $products_link_name)===false) {
        tep_redirect(ingo_product_link($product_info['products_id'], $product_info['products_name']), '301');
    } 
    $this_products_tax_rate = tep_get_tax_rate($product_info['products_tax_class_id']);

    $meta_desc = substr(str_replace(array("'", '"', "\n", "\r"), array("", "", " ", " "), strip_tags(nl2br(html_entity_decode($product_info['products_description'])))), 0, 199);

    $keyw_array = preg_split('/\\s/', clean_word(strip_tags(nl2br(html_entity_decode($product_info['products_description'])))), -1, PREG_SPLIT_NO_EMPTY);
    $loop = 0;
    foreach($keyw_array as $word) { 
        if ($word == ucfirst($word) && strlen($word)>3) {
            $meta_key .= ($loop==0?'':', ') . $word;
            $loop++;
        }
    }

    if ($new_price = tep_get_products_special_price($product_info['products_id'])) {
        $the_price = $currencies->display_price($new_price, $this_products_tax_rate);
        $products_price = '<span class="striked">' . $currencies->display_price($product_info['products_price'], $this_products_tax_rate) . '</span><br /><span class="productSpecialPrice listingprice">' . ingo_make_euro($the_price) . '</span>';
    } else {
        $the_price = $currencies->display_price($product_info['products_price'], $this_products_tax_rate);
        $products_price = '<span class="listingprice">' . ingo_make_euro($the_price) . '</span>';
    }
// Ingo, besuchte Produkte
    if (isset($_SESSION['viewed_products']) && is_object($_SESSION['viewed_products'])) {
        $_SESSION['viewed_products']->store($product_info['products_id'], $product_info['products_name']);
    }
} else {
    $product_info = ['products_name' => '', 'products_id' => 0];
}

$page_title = strip_tags($product_info['products_name'] . ' - ' . $title_path . ' - ' . $the_price);
$page_keywords = $meta_key;
$page_description = $meta_desc;
// Ingo's meta-tags ende

$breadcrumb->add($product_info['products_name'], ingo_product_link($product_info['products_id'], $product_info['products_name']));

$this_head_include = "<script type=\"text/javascript\"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>";
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')) . 'action=add_product'));

if ($product_check['total'] < 1) {

    define('HEADING_TITLE', TEXT_PRODUCT_NOT_FOUND);
    require(DIR_WS_INCLUDES . 'column_center.php');
?>
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
<?php
} else {

    tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed = products_viewed+1 where products_id = '" . (int)$_GET['products_id'] . "' and language_id = '" . (int)$_SESSION['languages_id'] . "'");

    define('HEADING_TITLE', $product_info['products_name']);
    require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td>
          <div class="pinfoimage">
            <script type="text/javascript"><!--
              document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . tep_href_link(FILENAME_POPUP_IMAGE, 'pID=' . $product_info['products_id']) . '\\\')">' . tep_image_product(DIR_WS_IMAGES . $product_info['products_image'], addslashes($product_info['products_name']), THUMBNAIL_IMAGE_WIDTH, THUMBNAIL_IMAGE_HEIGHT, 'style="padding:5px;"') . '</a>'; ?>');
              //-->
            </script><noscript><?php echo '<a href="' . tep_href_link(DIR_WS_IMAGES . $product_info['products_image']) . '" target="_blank">' . tep_image_product(DIR_WS_IMAGES . $product_info['products_image'], $product_info['products_name'], THUMBNAIL_IMAGE_WIDTH, THUMBNAIL_IMAGE_HEIGHT, 'style="padding:5px;"') . '</a>'; ?></noscript>
            <p class="main"><?php echo $products_price . '</p>' . ingo_price_added($this_products_tax_rate) . tep_image_submit('button_in_cart.gif', $product_info['products_name'] . ' - ' . IMAGE_BUTTON_IN_CART, 'style="margin-top:10px;"'); ?>
          </div>
          <div class="productsdescription"><?php echo ingo_link_in_text(stripslashes($product_info["products_description"])); ?></div>
        </td>
      </tr>
<?php
    $products_attributes_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$_GET['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$_SESSION['languages_id'] . "'");
    $products_attributes = tep_db_fetch_array($products_attributes_query);
    if ($products_attributes['total'] > 0) {
?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main" colspan="2"><?php echo TEXT_PRODUCT_OPTIONS; ?></td>
            </tr>
<?php
      $products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$_GET['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$_SESSION['languages_id'] . "' order by popt.products_options_name");
      while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
        $products_options_array = [];
        $products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$_GET['products_id'] . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$_SESSION['languages_id'] . "'");
        while ($products_options = tep_db_fetch_array($products_options_query)) {
          $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name']);
          if ($products_options['options_values_price'] != '0') {
            $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], $this_products_tax_rate) .') ';
          }
        }

        if (isset($cart->contents[$_GET['products_id']]['attributes'][$products_options_name['products_options_id']])) {
          $selected_attribute = $cart->contents[$_GET['products_id']]['attributes'][$products_options_name['products_options_id']];
        } else {
          $selected_attribute = false;
        }
?>
            <tr>
              <td class="main"><?php echo $products_options_name['products_options_name'] . ':'; ?></td>
              <td class="main"><?php echo tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute); ?></td>
            </tr>
<?php
      }
?>
          </table>
        </td>
      </tr>
<?php
    }
?>
<!-- Ingo Beginn images real unlimited -->
<?php
    if ( defined('NUMBER_OF_ADD_IMAGES') && defined('NUMBER_OF_ADD_IMAGES_PER_ROW') && NUMBER_OF_ADD_IMAGES_PER_ROW > 0 ){
      $add_images_query = tep_db_query("select image_id, image_name from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . $product_info['products_id'] . "' order by sort_order");
      $add_images_found = tep_db_num_rows($add_images_query);
      if ($add_images_found > 0) {
        if ($add_images_found > NUMBER_OF_ADD_IMAGES_PER_ROW) {
          $add_td_width = intval(100/NUMBER_OF_ADD_IMAGES_PER_ROW);
          $add_td_number = NUMBER_OF_ADD_IMAGES_PER_ROW;
        } else {
          $add_td_width = intval(100/$add_images_found);
          $add_td_number = $add_images_found;
        }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td colspan="2" class="seitpadding"><table width="100%" cellpadding="2" style="border: 1px solid #006f3a;">
          <tr>
            <td class="smallText" align="center"><?php echo str_replace('<br />', ' ',TEXT_CLICK_TO_ENLARGE); ?></td>
          </tr>
          <tr>
            <td align="center"><table width="100%" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <table width="100%" border="0" cellpadding="5" cellspacing="0">
                    <tr>
<?php
        $loop = 1;
        while ($add_images = tep_db_fetch_array($add_images_query)) {
?>
                      <td width="<?php echo $add_td_width; ?>%" align="center" class="smallText">
                        <script type="text/javascript">
                          <!--
                          document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . tep_href_link(FILENAME_POPUP_IMAGE, 'iID=' . $add_images['image_id']) . '\\\')">' . $thumbnail->get(DIR_WS_IMAGES . $add_images['image_name'], addslashes($product_info['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<\/a>'; ?>');
                          //-->
                        </script>
                        <noscript>
                          <a href="<?php echo tep_href_link(DIR_WS_IMAGES . $add_images['image_name']) . '" target="_blank">' . $thumbnail->get(DIR_WS_IMAGES . $add_images['image_name'], $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></a>
                        </noscript>
                      </td>
<?php
          $loop++;
          $add_images_found--;
          if ($loop > $add_td_number) {
            $loop = 1;
            if ($add_images_found > 0) {
?>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
<?php
            }
            if (($add_images_found > 0) && ($add_images_found < $add_td_number)) {
              $add_td_width = (int)(100/$add_images_found);
              $add_td_number = $add_images_found;
            }
          }
        }
?>
                    </tr>
                  </table>
                </td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
      }
    }
?>
<!-- Ingo Ende images real unlimited -->
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    $reviews_query = tep_db_query("select count(*) as count from " . TABLE_REVIEWS . " where products_id = '" . (int)$_GET['products_id'] . "'");
    $reviews = tep_db_fetch_array($reviews_query);
    if ($reviews['count'] > 0) {
?>
      <tr>
        <td class="main"><?php echo TEXT_CURRENT_REVIEWS . ' ' . $reviews['count']; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    }
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_HAVE_A_QUESTION, '<a href="' . tep_href_link(FILENAME_CONTACT_US, 'products_id=' . ingo_make_link($product_info['products_id'], 'p', $product_info['products_name'])) . '"><b>' . $product_info['products_name'] . '</b></a>'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>

<?php
    if (tep_not_null($product_info['products_url'])) {
?>
      <tr>
        <td  align="center"><h2 class="hsmall"><?php echo sprintf(TEXT_MORE_INFORMATION, 'http://' .  $product_info['products_url'], $product_info['products_name']); ?></h2></td>
      </tr>
<?php
    }

    if ($product_info['products_date_available'] > date('Y-m-d H:i:s')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_DATE_AVAILABLE, $product_info['products_name'], tep_date_long($product_info['products_date_available'])); ?></td>
      </tr>
<?php
    } else {
?>
      <tr>
        <td align="center" class="smallText"><h2 class="hsmall"><?php echo sprintf(TEXT_DATE_ADDED, $product_info['products_name'], tep_date_long($product_info['products_date_added'])); ?></h2></td>
      </tr>
<?php
    }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_PRODUCT_REVIEWS, tep_get_all_get_params()) . '">' . tep_image_button('button_reviews.gif', $product_info['products_name'] . ' '. IMAGE_BUTTON_REVIEWS, 'style="margin-left:10px;"') . '</a>'; ?></td>
                <td class="main" align="right"><?php echo tep_draw_hidden_field('products_id', $product_info['products_id']) . tep_image_submit('button_in_cart.gif', $product_info['products_name'] .' '. IMAGE_BUTTON_IN_CART, 'style="margin-right:10px;"'); ?></td>
              </tr>
<!-- Ingo blättert, beginn -->
<?php
    $vorige=$folgend=0;$merke=false;$ingo_flag=false;
    $ingo_query = tep_db_query('SELECT pd.products_id, pd.products_name FROM ' . TABLE_PRODUCTS . ' p, ' . TABLE_PRODUCTS_DESCRIPTION . ' pd, ' . TABLE_PRODUCTS_TO_CATEGORIES . ' pc WHERE p.products_id = pd.products_id AND p.products_id = pc.products_id AND p.products_status = 1 AND pd.language_id = ' . (int)$_SESSION['languages_id'] . ' AND pc.categories_id = ' . (int)$current_category_id . ' ORDER BY pc.categories_id, pd.products_name');
    while ($ingo=tep_db_fetch_array($ingo_query)){
      if ($ingo_flag){$folgend=$ingo['products_id'];$folgend_name=$ingo['products_name'];break;}
      if ($ingo['products_id']==$_GET['products_id']){if(is_array($merke)){$vorige=$merke['id'];$vorige_name=$merke['name'];}$ingo_flag=true;}
      $merke=array('id'=>$ingo['products_id'],'name'=>$ingo['products_name']);
    }
    //$folgend_name = tep_get_products_name($folgend);
    //$vorige_name = tep_get_products_name($vorige);
    if ($vorige>0 || $folgend>0) {
?>
              <tr>
                <td colspan="4" align="center"><?php echo tep_draw_separator('pixel_black.gif', '95%', '1'); ?></td>
              </tr>
              <tr>
                <td class="main" colspan="4" align="center">
<?php 
      echo
      '                 ' . ($vorige==0? '' : '&laquo;&laquo; <a href="'. ingo_product_link($vorige, $vorige_name).'">' . $vorige_name . '</a>') . ($folgend>0 ? ' &laquo;&laquo;':'') . "\n" .
      '                 ' . tep_draw_separator('pixel_trans.gif','20','1') . "\n".
      '                 ' . ($folgend==0? '' : ($vorige>0 ? '&raquo;&raquo; ':'') . '<a href="' . ingo_product_link($folgend, $folgend_name) . '">' . $folgend_name . '</a> &raquo;&raquo;') . "\n";
?>
                </td>
              </tr>
<?php
    } 
?>
<!-- Ingo blättert, ende  -->
            </table>
          </div>
        </td>
      </tr>
<?php
    if (USE_CACHE == 'ja' && empty($SID)) {
        echo tep_cache_also_purchased(3600);
    } else {
        include(DIR_WS_MODULES . FILENAME_ALSO_PURCHASED_PRODUCTS);
    }
}
?>
    </table></form></td>
<?php
  require(DIR_WS_INCLUDES . 'column_right.php');
  require(DIR_WS_INCLUDES . 'footer.php');
?>
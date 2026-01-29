<?php
/*
  $Id: shipping_estimator.php,v 2.00 2004/07/01 15:16:07 eml Exp $

  v2.00 by Acheron
  (see Install.txt for partial version history)

  Copyright (c) 2004

  Released under the GNU General Public License
*/

  define('CART_SHIPPING_SELECT_THIS', 'select this method');
  define('CART_SHIPPING_ITEM', 'Product');
  define('CART_SHIPPING_ITEMS', 'Products');

  define('CART_SHIPPING_CARRIER_TEXT', 'Carrier');
  define('CART_SHIPPING_OPTIONS', 'Shipping Estimate');
  define('CART_SHIPPING_OPTIONS_LOGIN', 'Please <a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '"><span class="underlined">Log In</span></a>, to display your personal shipping costs.');
  define('CART_SHIPPING_METHOD_TEXT','Delivery Methods');
  define('CART_SHIPPING_METHOD_RATES','Rates');
  define('CART_SHIPPING_METHOD_TO','Ship to: ');
  define('CART_SHIPPING_METHOD_TO_NOLOGIN', '<b>Ship to:</b>&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '"><span class="underlined">Log In</span></a>');
  define('CART_SHIPPING_METHOD_FREE_TEXT','Free Shipping');
  define('CART_SHIPPING_METHOD_ALL_DOWNLOADS',' (Virtual Products)');
  define('CART_SHIPPING_METHOD_RECALCULATE','Recalculate');
  define('CART_SHIPPING_METHOD_ADDRESS','Address:');
  define('CART_OT','Order Total Estimate');
  define('CART_SELECT','select');
?>
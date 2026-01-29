<?php
/*
  $Id: stats_keywords.php,v 0.90 10/03/2002 03:15:00 Exp $
	by Cheng


  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

  require('includes/application_top.php');
  
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body>
<?php 
  require(DIR_WS_INCLUDES . 'header.php'); 
?>

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php 
  require(DIR_WS_INCLUDES . 'column_left.php'); 
?>
<!-- body_text //-->

    <td><table>
		<tr><td>

		<form method="post" action="stats_keywords.php">
                <input type="hidden" name="action" value="deletedb">
                <input type="submit" value="deleteDB">
                </form>

		<br><br><br><br><br>

		<form method="post" action="stats_keywords.php">
		<input type="hidden" name="action" value="updatedb">
		<input type="submit" value="UpdateDB">
		</form>


		</td></tr><tr><td>

		<form method="post" action="stats_keywords.php">
		<input type="hidden" name="action" value="show_searches">
		<input type="hidden" name="sortorder" value="search_text">
		<input type="hidden" name="ascdsc" value="ASC">
		<input type="submit" value="Sort By Name">
		</form>


               <form method="post" action="stats_keywords.php">
                <input type="hidden" name="action" value="show_searches">
                <input type="hidden" name="sortorder" value="search_count">
		<input type="hidden" name="ascdsc" value="DESC">
                <input type="submit" value="Sort By Count">
                </form>

		</td></tr>
		</table>

	</td><td>

	<?php

		if ($_POST['action'] == 'deletedb') {

			tep_db_query("delete from search_queries_sorted");

			} // delete db


	if ($_POST['action'] == 'updatedb') {

		$sql_q = tep_db_query("select search_id, search_text from search_queries order by search_text");
    while ($sql_q_result = tep_db_fetch_array($sql_q)) {

			/*
                        $sql_count = tep_db_query("select count(*) as total from search_queries where search_text = '" .
                                $sql_q_result['search_text'] . "'");
                        $sql_count_result = tep_db_fetch_array($sql_count);
			*/

			$update_q = tep_db_query("select search_text, search_count from search_queries_sorted where
						search_text = '" . $sql_q_result['search_text'] . "'");

			$update_q_result = tep_db_fetch_array($update_q);

			$count = 1 + $update_q_result['search_count'];


			if ($update_q_result['search_count'] != '') {
				tep_db_query("update ignore search_queries_sorted set search_count = '" .
					$count . "' where search_text = '" .
					$sql_q_result['search_text'] . "'");
		  } else {

				tep_db_query("insert ignore into search_queries_sorted (search_text, search_count) values ('" . $sql_q_result['search_text'] . "','" . $sql_count_result['total'] . "')");

			} // search_count

			tep_db_query("delete from search_queries where search_id = '" . $sql_q_result['search_id'] . "'");

		} // while



	} // updatedb


		if ($_POST['action'] == 'show_searches') {

?>

		<table cellpadding=2 cellspacing=2>
      <tr>
        <td class="main" align="left"> 
          <b><u>Keyword</u></b>
        </td>
        <td class="main" align="center"> 
          <b><u>Searches</u></b> 
        </td></tr>

<?php
		$sql_q = tep_db_query("select search_text, search_count from search_queries_sorted order by " .
			$_POST['sortorder'] . " " . $_POST['ascdsc']);

		while ($sql_q_result = tep_db_fetch_array($sql_q)) {

			echo '<tr><td class="main"> &nbsp;&nbsp;&nbsp;&nbsp;' . $sql_q_result['search_text'] . '</td><td>
				&nbsp;&nbsp;&nbsp;&nbsp; ' . $sql_q_result['search_count'] . '</td></tr>';

		} // while

?>

		    </td>
      </tr>
    </table>

<?php
			} // if show_searches
?>

</td>
<!-- body_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<?php 
  require(DIR_WS_INCLUDES . 'footer.php'); 
?>
</body>
</html>
<?php 
  require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>
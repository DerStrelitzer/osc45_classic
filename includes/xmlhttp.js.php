<script type="text/javascript">
// <![CDATA[
var XMLHTTP = null;

if (window.XMLHttpRequest) {
    XMLHTTP = new XMLHttpRequest();
} else if (window.ActiveXObject) {
    try {
        XMLHTTP =
        new ActiveXObject("Msxml2.XMLHTTP");
    } catch (ex) {
        try {
            XMLHTTP =
            new ActiveXObject("Microsoft.XMLHTTP");
       } catch (ex) {
      }
    }
}

function show_the_cart()
{
    if (XMLHTTP.readyState == 4) {
        var d = document.getElementById("shoppingcart");
        d.innerHTML = XMLHTTP.responseText;
    }
}

function shopping_cart()
{
    XMLHTTP.open("POST", "<?php echo tep_href_link('box_shopping_cart.php'); ?>");
    XMLHTTP.onreadystatechange = show_the_cart;
    XMLHTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    XMLHTTP.send("session=<?php echo tep_session_name(); ?>");
}

function cart_action(id, qty)
{
    if (typeof id == 'undefined' || isNaN(id)) id=0;
    if (typeof qty == 'undefined' || isNaN(qty)) qty=1;
    XMLHTTP.open("POST", "<?php echo str_replace('&amp;', '&', tep_href_link('box_shopping_cart.php', 'action=buy_now')); ?>");
    XMLHTTP.onreadystatechange = show_the_cart;
    XMLHTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    XMLHTTP.send("session=<?php echo tep_session_name(); ?>&id=" + id + "&qty=" + qty);
}

// ]]>
</script>

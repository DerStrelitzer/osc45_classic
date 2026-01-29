<?php
/*
  $Id: popup_search_help.php,v 1.4 2003/06/05 23:26:23 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_SEARCH_HELP', 'Consejos para Búsqueda Avanzada');
define('TEXT_SEARCH_HELP', 'El motor de búsqueda le permite hacer una búsqueda por palabras clave en el modelo, nombre y descripción del producto y en el nombre del fabricante.<br /><br />Cuando haga una busqueda por palabras o frases clave, puede separar estas con los operadores lógicos AND y OR. Por ejemplo, puede hacer una busqueda por <span class="underlined">microsoft AND raton</span>. Esta búsqueda daría como resultado los productos que contengan ambas palabras. Por el contrario, si teclea  <span class="underlined">raton OR teclado</span>, conseguirá una lista de los productos que contengan las dos o solo una de las palabras. Si no se separan las palabras o frases clave con AND o con OR, la búsqueda se hara usando por defecto el operador logico AND.<br /><br />Puede realizar busquedas exactas de varias palabras encerrandolas entre comillas. Por ejemplo, si busca <span class="underlined">"ordenador portatil"</span>, obtendrás una lista de productos que tengan exactamente esa cadena en ellos.<br /><br />Se pueden usar paratensis para controlar el orden de las operaciones lógicas. Por ejemplo, puede introducir <span class="underlined">microsoft and (teclado or raton or "visual basic")</span>.');
define('TEXT_CLOSE_WINDOW', '<span class="underlined">Cerrar Ventana</span> [x]');

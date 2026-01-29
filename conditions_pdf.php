<?php
/*
  $Id: conditions_pdf.php,v 1.00 2005/11/01 Ingo <www.strelitzer.de>

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

$info_text = '';
if (defined('READ_INFO_TEXTE_FROM_DATABASE') && READ_INFO_TEXTE_FROM_DATABASE=='ja') {
    $query = tep_db_query("SELECT text FROM " . TABLE_INFO_TEXTE . " WHERE code = 'conditions' AND languages_id = '" . (int)$_SESSION['languages_id'] . "'");
    if ($result = tep_db_fetch_array($query)) {
        $info_text = trim($result['text']);
    }
} else {
    $info_text = TEXT_INFORMATION;
} 

$info_text = trim(html_entity_decode(strip_tags(str_replace(array('<br>', '<BR>', '<br />', '<BR />', "\n\n"), "\n", $info_text))));

require('fpdf/fpdf.php');
class cpdf extends fpdf {
    function header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(190, 5, html_entity_decode(HEADING_TITLE . ', ' . STORE_NAME), 1);
        $this->Ln(8);
    }
    function footer()
    {
        $this->SetY(-8);
        $this->SetFont('Arial','I',7);
        $this->Cell(0,8,'Seite '. $this->PageNo().'/{nb}',0,0,'R');
    }
}

$pdf = new cpdf();
$pdf->SetCreator('Ingo@osCommerce 2.2');
$pdf->SetTitle(html_entity_decode(HEADING_TITLE));
$pdf->SetAuthor(STORE_OWNER);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true,8);
$pdf->SetFont('Arial', '', 9);
$pdf->write(4, $info_text);
$pdf->Output('conditions.pdf', 'I');
exit();

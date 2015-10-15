<?php

$reportdata['title'] = 'Client Gross Revenue Report for ' . $months[(int)$month] . ' ' . $year;
$reportdata['description'] = 'Client Gross Revenue per month';
$reportdata['monthspagination'] = true;

$reportdata["tableheadings"] = array(
  "Client",
  "Total Invoices",
  "Invoices Total",
);

$datepaidString = $year . '-' . $month . '-%';

$monthlyInvoicesQuery = select_query('tblinvoices', 'userid', array('datepaid' => array('sqltype' => 'LIKE', 'value' => $datepaidString), 'status' => 'Paid'));

$monthlyInvoicesArray = array();
while($row = mysql_fetch_array($monthlyInvoicesQuery))
{
  $monthlyInvoicesArray[] = $row;
}

$clientIdsWithPaidInvoices = array();
if($monthlyInvoicesArray)
{
  foreach($monthlyInvoicesArray as $invoice)
  {
    if(!in_array($invoice['userid'], $clientIdsWithPaidInvoices))
    {
      $clientIdsWithPaidInvoices[] = $invoice['userid'];
    }
  }
}

if($clientIdsWithPaidInvoices)
{
  foreach($clientIdsWithPaidInvoices as $client)
  {
    $clientInfoQuery = select_query('tblclients', 'id, firstname, lastname, companyname', array('id' => $client));

    $clientInfoArray = array();
    while($row = mysql_fetch_array($clientInfoQuery))
    {
      $clientInfoArray = $row;
    }

    if($clientInfoArray)
    {
      if($clientInfoArray['companyname'] == '' )
      {
        $companyName = $clientInfoArray['lastname'] . ', ' . $clientInfoArray['firstname'];
      }else{
        $companyName = $clientInfoArray['companyname'];
      }
    }

    $clientMonthlyInvoiceQuery = select_query('tblinvoices', 'id, userid, datepaid, total', array('datepaid' => array('sqltype' => 'LIKE', 'value' => $datepaidString), 'status' => 'Paid', userid => $client));

    $clientMonthlyInvoiceArray = array();
    while($row = mysql_fetch_array($clientMonthlyInvoiceQuery))
    {
      $clientMonthlyInvoiceArray[] = $row;
    }

    $totalInvoices = 0;
    $invoiceTotals = 0.00;
    if($clientMonthlyInvoiceArray)
    {
      foreach($clientMonthlyInvoiceArray as $invoice)
      {
        $invoiceTotals += $invoice['total'];
        $totalInvoices++;
      }

      setlocale(LC_MONETARY, 'en_US');
      $reportdata['tablevalues'][] = array(
        '<a href="clientssummary.php?userid=' . $clientInfoArray['id'] . '" target="_blank">' . $companyName . '</a>',
        $totalInvoices,
        money_format('%i', $invoiceTotals)
      );
    }
  }
}

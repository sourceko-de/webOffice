<!DOCTYPE html>
<!--
  Invoice template by invoicebus.com
  To customize this template consider following this guide https://invoicebus.com/how-to-create-invoice-template/
  This template is under Invoicebus Template License, see https://invoicebus.com/templates/license/
-->
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="Invoice">

    <style>
        /* Reset styles */
        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed,
        figure, figcaption, footer, header, hgroup,
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 80%;
            vertical-align: baseline;
        }

        html {
            line-height: 1;
        }

        ol, ul {
            list-style: none;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        caption, th, td {
            text-align: left;
            font-weight: normal;
            vertical-align: middle;
        }

        q, blockquote {
            quotes: none;
        }
        q:before, q:after, blockquote:before, blockquote:after {
            content: "";
            content: none;
        }

        a img {
            border: none;
        }

        article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
            display: block;
        }

        /* Invoice styles */
        /**
         * DON'T override any styles for the <html> and <body> tags, as this may break the layout.
         * Instead wrap everything in one main <div id="container"> element where you may change
         * something like the font or the background of the invoice
         */
        html, body {
            /* MOVE ALONG, NOTHING TO CHANGE HERE! */
        }

        /**
         * IMPORTANT NOTICE: DON'T USE '!important' otherwise this may lead to broken print layout.
         * Some browsers may require '!important' in oder to work properly but be careful with it.
         */
        .clearfix {
            display: block;
            clear: both;
        }

        .hidden {
            display: none;
        }

        b, strong, .bold {
            font-weight: bold;
        }

        #container {
            font: normal 13px/1.4em 'Open Sans', Sans-serif;
            margin: 0 auto;
            min-height: 1078px;
        }

        .invoice-top {
            background: #242424;
            color: #fff;
            padding: 40px 40px 30px 40px;
        }

        .invoice-body {
            padding: 50px 40px 40px 40px;
        }

        #memo .logo {
            float: left;
            margin-right: 20px;
        }
        #memo .logo img {
            max-width: 150px;
        }
        #memo .company-info {
            /*float: right;*/
            text-align: right;
        }
        #memo .company-info .company-name {
            color: #F8ED31;
            font-weight: bold;
            font-size: 32px;
        }
        #memo .company-info .spacer {
            height: 15px;
            display: block;
        }
        #memo .company-info div {
            font-size: 12px;
            float: right;
            margin: 0 18px 3px 0;
        }
        #memo:after {
            content: '';
            display: block;
            clear: both;
        }

        #invoice-info {
            float: left;
            margin-top: 50px;
        }

        #invoice-info table{
            width: 30%;
        }
        #invoice-info > div {
            float: left;
        }
        #invoice-info > div > span {
            display: block;
            min-width: 100px;
            min-height: 18px;
            margin-bottom: 3px;
        }
        #invoice-info > div:last-of-type {
            margin-left: 10px;
            text-align: right;
        }
        #invoice-info:after {
            content: '';
            display: block;
            clear: both;
        }

        #client-info {
            float: right;
            /*margin-top: 5px;*/
            margin-right: 30px;
            min-width: 220px;
        }
        #client-info > div {
            margin-bottom: 3px;
        }
        #client-info span {
            display: block;
        }
        #client-info > span {
            margin-bottom: 3px;
        }

        #invoice-title-number {
            margin-top: 30px;
        }
        #invoice-title-number #title {
            margin-right: 5px;
            text-align: right;
            font-size: 35px;
        }
        #invoice-title-number #number {
            margin-left: 5px;
            text-align: left;
            font-size: 20px;
        }

        table {
            table-layout: fixed;
        }
        table th, table td {
            vertical-align: top;
            word-break: keep-all;
            word-wrap: break-word;
        }

        #items .first-cell, #items table th:first-child, #items table td:first-child {
            width: 18px;
            text-align: right;
        }
        #items table {
            border-collapse: separate;
            width: 100%;
        }
        #items table th {
            font-weight: bold;
            padding: 12px 10px;
            text-align: right;
            border-bottom: 1px solid #444;
            text-transform: uppercase;
        }
        #items table th:nth-child(2) {
            width: 30%;
            text-align: left;
        }
        #items table th:last-child {
            text-align: right;
        }
        #items table td {
            border-right: 1px solid #b6b6b6;
            padding: 15px 10px;
            text-align: right;
        }
        #items table td:first-child {
            text-align: left;
            border-right: none !important;
        }
        #items table td:nth-child(2) {
            text-align: left;
        }
        #items table td:last-child {
            border-right: none !important;
        }

        #sums {
            float: right;
            /*margin-right: 100px;*/
            margin-top: 30px;
        }
        
        #sums table{
            width: 34%;
        }
        #sums table tr th, #sums table tr td {
            min-width: 100px;
            padding: 10px;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        #sums table tr th {
            text-align: left;
            padding-right: 25px;
            color: #707070;
        }
        /*#sums table tr td:last-child {*/
            /*min-width: 0 !important;*/
            /*max-width: 0 !important;*/
            /*width: 0 !important;*/
            /*padding: 0 !important;*/
            /*overflow: visible;*/
        /*}*/
        #sums table tr.amount-total th {
            color: black;
        }
        #sums table tr.amount-total th, #sums table tr.amount-total td {
            font-weight: bold;
        }
        /*#sums table tr.amount-total td:last-child {*/
            /*position: relative;*/
        /*}*/
        /*#sums table tr.amount-total td:last-child .currency {*/
            /*position: absolute;*/
            /*top: 3px;*/
            /*left: -740px;*/
            /*font-weight: normal;*/
            /*font-style: italic;*/
            /*font-size: 12px;*/
            /*color: #707070;*/
        /*}*/
        /*#sums table tr.amount-total td:last-child:before {*/
            /*display: block;*/
            /*content: '';*/
            /*border-top: 1px solid #444;*/
            /*position: absolute;*/
            /*top: 0;*/
            /*left: -740px;*/
            /*right: 0;*/
        /*}*/
        /*#sums table tr:last-child th, #sums table tr:last-child td {*/
            /*color: black;*/
        /*}*/

        #terms {
            margin: 100px 0 15px 0;
        }
        #terms > div {
            min-height: 70px;
        }

        .payment-info {
            color: #707070;
            font-size: 12px;
        }
        .payment-info div {
            display: inline-block;
            min-width: 10px;
        }

        .ib_drop_zone {
            color: #F8ED31 !important;
            border-color: #F8ED31 !important;
        }

        /**
         * If the printed invoice is not looking as expected you may tune up
         * the print styles (you can use !important to override styles)
         */
        @media print {
            /* Here goes your print styles */
        }

    </style>
</head>
<body>
<div id="container">
    <div class="invoice-top">
        <section id="memo">
            <div class="logo">
                @if(is_null($global->logo))
                <img src="{{ asset('worksuite-logo.png') }}" />
                @else
                    <img src="{{ asset('user-uploads/app-logo/'.$global->logo) }}" alt="home" class="dark-logo"/>
                @endif
            </div>

            <div class="company-info">
                <span class="company-name">
                <?php
                    $company = explode(' ', trim($global->company_name));
                    echo $company[0];
                    ?>
                </span>

                <span class="spacer"></span>

                <div>{!! nl2br($global->address) !!}</div>


                <span class="clearfix"></span>

                <div>{{ $global->company_phone }}</div>
            </div>

        </section>

        <section id="invoice-info">
            <table>
                <tr>
                    <td>Issue Date:</td>
                    <td>{{ $invoice->issue_date->format("dS M Y") }}</td>
                </tr>
                @if($invoice->status == 'unpaid')
                <tr>
                    <td>Due Date:</td>
                    <td>{{ $invoice->due_date->format("dS M Y") }}</td>
                </tr>
                @endif

            </table>


            <div class="clearfix"></div>

            <section id="invoice-title-number">

                <span id="title">{{ $invoiceSetting->invoice_prefix }}</span>
                <span id="number">#{{ ($invoice->id < 10) ? "0".$invoice->id : $invoice->id }}</span>

            </section>
        </section>
        @if(!is_null($invoice->project->client))
            <section id="client-info">
                @if(!is_null($invoice->project->client))
                <span>Billed To:</span>
                <div>
                    <span class="bold">{{ ucwords($invoice->project->client->name) }}</span>
                </div>

                <div>
                    <span>{{ ucwords($invoice->project->client->client[0]->company_name) }}</span>
                </div>

                <div>
                    <span>{!! nl2br($invoice->project->client->client[0]->address) !!}</span>
                </div>

                <div>
                    <span>{{ $invoice->project->client->email }}</span>
                </div>
                @endif

            </section>
        @endif
        <div class="clearfix"></div>
    </div>

    <div class="clearfix"></div>

    <div class="invoice-body">
        <section id="items">

            <table cellpadding="0" cellspacing="0">

                <tr>
                    <th>#</th> <!-- Dummy cell for the row number and row commands -->
                    <th>@lang("modules.invoices.item")</th>
                    <th>@lang("modules.invoices.qty")</th>
                    <th>@lang("modules.invoices.unitPrice")</th>
                    <th>@lang("modules.invoices.price")</th>
                </tr>

                <?php $count = 0; ?>
                @foreach($invoice->items as $item)
                    @if($item->type == 'item')
                <tr data-iterate="item">
                    <td>{{ ++$count }}</td> <!-- Don't remove this column as it's needed for the row commands -->
                    <td>{{ ucfirst($item->item_name) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{!! htmlentities($invoice->currency->currency_symbol)  !!}{{ number_format((float)$item->unit_price, 2, '.', '') }}</td>
                    <td>{!! htmlentities($invoice->currency->currency_symbol)  !!}{{ number_format((float)$item->amount, 2, '.', '') }}</td>
                </tr>
                    @endif
                @endforeach

            </table>

        </section>

        <section id="sums">

            <table cellpadding="0" cellspacing="0">
                <tr>
                    <th>@lang("modules.invoices.subTotal"):</th>
                    <td>{!! htmlentities($invoice->currency->currency_symbol)  !!}{{ number_format((float)$invoice->sub_total, 2, '.', '') }}</td>
                </tr>
                @if($discount != 0 && $discount != '')
                <tr data-iterate="tax">
                    <th>@lang("modules.invoices.discount"):</th>
                    <td>-{!! htmlentities($invoice->currency->currency_symbol)  !!}{{ number_format((float)$discount, 2, '.', '') }}</td>
                </tr>
                @endif
                @foreach($taxes as $key=>$tax)
                <tr data-iterate="tax">
                    <th>{{ strtoupper($key) }}:</th>
                    <td>{!! htmlentities($invoice->currency->currency_symbol)  !!}{{ number_format((float)$tax, 2, '.', '') }}</td>
                </tr>
                @endforeach

                <tr class="amount-total">
                    <th>
                        <hr>@lang("modules.invoices.total"):</th>
                    <td>
                        <hr>{!! htmlentities($invoice->currency->currency_symbol)  !!}{{ number_format((float)$invoice->total, 2, '.', '') }}</td>
                </tr>


            </table>

        </section>

        <div class="clearfix"></div>
        <hr>
        <section id="terms">
            <span class="hidden">Terms:</span>
            <div class="currency">
                <span>Here {!! htmlentities($invoice->currency->currency_symbol)  !!} refers to {!! $invoice->currency->currency_code  !!}</span>

            </div>
            @if(!is_null($invoice->note))
                <div>{!! nl2br($invoice->note) !!}</div>
            @endif
            @if($invoice->status == 'unpaid')
            <div>{!! nl2br($invoiceSetting->invoice_terms) !!}</div>
            @endif

        </section>


    </div>

</div>

</body>
</html>

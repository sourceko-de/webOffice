<?php

namespace App\Http\Controllers\Admin;

use App\Currency;
use App\Helper\Reply;
use App\Invoice;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Payment;

class FinanceReportController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.financeReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    // public function index() 
    // {

    //     $this->currencies = Currency::all();
    //     $this->currentCurrencyId = $this->global->currency_id;

    //     $symbols = array();
    //     foreach($this->currencies as $currency){
    //         $symbols[] = $currency->currency_code;
    //     }
    //     $symbols = implode(',', $symbols);

    //     $client = new Client();
    //     $res = $client->request('GET', 'http://apilayer.net/api/live?access_key='.env('CURRENCY_EXCHANGE_ACCESS_KEY', '16a1873361ce993889eea414e73cc9a6').'&currencies='.$symbols.'&source='.$this->global->currency->currency_code.'&format=1', ['verify' => false]);

    //     $conversionRate = $res->getBody();

    //     $conversionRateArray = json_decode($conversionRate, true);

    //     $this->fromDate = Carbon::today()->subDays(180);
    //     $this->toDate = Carbon::today();
    //     $invoices = DB::table('payments')
    //         ->join('currencies', 'currencies.id', '=', 'payments.currency_id')
    //         ->where('paid_on', '>=', $this->fromDate)
    //         ->where('paid_on', '<=', $this->toDate)
    //         ->groupBy('year', 'month')
    //         ->orderBy('paid_on', 'ASC')
    //         ->get([
    //             DB::raw('DATE_FORMAT(paid_on,\'%M/%y\') as date'),
    //             DB::raw('sum(amount) as total'),
    //             DB::raw('YEAR(paid_on) year, MONTH(paid_on) month'),
    //             'currencies.currency_code'
    //         ]);

    //     $chartData = array();
    //     foreach($invoices as $chart) {
    //         if($chart->currency_code != $this->global->currency->currency_code){
    //             if(isset($conversionRateArray['quotes'])){
    //                 $chartData[] = ['date' => $chart->date, 'total' => round(floor($chart->total / $conversionRateArray['quotes'][strtoupper($this->global->currency->currency_code.$chart->currency_code)]), 2)];
    //             }else{
    //                 $chartData[] = ['date' => $chart->date, 'total' => round(floor($chart->total),2)];
    //             }

    //         }
    //         else{
    //             $chartData[] = ['date' => $chart->date, 'total' => round($chart->total,2)];
    //         }
    //     }

    //     $this->chartData = json_encode($chartData);

    //     return view('admin.reports.finance.index', $this->data);
    // }

    public function index()
    {
        $graphData = [];
        $this->currencies = Currency::all();
        $this->currentCurrencyId = $this->global->currency_id;

        $this->fromDate = Carbon::today()->subDays(180);
        $this->toDate = Carbon::today();
        $incomes = [];
        $invoices = Payment::join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->where(DB::raw('DATE(`paid_on`)'), '>=', $this->fromDate)
            ->where(DB::raw('DATE(`paid_on`)'), '<=', $this->toDate)
            ->where('payments.status', 'complete')
            // ->groupBy('year', 'month')
            ->orderBy('paid_on', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(paid_on,"%M/%y") as date'),
                DB::raw('YEAR(paid_on) year, MONTH(paid_on) month'),
                DB::raw('amount as total'),
                'currencies.id as currency_id',
                'currencies.exchange_rate'
            ]);

        foreach ($invoices as $invoice) {
            if (!isset($incomes[$invoice->date])) {
                $incomes[$invoice->date] = 0;
            }

            if ($invoice->currency_id != $this->global->currency->id) {
                $incomes[$invoice->date] += floor($invoice->total / $invoice->exchange_rate);
            } else {
                $incomes[$invoice->date] += round($invoice->total, 2);
            }
        }

        $dates = array_keys($incomes);

        foreach ($dates as $date) {
            $graphData[] = [
                'date' =>  $date,
                'total' =>  isset($incomes[$date]) ? round($incomes[$date], 2) : 0,
            ];
        }

        usort($graphData, function ($a, $b) {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        });

        $this->chartData = json_encode($graphData);

        return view('admin.reports.finance.index', $this->data);
    }

    public function store(Request $request)
    {
        $this->currentCurrencyId = $request->currencyId;

        $fromDate = $request->startDate;
        $toDate = $request->endDate;

        $incomes = [];
        $graphData = [];
        $invoices = Payment::join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->where(DB::raw('DATE(`paid_on`)'), '>=', $fromDate)
            ->where(DB::raw('DATE(`paid_on`)'), '<=', $toDate)
            ->where('payments.status', 'complete')
            // ->groupBy('year', 'month')
            ->orderBy('paid_on', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(paid_on,"%M/%y") as date'),
                DB::raw('YEAR(paid_on) year, MONTH(paid_on) month'),
                DB::raw('amount as total'),
                'currencies.id as currency_id',
                'currencies.exchange_rate'
            ]);

        foreach ($invoices as $invoice) {
            if (!isset($incomes[$invoice->date])) {
                $incomes[$invoice->date] = 0;
            }

            if ($invoice->currency_id != $this->global->currency->id) {
                $incomes[$invoice->date] += floor($invoice->total / $invoice->exchange_rate);
            } else {
                $incomes[$invoice->date] += round($invoice->total, 2);
            }
        }

        $dates = array_keys($incomes);

        foreach ($dates as $date) {
            $graphData[] = [
                'date' =>  $date,
                'total' =>  isset($incomes[$date]) ? round($incomes[$date], 2) : 0,
            ];
        }

        usort($graphData, function ($a, $b) {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        });

        $chartData = json_encode($graphData);

        return Reply::successWithData(__('messages.reportGenerated'), ['chartData' => $chartData]);
    }
}

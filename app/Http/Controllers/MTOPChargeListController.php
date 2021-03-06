<?php

namespace App\Http\Controllers;

use App\Exports\ChargesExport;
use App\Models\MtopApplication;
use App\Models\Taxpayer;
use App\Models\Tricycle;
use Illuminate\Support\Facades\DB;
use Excel;

class MTOPChargeListController extends Controller
{
    public function index() {
        return view('report.mtop_charge_list');
    }

    public function fetchSearchedDataByOperator($operator_id) {

        $mtop_application_annual_taxes = MtopApplication::leftJoin('colhdr', 'colhdr.mtop_application_id', 'mtop_applications.id')
        ->leftJoin('collne2', 'collne2.or_code', 'colhdr.or_code')
        ->leftJoin('otherinc', 'otherinc.inc_code','collne2.inc_code')
        ->leftJoin('taxpayer', 'taxpayer.id', 'mtop_applications.taxpayer_id')
        ->where('otherinc.annual_tax','Y')
        ->where('mtop_applications.taxpayer_id', $operator_id)
//        ->where('otherinc.inc_desc', 'LIKE', '%' . $taxyear .'%')
        ->select(
            'colhdr.trnx_date',
            'collne2.inc_desc',
            'colhdr.or_number',
            'collne2.ln_amnt as amount',
            'taxpayer.full_name as operator',
            'mtop_applications.body_number',
            'mtop_applications.mtfrb_case_no',
            'mtop_applications.validity_date'
        )
        ->get()
        ->toArray();


        $tricycle_old_annual_taxes = Tricycle::leftJoin('taxpayer', 'taxpayer.id', 'tricycles.operator_id')
        ->where('at_or_number', '!=', '')
        ->where('tricycles.operator_id', $operator_id)
        ->select(
            'at_or_date as trnx_date',
            'taxpayer.full_name as operator',
            'at_or_number as or_number',
            'tricycles.body_number',
            'at_or_amnt as amount',
        )
        ->get();

        foreach($tricycle_old_annual_taxes as $old_data) {

            $checkCorrectTransactDesc = DB::table('colhdr')
                ->leftJoin('collne2', 'colhdr.or_code', 'collne2.or_code')
                ->leftJoin('otherinc', 'collne2.inc_code', 'otherinc.inc_code')
                ->where('otherinc.annual_tax', 'Y')
                ->where('or_number', 'LIKE', '%' . $old_data['or_number'] . '%')
//                ->where('otherinc.inc_desc', 'LIKE', '%' . $taxyear .'%')
                ->select('otherinc.inc_desc')
                ->first();


            if($checkCorrectTransactDesc)
            {
                array_push($mtop_application_annual_taxes, [
                    'trnx_date' => $old_data['trnx_date'],
                    'inc_desc' => $checkCorrectTransactDesc->inc_desc ?? '',
                    'or_number' => $old_data['or_number'],
                    'amount' => $old_data['amount'],
                    'operator' => $old_data['operator'],
                    'body_number' => $old_data['body_number'],
                    'mtfrb_case_no' => '',
                    'validity_date' => '',
                ]);
            }
        }

        return $mtop_application_annual_taxes;
    }

    public function filter_operator($operator_id) {
        $data = $this->fetchSearchedDataByOperator($operator_id);
        return response()->json(['charges' => $data], 200);
    }

    public function search($name)
    {
        $taxpayer = new Taxpayer();
        $data = $taxpayer->fetchSearchedDataByName(strtoupper($name));
        return response()->json(['operator' => $data], 200);
    }

    public function pdf($operator_id, $taxyear, $size, $orientation) {
        $mtop_charges = $this->fetchSearchedDataByOperator($operator_id, $taxyear);

        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView('pdf.pdf_mtop_charges', compact('mtop_charges'));
        $pdf->setPaper($size, $orientation);

        return $pdf->stream();
    }

    public function export($operator_id, $taxyear) {
        return Excel::download(new ChargesExport($this->fetchSearchedDataByOperator($operator_id, $taxyear)), 'operator_annual_tax' . time() . '.xlsx');
    }

}

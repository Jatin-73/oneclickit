<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class EMIController extends Controller
{
    public function emiCalculation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_date'    =>  'required',
            'checkin_date'    =>  'required',
            'amount'          =>  'required|numeric',
            'instalment_type' =>  'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        $sanitized = $validator->valid();

        // If the Checkin date is within 30 days then Emi is not available
        $your_date = strtotime($sanitized['booking_date']);
        $datediff = strtotime($sanitized['checkin_date']) - $your_date;
        $days = round($datediff / (60 * 60 * 24));
        if($days < 31){
            return $this->errorResponse("Emi is not available!");
        }
        $installment = [];

        // Calulcate Monthly EMI
        if($sanitized['instalment_type'] == 'monthly'){
            $months = $this->calculateMonths($sanitized['booking_date'], $sanitized['checkin_date']);
            $installmentMonths = $months - 2;

            $firstEMIAmount = (25 / 100) * $sanitized['amount'];
            $remainingEMIAmount = $sanitized['amount'] - $firstEMIAmount;

            if($installmentMonths <= 0){
                return $this->errorResponse("Monthly Emi is not available!");
            }
            $EMIAmount = $remainingEMIAmount / $installmentMonths;
            $firstEMIDate = date("Y-m-d", strtotime("+1 month", $your_date));

            $installment[] = [
                'emi_date' => $firstEMIDate,
                'amount'   => (string) $firstEMIAmount,
            ];

            $nextEMIDate = $firstEMIDate;

            for ($i = 0; $i < $installmentMonths; $i++) {
                $nextEMIDate = date("Y-m-d", strtotime("+1 month", strtotime($nextEMIDate)));
                $installment[] = [
                    'emi_date' => $nextEMIDate,
                    'amount'   => number_format($EMIAmount, 2),
                ];
            }
        }

        $emiData = [
            'emi_available' => true,
            'data'          => $installment,
        ];

        return response()->json($emiData);
    }

    function calculateMonths($bookingDate, $checkinDate){

        $ts1 = strtotime($bookingDate);
        $ts2 = strtotime($checkinDate);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
        return $diff;
    }
}

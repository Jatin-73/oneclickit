<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class EMIController extends Controller
{
    public function emiCalculation(Request $request, $installment = [])
    {
        $validator = Validator::make($request->all(), [
            'booking_date'    =>  'required|date_format:Y-m-d|after_or_equal:now',
            'checkin_date'    =>  'required|date_format:Y-m-d|after_or_equal:booking_date',
            'amount'          =>  'required|numeric',
            'instalment_type' =>  'required|in:monthly,weekly,biweekly',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        // Validate inputs
        $sanitized = $validator->valid();

        // If the Checkin date is within 30 days then Emi is not available
        $booking_date = strtotime($sanitized['booking_date']);
        $datediff = strtotime($sanitized['checkin_date']) - strtotime($sanitized['booking_date']);
        $days = round($datediff / (60 * 60 * 24));
        if($days < 31){
            return $this->errorResponse("Emi is not available!");
        }
        
        // All EMI Should be completed before 14 days of check-in
        $checkin_date = date('Y-m-d', strtotime('-14 day', strtotime($sanitized['checkin_date'])));

        // Calulcate Monthly EMI
        if($sanitized['instalment_type'] == 'monthly'){
            $installmentMonths = $this->datediffInMonths($sanitized['booking_date'], $checkin_date);

            if($installmentMonths <= 0){
                return $this->errorResponse("Monthly EMI is not available!");
            }

            // First EMI Should be 25 % of total amount.
            $totalAmount = $sanitized['amount'];
            $firstEMIAmount = (25 / 100) * $sanitized['amount'];
            $remainingEMIAmount = $sanitized['amount'] - $firstEMIAmount;
            $firstEMIDate = $sanitized['booking_date'];
            $nextEMIDate = $firstEMIDate;

            if($installmentMonths >= 1){
                // Bind First EMI
                $installment[] = [
                    'emi_date' => $firstEMIDate,
                    'amount'   => (string) number_format($firstEMIAmount, 2),
                ];

                // $installmentMonths = ($installmentMonths > 1) ? $installmentMonths : $installmentMonths;
                $finalRemainingEMIAmount = $remainingEMIAmount / $installmentMonths;

                // Bind Loop Throgh Remaining EMI
                for ($i = 0; $i < $installmentMonths; $i++) {
                    $nextEMIDate = date("Y-m-d", strtotime("+1 month", strtotime($nextEMIDate)));
                    $installment[] = [
                        'emi_date' => $nextEMIDate,
                        'amount'   => number_format($finalRemainingEMIAmount, 2),
                    ];
                }
            }else{
                // Bind First EMI
                $installment[] = [
                    'emi_date' => $firstEMIDate,
                    'amount'   => (string) $totalAmount,
                ];
            }
        }
        // Calulcate Weekly EMI
        else if($sanitized['instalment_type'] == 'weekly'){
            $installmentWeeks = $this->datediffInBweeks($sanitized['booking_date'], $checkin_date);

            if($installmentWeeks <= 0){
                return $this->errorResponse("Weekly EMI is not available!");
            }

            // First EMI Should be 25 % of total amount.
            $totalAmount = $sanitized['amount'];
            $firstEMIAmount = (25 / 100) * $sanitized['amount'];
            $remainingEMIAmount = $sanitized['amount'] - $firstEMIAmount;
            $firstEMIDate = $sanitized['booking_date'];
            $nextEMIDate = $firstEMIDate;

            if($installmentWeeks >= 1){
                // Bind First EMI
                $installment[] = [
                    'emi_date' => $firstEMIDate,
                    'amount'   => (string) number_format($firstEMIAmount, 2),
                ];

                $finalRemainingEMIAmount = $remainingEMIAmount / $installmentWeeks;

                // Bind Loop Throgh Remaining EMI
                for ($i = 0; $i < $installmentWeeks; $i++) {
                    $nextEMIDate = date("Y-m-d", strtotime("+7 days", strtotime($nextEMIDate)));
                    $installment[] = [
                        'emi_date' => $nextEMIDate,
                        'amount'   => number_format($finalRemainingEMIAmount, 2),
                    ];
                }
            }else{
                // Bind First EMI
                $installment[] = [
                    'emi_date' => $firstEMIDate,
                    'amount'   => (string) $totalAmount,
                ];
            }
        }

        // Calulcate 15 Days EMI
        else if($sanitized['instalment_type'] == 'biweekly'){
            $installmentBiweeks = $this->datediffInBweeks($sanitized['booking_date'], $checkin_date);

            if($installmentBiweeks <= 0){
                return $this->errorResponse("Weekly EMI is not available!");
            }

            // First EMI Should be 25 % of total amount.
            $totalAmount = $sanitized['amount'];
            $firstEMIAmount = (25 / 100) * $sanitized['amount'];
            $remainingEMIAmount = $sanitized['amount'] - $firstEMIAmount;
            $firstEMIDate = $sanitized['booking_date'];
            $nextEMIDate = $firstEMIDate;

            if($installmentBiweeks >= 1){
                // Bind First EMI
                $installment[] = [
                    'emi_date' => $firstEMIDate,
                    'amount'   => (string) number_format($firstEMIAmount, 2),
                ];

                $finalRemainingEMIAmount = $remainingEMIAmount / $installmentBiweeks;

                // Bind Loop Throgh Remaining EMI
                for ($i = 0; $i < $installmentBiweeks; $i++) {
                    $nextEMIDate = date("Y-m-d", strtotime("+15 days", strtotime($nextEMIDate)));
                    $installment[] = [
                        'emi_date' => $nextEMIDate,
                        'amount'   => number_format($finalRemainingEMIAmount, 2),
                    ];
                }
            }else{
                // Bind First EMI
                $installment[] = [
                    'emi_date' => $firstEMIDate,
                    'amount'   => (string) $totalAmount,
                ];
            }
        }

        // Return EMI Data
        $emiData = [
            'emi_available' => true,
            'data'          => $installment,
        ];

        return response()->json($emiData);
    }

    function datediffInWeeks($date1, $date2)
    {
        $diff = strtotime($date2, 0) - strtotime($date1, 0);
        return floor($diff / 604800);
    }

    function datediffInBweeks($date1, $date2)
    {
        $diff = strtotime($date2, 0) - strtotime($date1, 0);
        return floor($diff / 1296000);
    }

    function datediffInMonths($bookingDate, $checkinDate){

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
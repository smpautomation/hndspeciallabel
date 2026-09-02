<?php

namespace App\Http\Controllers;

use App\Models\Datalist;
use App\Models\HNDModels;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class PrintController extends Controller
{
    private $ip_address;
    private $print_settings;

    public function index(Request $request)
    {
        $this->ip_address = $request->ip();
        $this->print_settings = Settings::where('ip_address', $this->ip_address)->first();

        return Inertia::render('HNDSpecialLabel', [
            'models'        => HNDModels::select('model_name', 'fixed_value')->get(),
            'userIp'        => $this->ip_address,
            'printerConfig' => [
                'satoIp'           => $this->print_settings->SATO_ip_address ?? '',
                'horizontalOffset' => $this->print_settings->horizontal_offset ?? '',
                'verticalOffset'   => $this->print_settings->vertical_offset ?? '',
            ],
            'flash' => session()->only(['error', 'success']),
        ]);
    }

    public function print(Request $request){
        $this->ip_address = $request->ip();
        $this->print_settings = Settings::where('ip_address', $this->ip_address)->first();


        try {
            $validated = $request->validate([
                'model_name' => 'required|string',
                'shipping_date' => 'required|date',
                'quantity' => 'required|numeric',
                'print_quantity' => 'nullable|numeric',
                'is_custom' => 'required|boolean'
            ]);

            $fixed_value = HNDModels::where('model_name', $validated['model_name'])->value('fixed_value');

        } catch (ValidationException $e) {
            Log::error('Validation failed', $e->errors());
            return to_route('hnd')->with('error', 'Validation Error: ' . $e->getMessage());
        }

        $formattedDate = Carbon::parse($validated['shipping_date'])->format('ymd');

        if($validated['is_custom']){
            $formattedPrintQuantity = str_pad($validated['print_quantity'], 4, 0, STR_PAD_LEFT);
            $lot = $formattedDate . '-' . $formattedPrintQuantity;

            if($this->Duplicate($validated, $lot) && Settings::where('ip_address', $this->ip_address)->where('role', 0)->exists()){
                Log::error('Duplicate Printing');
                return to_route('hnd')->with('error', 'Duplicate Printing');
            }

            $this->sendToPrinter([
                'sato_ip' => $this->print_settings->SATO_ip_address,
                'horizontal_offset' => $this->print_settings->horizontal_offset,
                'vertical_offset' => $this->print_settings->vertical_offset,
                'model_name' => $validated['model_name'],
                'fixed_value' => $fixed_value,
                'quantity' => $validated['quantity'],
                'lot' => $lot
            ]);

            Datalist::create([
                'ip_address' => $this->ip_address,
                'sato_ip' => $this->print_settings->SATO_ip_address,
                'model' => $validated['model_name'],
                'fixed_value' => $fixed_value,
                'quantity' => $validated['quantity'],
                'lot' => $lot
            ]);

            return to_route('hnd')->with('success', 'Printed Successfully');
        }

        for($i=1;$i<=$validated['print_quantity'];$i++){
            $formattedPrintQuantity = str_pad($i, 4, 0, STR_PAD_LEFT);
            $lot = $formattedDate . '-' . $formattedPrintQuantity;

            if($this->Duplicate($validated, $lot) && Settings::where('ip_address', $this->ip_address)->where('role', 0)->exists()){
                Log::error('Duplicate Printing');
                return to_route('hnd')->with('error', 'Duplicate Printing');
            }

            $this->sendToPrinter([
                'sato_ip' => $this->print_settings->SATO_ip_address,
                'horizontal_offset' => $this->print_settings->horizontal_offset,
                'vertical_offset' => $this->print_settings->vertical_offset,
                'model_name' => $validated['model_name'],
                'fixed_value' => $fixed_value,
                'quantity' => $validated['quantity'],
                'lot' => $lot
            ]);

            Datalist::create([
                'ip_address' => $this->ip_address,
                'sato_ip' => $this->print_settings->SATO_ip_address,
                'model' => $validated['model_name'],
                'fixed_value' => $fixed_value,
                'quantity' => $validated['quantity'],
                'lot' => $lot
            ]);
        }

        return to_route('hnd')->with('success', 'Printed Successfully');
    }

    private function sendToPrinter($data){
        $SATO_IP = $data['sato_ip'];
        $offsetH = $data['horizontal_offset'];
        $offsetV = $data['vertical_offset'];
        $Model = $data['model_name'];
        $Fixed_Value = $data['fixed_value'];
        $Quantity = $data['quantity'];
        $LotNo = $data['lot'];
        //$LotNo = str_replace("-", "", $data['lot']);

        $xData1 = str_pad(substr($Fixed_Value, 0, 3), 20, " ", STR_PAD_RIGHT);
        $xData2 = str_pad(substr($Fixed_Value, 3, 1), 22, " ", STR_PAD_RIGHT);
        $xData4 = str_pad($LotNo, 30, " ", STR_PAD_RIGHT);
        $xData5 = str_pad(substr($Fixed_Value, 4, 1), 20, " ", STR_PAD_RIGHT);

        $finalBarcode = $xData1 . $xData2 . $xData5 . $xData4;

        $fp = pfsockopen($SATO_IP, 9100);
        $xQRCode = strtoupper(urldecode($finalBarcode));
        $xQRCode = str_replace(" ", "", $xQRCode);
        $esc = chr(27);
        $data = "";
        $data .= $esc . 'A';
        $data .= $esc . 'A3H1374V0001';

        $data .= $esc . 'H' . sprintf("%04d", $offsetH) . $esc . 'V' . sprintf("%04d", $offsetV) . $esc . 'P2' . $esc . 'L0202' . $esc . 'OB' . strtoupper($Model);
        $data .= $esc . 'H' . sprintf("%04d", $offsetH + 10) . $esc . 'V' . sprintf("%04d", $offsetV + 60) . $esc . 'P2' . $esc . 'L0203' . $esc . 'XS' . strtoupper($LotNo);
        $data .= $esc . 'H' . sprintf("%04d", $offsetH) . $esc . 'V' . sprintf("%04d", $offsetV + 110) . $esc . 'P2' . $esc . 'L0202' . $esc . 'OB' . $Quantity . ' PCS';


        $data .= $esc . 'H' . sprintf("%04d", $offsetH) . $esc . 'V' . sprintf("%04d", $offsetV + 200) . $esc . 'P2'.$esc.'L0201'.$esc.'XS'."QR Code Detail:";
        $data .= $esc . 'H' . sprintf("%04d", $offsetH) . $esc . 'V' . sprintf("%04d", $offsetV + 230) . $esc . 'P2' . $esc . 'L0201' . $esc . 'XS' . str_replace(" ", "", $xData1);
        $data .= $esc . 'H' . sprintf("%04d", $offsetH) . $esc . 'V' . sprintf("%04d", $offsetV + 260) . $esc . 'P2' . $esc . 'L0201' . $esc . 'XS' . str_replace(" ", "", $xData2);
        $data .= $esc . 'H' . sprintf("%04d", $offsetH) . $esc . 'V' . sprintf("%04d", $offsetV + 290) . $esc . 'P2' . $esc . 'L0201' . $esc . 'XS' . str_replace(" ", "", $xData5);
        $data .= $esc . 'H' . sprintf("%04d", $offsetH) . $esc . 'V' . sprintf("%04d", $offsetV + 320) . $esc . 'P2' . $esc . 'L0201' . $esc . 'XS' . str_replace(" ", "", $xData4);

        //print qr code
        $data .= $esc . 'H' . sprintf("%04d", $offsetH + 325) . $esc . 'V' . sprintf("%04d", $offsetV + 275) . $esc . '2D30,L,12,0,0';
        $data .= $esc . 'DN' . sprintf("%04d", strlen($xQRCode)) . ',' . $xQRCode;

        $data .= $esc . 'Q1';
        $data .= $esc . 'Z' . $esc;
        $print_output = $data;

        fputs($fp, $print_output);
        fclose($fp);
    }

    public function saveSettings(Request $request){
        $this->ip_address = $request->ip();
        try {
            $validated = $request->validate([
                'sato_ip' => 'required|string',
                'horizontal_offset' => 'required|numeric',
                'vertical_offset' => 'required|numeric'
            ]);

        } catch (ValidationException $e) {
            Log::error('Validation failed', $e->errors());
            return to_route('hnd')->with('error', 'Validation Error: ' . $e->getMessage());
        }

        Settings::updateOrCreate(
            ['ip_address' => $this->ip_address],
            [
                'SATO_ip_address'   => $validated['sato_ip'],
                'horizontal_offset' => $validated['horizontal_offset'],
                'vertical_offset'   => $validated['vertical_offset'],
                'remarks' => " "
            ]
        );

        return to_route('hnd')->with('success', 'Printer settings saved successfully.');
    }

    private function Duplicate(array $validated, string $lot):bool{
        $duplicate = Datalist::where('model', $validated['model_name'])
                            ->where('lot', $lot)
                            ->where('quantity', $validated['quantity'])
                            ->exists();
        if(!$duplicate){
            return false;
        }

        return true;
    }
}

<?php

// Include the main TCPDF library (search for installation path).
require_once("tcpdf/tcpdf_include.php");
require_once("mail/class.phpmailer.php"); //library added in download source.

function generateToPDF($data){
    define('UPLOAD_DIR', 'tmp/');

    //if ($data) {
        //recibo la informacion y descodificamos el json

        $data = json_decode($data);

            //obtenemos la data del objeto Comercio
            //logo
            $logo = UPLOAD_DIR . 'pagadito.png';
            //comercio
            $comercio = $data->dataTransacction->comercio; 
            //$comercio = 'hola';
            //monto
            $monto = $data->dataTransacction->parametros->transaction->currency + '$' +$data->dataTransacction->value->amount;
            //$monto = "USD $15";
            //terminal
            $terminal = $data->dataTransacction->parametros->terminal->terminal_id;
            //$terminal = "1";
            //name
            $name = $data->dataTransacction->name;
            //$name = "Roberto Salguero";
            //fecha
            $fecha = $data->dataTransacction->value->date_trans;
            //$fecha = "24/10/2017";
            //referencia
            $referencia = $data->dataTransacction->value->reference;
            //$referencia = "ASFG10155";
            //card
            $card = $data->dataTransacction->last_card_numbers;
            //$card = "4785";
            //correo comercio
            //$emailComercio = $data->emailComercio;
            //$emailComercio = "rjsg10@gmail.com";
            //idcomercio
            $idcomercio = $data->dataTransacction->parametros->terminal->branch_office_id;
            //$idcomercio = "1";
            //tipo tarjeta
            $tipocard = $data->dataTransacction->type;
            //$tipocard = "VISA";
            //correo cliente
            $emailCliente = $data->mail_comprador;
            //$emailCliente = "rjsg10@gmail.com";
            //correo comercio
            //$emailComercio = "developers@pagadito.com";            
            $emailComercio = $data->dataTransacction->emailComercio;
            
    ///}

    $text ='<div id="page-content-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col-sm-3 " style="margin: 0 auto;float: none;padding: 0;">
                        <div class="">
                            <div class="">
                                <div class="row" style="    margin-bottom: 0 !important;">
                                    <div class="col-sm-12 center" style=" text-align: center;   font-size: 9px;">
                                        <img id="logo" src="'.$logo.'">
                                        <p id="comercio">'.$comercio.'</p>
                                    </div>
                                </div>
                                <div class="row" style="  font-size: 9px;  margin-bottom: 0 !important;">
                                    <div class="col-sm-6">
                                        COMERCIO ID
                                    </div>
                                    <div class="col-sm-6" style="margin-top: -13px; text-align:right">
                                        '.$idcomercio.'
                                    </div>
                                    <div class="col-sm-6">
                                        TERMINAL ID
                                    </div>
                                    <div class="col-sm-6" style="margin-top: -13px; text-align:right">
                                        '.$terminal.'
                                    </div>
                                </div>
                                <div class="row" style=" font-size: 10px; text-align: center;  margin-bottom: 0 !important;">
                                    <div class="col-sm-12 center ">
                                        <strong> VENTA</strong>
                                    </div>
                                </div>
                                <div class="row" style=" font-size: 10px;   margin-bottom: 0 !important;">
                                    <div class="col-sm-4 " style="    padding-right: 0;">
                                        <span> '.$referencia.' </span>
                                    </div>
                                    <div class="col-sm-8 right" style="text-align:right">
                                        <strong>
                                            <div>'.$tipocard.'<br></div>
                                            <div>'.$card.'<br></div>
                                            <div>'.$fecha.'<br></div>
                                        </strong>
                                    </div>
                                </div>
                                 <div class="row" style=" font-size: 12px;   margin-bottom: 0 !important;">
                                    <div class="col-sm-5 ">
                                    
                                       <strong> MONTO<strong>
                                    </div>
                                    <div class="col-sm-7 right" style="text-align:right">
                                        <strong>'.$monto.'<strong>
                                    </div>
                                </div>
                                <div class="row" style="    margin-bottom: 0 !important;">
                                    <div class="col-sm-12 center" style="  text-align: center;  font-size: 9px;">
                                        -- COPIA DE CLIENTE --
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>';

    $tipo = 1;

    generatePDF1($tipoArchivo = "VoucherCliente", $text, $emailCliente, $emailComercio, $tipo); //VOUCHER CLIENTE

    //firma
            $firma = $data->firma;
                $img = $firma;
                $img = str_replace('data:image/png;base64,', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);
                $file = UPLOAD_DIR . uniqid() . '.png';
                $success = file_put_contents($file, $data);

    $text = '<div id="page-content-wrapper">
             <div class="container">
                <div class="row">
                    <div class="col-sm-3 " style="margin: 0 auto;float: none;padding: 0;">
                        <div class="card">
                            <div class="card-content" style="padding: 15px">
                                <div class="row" style="  text-align: center;  margin-bottom: 0 !important;">
                                    <div class="col-sm-12 center ">
                                        <br><strong> PAGADITO POS </strong>
                                        <br><strong> '.$comercio.' </strong><br>
                                    </div>
                                </div>
                                <div class="row" style=" text-align: center; margin-bottom: 10px; margin-top: 10px ">
                                    <div class="col-sm-12 center ">
                                        <strong> VENTA</strong><br>
                                        <strong> '.$card.' <strong>
                                    </div>
                                </div>
                                <div class="row" style="  font-size: 11px; margin-bottom: 5px  ">
                                    <div class="col-sm-6">
                                        AUTORIZA:
                                    </div>
                                    <div class="col-sm-6" style="text-align:right; margin-top: -13px;">
                                        '.$referencia.'
                                    </div>
                                </div>
                                <div class="row" style="  font-size: 11px;margin-bottom: 5px  ">
                                    <div class="col-sm-6">
                                        TERMINAL ID :
                                    </div>
                                    <div class="col-sm-6" style="text-align:right;margin-top: -13px;">
                                        '.$terminal.'
                                    </div>
                                </div>
                                <div class="row" style="  font-size: 11px; margin-bottom: 5px  ">
                                    <div class="col-sm-6">
                                        FECHA:
                                    </div>
                                    <div class="col-sm-6" style="text-align:right;margin-top: -13px; font-size: 9px">
                                        '.$fecha.'
                                    </div>
                                </div>
                                <div class="row" style="  font-size: 11px; margin-bottom: 5px  ">
                                    <div class="col-sm-6">
                                        MONTO :
                                    </div>
                                    <div class="col-sm-6" style="text-align:right; margin-top: -13px;">
                                        '.$monto.'
                                    </div>
                                </div>
                                <div class="row" style="position: absolute;right: 0;bottom: 15px;left: 0;">
                                    <div class="row" style="  text-align: center; ">
                                        <div class="col-sm-12 center" style="    font-size: 11px;">
                                            -- ORIGINAL CLIENTE --
                                        </div><br><br>
                                    </div>
                                </div>
                                <div class="row" style="  text-align: center; ">
                                    <div class="col-sm-12 center" style="    font-size: 11px;">
                                        <img id="firma" src="'.$file.'" width="150" height="75">
                                    </div>
                                    <div class="col-sm-12 center" style="    font-size: 11px;">
                                        '.$name.'
                                    </div>
                                </div>
                                <div class="row" style=" text-align: center;  ">
                                    <div class="col-sm-12 center" style="    font-size: 11px;">
                                        * PAGO ELECTRÓNICO *
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>';

    $tipo = 2; 

    $voucher = generatePDF1($tipoArchivo = "VoucherComercio", $text, $emailCliente, $emailComercio, $tipo); //VOUCHER COMERCIO

    $response = new stdClass();
    return $response = $voucher;

}

    function generatePDF1($tipoArchivo, $text, $emailCliente, $emailComercio, $tipo){
        // create new PDF document
        $pdf1 = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // set document information
        //$pdf->SetCreator(PDF_CREATOR);
        $pdf1->SetAuthor('Pagadito');
        $pdf1->SetTitle('Pagadito');
        $pdf1->SetSubject('Pagadito');
        $pdf1->SetKeywords('Voucher, Pagadito');

        // set default monospaced font
        $pdf1->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf1->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // set auto page breaks
        $pdf1->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf1->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'tcpdf/lang/spa.php')) {
            require_once(dirname(__FILE__).'tcpdf/lang/spa.php');
            $pdf1->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf1->SetFont('times', '', 11);

        // add a page
        $pdf1->AddPage();

        $pdf1->writeHTML($text, true, false, true, false, '');

        // ---------------------------------------------------------

        //Close and output PDF document
        //$fileName = $tipoArchivo'.pdf';                
        $fileName = 'Voucher.pdf';                
        $attachment = $pdf1->Output($fileName, 'S');

        $sendMail = sendEmail($attachment, $text, $emailCliente, $emailComercio, $tipo); 

        $response = new stdClass();
        return $response = $sendMail;

        //============================================================+
        // END OF FILE
        //============================================================+
    }    


    function sendEmail($attachment, $text, $emailCliente, $emailComercio, $tipo){
        //INICIO HEADER DEL CORREO

        if ($tipo == 1) {
            $to   = $emailCliente;
            $from = $emailComercio;    
        }elseif ($tipo == 2) {
            $to   = $emailComercio;
            $from = $emailComercio;
        }

        $subj = 'Voucher Pagadito';

            global $error;
            $mail = new PHPMailer();
            $mail->CharSet = 'UTF-8';
            $mail->IsSendmail();
            $mail->SMTPDebug = 2;
            $is_gmail = true;
            $mail->SMTPAuth = true; 
            if($is_gmail){
                //$mail->SMTPSecure = 'ssl'; 
                $mail->Host = 'secure.emailsrvr.com';
                //$mail->Port = 465;  
                $mail->Username = 'pagaditopos@pagadito.com';  
                $mail->Password = 'U6O0rJTNof6li51uW7z0';   
            }else{
                $mail->Host = 'secure.emailsrvr.com';
                $mail->Username = 'pagaditopos@pagadito.com';  
                $mail->Password = 'U6O0rJTNof6li51uW7z0';
            }
            $mail->IsHTML(true);
            $mail->From=$emailComercio;
            $mail->FromName="Voucher Comercio Pagadito";
            $mail->Sender=$from; // indicates ReturnPath header
           // $mail->addCustomHeader();
            $mail->AddReplyTo($from, $mail->FromName); // indicates ReplyTo headers
            //$mail->AddCC('');
            //$mail->AddBCC(""); // Copia oculta
            $mail->Subject = $subj;
            $mail->MsgHTML($text);
            $mail->AltBody = "Gracias por su preferencia!"; // email_messageo sin html
            //$mail->AddAttachment($pdfString, "voucher.pdf");
            $mail->AddStringAttachment($attachment, "voucher.pdf");
            $mail->AddAddress($to);
            $response = new stdClass();
            if(!$mail->Send()){
                $error = 'Mail error: '.$mail->ErrorInfo;
                //echo $error;
                $response->status = false;
                $response->mailError = $error;
                $response->error = "aquii va el to ".$to;
            }else{
                $error = 'Correo Enviado!';
                $response->status = true;
            }
            return $response;
    }

?>
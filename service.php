<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
$method = null;

if (!empty($_POST["method"])) {
    $method = $_POST['method'];
}

switch ($method) {
    case 'transaction':
        transaction();
        break;

    case 'get_terminal_transaction':
        get_terminal_transaction();
        break;

    case 'get_terminal_transaction_mobil':
        get_terminal_transaction_mobil();
        break;    

    case 'getCodTurno':
        getCodTurno();
        break;        

    case 'void_transaction':
        void_transaction();
        break;
    case 'get_terminal_transactions_by_shift':
        get_terminal_transactions_by_shift();
        break;

    case 'get_terminal_transactions_by_shift_mobil':
        get_terminal_transactions_by_shift_mobil();
        break;

    case 'transaction_mobil':
        transaction_mobil();
        break;

    case 'void_transaction_mobil':
        void_transaction_mobil();
        break;

    case 'insertConfDispositivo':
        insertConfDispositivo();
        break;

    case 'insertConfDispositivo_mobil':
        insertConfDispositivo_mobil();
        break;        

    case 'initLogin':
        initLogin();
        break;

    case 'insertUsuariosSystem':
        insertUsuariosSystem();
        break;  

    case 'getAllUsuarios':
        getAllUsuarios();
        break;    

    case 'deleteUsuariosSystem':
        deleteUsuariosSystem();
        break;            

    case 'editUsuariosSystem':
        editUsuariosSystem();
        break;     

    case 'readValidWSK':
        readValidWSK();
        break;             
    
    case 'restorePasswordAdmin':
        restorePasswordAdmin();
        break;                       

    case 'initSystem':
        initSystem();
        break;     

    case 'getUsersPOS':
        getUsersPOS();
        break;

    case 'insertAsignarTurno':
        insertAsignarTurno();
        break; 

    case 'validCodeSupervisor':
        validCodeSupervisor();
        break;            

    case 'insertTransactions':
        insertTransactions();
        break;             

    case 'generatePDF':
        generatePDF();
        break;

    case 'generateVoidPDF':
        generateVoidPDF();
        break;

    case 'cerrarTurno':
        cerrarTurno();
        break;      

    case 'checkUserName':
        checkUserName();
        break;      

    case 'checkCommerceExist':
        checkCommerceExist();
        break;              

    case 'insertConfDispositivoNewSucursal':
        insertConfDispositivoNewSucursal();
        break;              

    case 'getCajeroTurnos':
        getCajeroTurnos();
        break;           
    
    case 'getSucursales':
        getSucursales();
        break;       

    case 'getDataCommerce':
        getDataCommerce();
        break;
    
    case 'updateConfDispositivo':
        updateConfDispositivo();
        break;

    case 'getAllShift':
         getAllShift();
         break;

    default:
        $response = new stdClass();
        $response->status = 'NOT WORKING';
        header('Content-Type: application/json');
        echo json_encode($response);
}

function transaction(){

    $response = new stdClass();
    
    try {

        //$config = $_POST['config'];
        
        // credenciales
        $credentials = $_POST['credentials'];
        
        // configuraciones
        if ($credentials['ambiente'] == 0) { //sandbox
            $CONF_CARD_PRESENT_WSDL = 'https://sandbox.pagadito.com/comercios/wspg/card_present.php?wsdl';
        }elseif ($credentials['ambiente'] == 1) { //produccion
            $CONF_CARD_PRESENT_WSDL = 'https://comercios.pagadito.com/wspg/card_present.php?wsdl';
        }
        
        $CONF_UID = $credentials['uid'];
        $CONF_WSK = $credentials['wsk'];
        $CONF_METODO_CIFRADO = 'AES-256-CBC';
        $CONF_LLAVE_CIFRADO = $credentials["llaveCifrado"];
        $CONF_IV_CIFRADO = $credentials["cifradoIV"];
        //$CONF_LLAVE_CIFRADO = 'kK9lm5nw2YRn3WXZtAja5yfIiNbqHqMR';
        //$CONF_IV_CIFRADO = 'w6Doza72zUUM5NiX';
        //$config = $_POST['config'];

        // terminal
        $terminal = $_POST['terminal'];

        // transaccion
        $transaction = $_POST['transaction'];

        // tarjeta
        $card = $_POST['card']; 

        //cifrar datos de tarjeta
        foreach ($card as $dato => $valor_plano) {
            $response->valor = $valor_plano;
            $card[$dato] = openssl_encrypt($valor_plano, $CONF_METODO_CIFRADO, $CONF_LLAVE_CIFRADO, 0, $CONF_IV_CIFRADO);
        }

        // armar parametros
        $params['credentials'] = $credentials;
        $params['transaction'] = $transaction;
        $params['terminal'] = $terminal;
        $params['card'] = $card;

        // instanciar cliente
        $WS_CARD_PRESENT_CLIENT = new SoapClient($CONF_CARD_PRESENT_WSDL, array(
                'trace' => 1,
                'exceptions' => TRUE,
                'cache_wsdl' => WSDL_CACHE_NONE
            )
        );

        // // llamar metodo
        $response = $WS_CARD_PRESENT_CLIENT->__soapCall('transaction', $params);
   
        $response->status = 'ok';
        $response->parametros = $params;

        
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function transaction_mobil(){
    
        $response = new stdClass();
        
        try {
    
            //$config = $_POST['config'];
            
            // credenciales
            $credentials = json_decode( $_POST['credentials']);
            
            // configuraciones
            if ($credentials->ambiente == 0) { //sandbox
                $CONF_CARD_PRESENT_WSDL = 'https://sandbox.pagadito.com/comercios/wspg/card_present.php?wsdl';
            }elseif ($credentials->ambiente == 1) { //produccion
                $CONF_CARD_PRESENT_WSDL = 'https://comercios.pagadito.com/wspg/card_present.php?wsdl';
            }

            $CONF_UID = $credentials->uid;
            $CONF_WSK = $credentials->wsk;
            $CONF_METODO_CIFRADO = 'AES-256-CBC';
            $CONF_LLAVE_CIFRADO = $credentials->llaveCifrado;
            $CONF_IV_CIFRADO = $credentials->cifradoIV;
            //$CONF_LLAVE_CIFRADO = 'kK9lm5nw2YRn3WXZtAja5yfIiNbqHqMR';
            //$CONF_IV_CIFRADO = 'w6Doza72zUUM5NiX';
            //$config = $_POST['config'];
    
            // terminal
            $terminal = json_decode( $_POST['terminal']);
    
            // details
            //$details = $_POST['details'];
    
            // transaccion
            $transaction = json_decode($_POST['transaction']);
    
            // tarjeta
            $card = json_decode( $_POST['card'],true); 
    
            //cifrar datos de tarjeta
            foreach ($card as $dato => $valor_plano) {
                $card[$dato] = openssl_encrypt($valor_plano, $CONF_METODO_CIFRADO, $CONF_LLAVE_CIFRADO, 0, $CONF_IV_CIFRADO);
            }
    
            // armar parametros
            $params['credentials'] = $credentials;
            $params['transaction'] = $transaction;
            $params['terminal'] = $terminal;
            $params['card'] = $card;
    
            // instanciar cliente
            $WS_CARD_PRESENT_CLIENT = new SoapClient($CONF_CARD_PRESENT_WSDL, array(
                    'trace' => 1,
                    'exceptions' => TRUE,
                    'cache_wsdl' => WSDL_CACHE_NONE
                )
            );
    
            // // llamar metodo
            $response = $WS_CARD_PRESENT_CLIENT->__soapCall('transaction', $params);
       
            $response->status = 'ok';
            $response->test = $CONF_CARD_PRESENT_WSDL;
            $response->parametros = $params;
    
            
        } catch (Exception $exc) {
            
            $response->status = 'fail';
            $response->mensaje = $exc->getMessage();
            
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    
}

function get_terminal_transaction(){

    $response = new stdClass();
    
    try {
        
       // credenciales
       $credentials = $_POST['credentials'];
       
       // configuraciones
        if ($credentials['ambiente'] == 0) { //sandbox
            $CONF_CARD_PRESENT_WSDL = 'https://sandbox.pagadito.com/comercios/wspg/card_present.php?wsdl';
        }elseif ($credentials['ambiente'] == 1) { //produccion
            $CONF_CARD_PRESENT_WSDL = 'https://comercios.pagadito.com/wspg/card_present.php?wsdl';
        }
        
        $CONF_UID = $credentials['uid'];
        $CONF_WSK = $credentials['wsk'];
        // datos
        $params = array();

        // credenciales
        $credentials = array(
            'uid' => $CONF_UID,
            'wsk' => $CONF_WSK
        );

        // terminal
        $terminal = $_POST['terminal'];

        // period
        $period = $_POST['period'];

        // armar parametros
        $params['credentials'] = $credentials;
        $params['terminal'] = $terminal;
        $params['period'] = $period;

        // instanciar cliente
        $WS_CARD_PRESENT_CLIENT = new SoapClient($CONF_CARD_PRESENT_WSDL, array(
            'trace' => 1,
            'exceptions' => TRUE,
            'cache_wsdl' => WSDL_CACHE_NONE
            )
        );

        $response = $WS_CARD_PRESENT_CLIENT->__soapCall('get_terminal_transactions', $params);
           
    } catch (Exception $exc) {
        
        $response->status = "fail: ".$exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    
}

function get_terminal_transaction_mobil(){

    $response = new stdClass();
    
    try {
        
       // credenciales
       //$credentials = $_POST['credentials'];
       $credentials = json_decode($_POST['credentials']);

        // configuraciones
         if ($credentials->ambiente == 0) { //sandbox
             $CONF_CARD_PRESENT_WSDL = 'https://sandbox.pagadito.com/comercios/wspg/card_present.php?wsdl';
         }elseif ($credentials->ambiente == 1) { //produccion
            $CONF_CARD_PRESENT_WSDL = 'https://comercios.pagadito.com/wspg/card_present.php?wsdl';
         }         
         
         $CONF_UID = $credentials->uid;
         $CONF_WSK = $credentials->wsk;
       
        // datos
        $params = array();

        // credenciales
        $credentials = array(
            'uid' => $CONF_UID,
            'wsk' => $CONF_WSK
        );

        // terminal
        $terminal = json_decode($_POST['terminal']);

        // period
        $period = json_decode($_POST['period']);

        // armar parametros
        $params['credentials'] = $credentials;
        $params['terminal'] = $terminal;
        $params['period'] = $period;

        // instanciar cliente
        $WS_CARD_PRESENT_CLIENT = new SoapClient($CONF_CARD_PRESENT_WSDL, array(
            'trace' => 1,
            'exceptions' => TRUE,
            'cache_wsdl' => WSDL_CACHE_NONE
            )
        );

        //$response = $params;
        $response = $WS_CARD_PRESENT_CLIENT->__soapCall('get_terminal_transactions', $params);
           
    } catch (Exception $exc) {
        
        $response->status = "fail: ".$exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    
}

function void_transaction(){

    $response = new stdClass();
    
    try {

        // credenciales
        $credentials = $_POST['credentials'];
        
        // configuraciones
        if ($credentials['ambiente'] == 0) { //sandbox
            $CONF_CARD_PRESENT_WSDL = 'https://sandbox.pagadito.com/comercios/wspg/card_present.php?wsdl';
        }elseif ($credentials['ambiente'] == 1) { //produccion
            $CONF_CARD_PRESENT_WSDL = 'https://comercios.pagadito.com/wspg/card_present.php?wsdl';
        }

        $CONF_UID = $credentials['uid'];
        $CONF_WSK = $credentials['wsk'];

        // datos
        $params = array();

        // credenciales
        $credentials = array(
            'uid' => $CONF_UID,
            'wsk' => $CONF_WSK
        );

        // terminal
        $transaction_reference = $_POST["transaction_reference"];
        $void_reason = $_POST["void_reason"];

        // armar parametros
        $params['credentials'] = $credentials;
        $params['transaction_reference'] = $transaction_reference;
        $params['void_reason'] = $void_reason;

        // instanciar cliente
        $WS_CARD_PRESENT_CLIENT = new SoapClient($CONF_CARD_PRESENT_WSDL, array(
            'trace' => 1,
            'exceptions' => TRUE,
            'cache_wsdl' => WSDL_CACHE_NONE
                )
        );

        // llamar metodo

        $response = $WS_CARD_PRESENT_CLIENT->__soapCall('void_transaction', $params);
     
        
        $response->status = 'ok';
        
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    
}

function void_transaction_mobil(){
    
        $response = new stdClass();
        
        try {
    
            // credenciales
            $credentials = json_decode($_POST['credentials']);
            
            // configuraciones
            if ($credentials->ambiente == 0) { //sandbox
                $CONF_CARD_PRESENT_WSDL = 'https://sandbox.pagadito.com/comercios/wspg/card_present.php?wsdl';
            }elseif ($credentials->ambiente == 1) { //produccion
                $CONF_CARD_PRESENT_WSDL = 'https://comercios.pagadito.com/wspg/card_present.php?wsdl';
            }

            $CONF_UID = $credentials->uid;
            $CONF_WSK = $credentials->wsk;
    
            // datos
            $params = array();
    
            // credenciales
            $credentials = array(
                'uid' => $CONF_UID,
                'wsk' => $CONF_WSK
            );
    
            // terminal
            $transaction_reference = $_POST["transaction_reference"];
            $void_reason = $_POST["void_reason"];
    
            // armar parametros
            $params['credentials'] = $credentials;
            $params['transaction_reference'] = $transaction_reference;
            $params['void_reason'] = $void_reason;
    
            // instanciar cliente
            $WS_CARD_PRESENT_CLIENT = new SoapClient($CONF_CARD_PRESENT_WSDL, array(
                'trace' => 1,
                'exceptions' => TRUE,
                'cache_wsdl' => WSDL_CACHE_NONE
                    )
            );
    
            // llamar metodo
    
            $response = $WS_CARD_PRESENT_CLIENT->__soapCall('void_transaction', $params);
         
            
            $response->status = 'ok';
            
        } catch (Exception $exc) {
            
            $response->status = 'fail';
            
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
        
}

function get_terminal_transactions_by_shift(){

    $response = new stdClass();
    
    try {
        
     // credenciales
     $credentials = $_POST['credentials'];
     
     // configuraciones
        if ($credentials['ambiente'] == 0) { //sandbox
            $CONF_CARD_PRESENT_WSDL = 'https://sandbox.pagadito.com/comercios/wspg/card_present.php?wsdl';
        }elseif ($credentials['ambiente'] == 1) { //produccion
            $CONF_CARD_PRESENT_WSDL = 'https://comercios.pagadito.com/wspg/card_present.php?wsdl';
        }
        
        $CONF_UID = $credentials['uid'];
        $CONF_WSK = $credentials['wsk'];
        // datos
        $params = array();
        
        // credenciales
        $credentials = array(
            'uid' => $CONF_UID,
            'wsk' => $CONF_WSK
        );
        
        // terminal
        $terminal = $_POST['terminal'];
        
        // shift code
        //$shift_code = 'ahsaa56';
        $shift_code = $_POST['shift_code'];
        
        // armar parametros
        $params['credentials'] = $credentials;
        $params['terminal'] = $terminal;
        $params['shift_code'] = $shift_code;
        
        // instanciar cliente
        $WS_CARD_PRESENT_CLIENT = new SoapClient($CONF_CARD_PRESENT_WSDL, array(
            'trace' => 1,
            'exceptions' => TRUE,
            'cache_wsdl' => WSDL_CACHE_NONE
                )
        );
        
        // llamar metodo
    
        $response = $WS_CARD_PRESENT_CLIENT->__soapCall('get_terminal_transactions_by_shift', $params);
       
        
        
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    
}

function get_terminal_transactions_by_shift_mobil(){

        date_default_timezone_set('America/El_Salvador');
        $response = new stdClass();
        $responseJson = new stdClass();
        
        try {
            
         // credenciales
         $credentials = json_decode($_POST['credentials']);
         
         // configuraciones
         if ($credentials->ambiente == 0) { //sandbox
             $CONF_CARD_PRESENT_WSDL = 'https://sandbox.pagadito.com/comercios/wspg/card_present.php?wsdl';
         }elseif ($credentials->ambiente == 1) { //produccion
            $CONF_CARD_PRESENT_WSDL = 'https://comercios.pagadito.com/wspg/card_present.php?wsdl';
         }         
         
         $CONF_UID = $credentials->uid;
         $CONF_WSK = $credentials->wsk;
            // datos
            $params = array();
            
            // credenciales
            $credentials = array(
                'uid' => $CONF_UID,
                'wsk' => $CONF_WSK
            );
            
            // terminal
            $terminal = json_decode($_POST['terminal']);
            
            // shift code
            //$shift_code = 'ahsaa56';
            $shift_code = $_POST['shift_code'];
            
            // armar parametros
            $params['credentials'] = $credentials;
            $params['terminal'] = $terminal;
            $params['shift_code'] = $shift_code;
            
            // instanciar cliente
            $WS_CARD_PRESENT_CLIENT = new SoapClient($CONF_CARD_PRESENT_WSDL, array(
                'trace' => 1,
                'exceptions' => TRUE,
                'cache_wsdl' => WSDL_CACHE_NONE
                    )
            );
            
            // llamar metodo
            $response = $WS_CARD_PRESENT_CLIENT->__soapCall('get_terminal_transactions_by_shift', $params);

            //tipo de reporte a solicitar
            $typeReport = $_POST['typeReport'];

            //funciones de conversión de horas
                function convertHour($xmlDateTransaction){
                    $dateTransaction = new DateTime($xmlDateTransaction);
                    $fechaTransaccion = $dateTransaction->format('d/m/Y H:i A');
                    return $fechaTransaccion;
                }
                
                function MinutesAdd($xmlDateTransaction){
                    $fechaTransaccionSinMinutosAdd = new DateTime($xmlDateTransaction);
                    $fechaTransaccionConMinutosAdd = $fechaTransaccionSinMinutosAdd->add(new DateInterval('PT30M'));
                    return $fechaTransaccionConMinutosAdd->format("Y-m-d H:i");
                }

            //capturo response de WS Pagadito donde trae la información en XML
            $xml = $response->value->xml_transactions;

            //Parseamos XML y se convierte en Array
            $xmlReturnPagadito = simplexml_load_string($xml);
            $jsonReturnPagadito = json_encode($xmlReturnPagadito); //to json
            $arrayReturnPagadito = json_decode($jsonReturnPagadito,TRUE); //to array

            if($response->value->num_transactions == 1){
                $countArray = 0;
            }else{
                $countArray = $response->value->num_transactions - 1;
            }
            
            $resultConvertWs=null;

            //Recorremos el Array y se hacen las validaciones pertinentes para mostrar el reporte deseado
            for($i = 0; $i <= $countArray; $i++){
                if($countArray != 0){
                    if((((new \DateTime())->format('Y-m-d H:i') <= MinutesAdd($arrayReturnPagadito["transaction"][$i]["transaction_datetime"]))) && ($arrayReturnPagadito["transaction"][$i]["transaction_status"] == "COMPLETED") && ($typeReport == 1)){//Anulaciones
                        $resultConvertWs['transaction'][] = array(
                            "transaction_status" => $arrayReturnPagadito["transaction"][$i]["transaction_status"],
                            "transaction_token" => $arrayReturnPagadito["transaction"][$i]["transaction_token"],
                            "transaction_ern" => $arrayReturnPagadito["transaction"][$i]["transaction_ern"],
                            "transaction_amount" => $arrayReturnPagadito["transaction"][$i]["transaction_amount"],
                            "transaction_datetime" => convertHour($arrayReturnPagadito["transaction"][$i]["transaction_datetime"]),
                            "transaction_reference" => $arrayReturnPagadito["transaction"][$i]["transaction_reference"],
                        );
                        $anullVoid = false;
                    }elseif ($typeReport == 2) {//Reporte Transacciones
                        $resultConvertWs['transaction'][] = array(
                            "transaction_status" => $arrayReturnPagadito["transaction"][$i]["transaction_status"],
                            "transaction_token" => $arrayReturnPagadito["transaction"][$i]["transaction_token"],
                            "transaction_ern" => $arrayReturnPagadito["transaction"][$i]["transaction_ern"],
                            "transaction_amount" => $arrayReturnPagadito["transaction"][$i]["transaction_amount"],
                            "transaction_datetime" => convertHour($arrayReturnPagadito["transaction"][$i]["transaction_datetime"]),
                            "transaction_reference" => $arrayReturnPagadito["transaction"][$i]["transaction_reference"],
                        );
                        $anullVoid = false;
                    }else{
                        $anullVoid = true;
                    }
                }else{
                    if((((new \DateTime())->format('Y-m-d H:i') <= MinutesAdd($arrayReturnPagadito["transaction"]["transaction_datetime"]))) && ($arrayReturnPagadito["transaction"]["transaction_status"] == "COMPLETED") && ($typeReport == 1)){//Anulaciones
                        $resultConvertWs['transaction'][] = array(
                            "transaction_status" => $arrayReturnPagadito["transaction"]["transaction_status"],
                            "transaction_token" => $arrayReturnPagadito["transaction"]["transaction_token"],
                            "transaction_ern" => $arrayReturnPagadito["transaction"]["transaction_ern"],
                            "transaction_amount" => $arrayReturnPagadito["transaction"]["transaction_amount"],
                            "transaction_datetime" => convertHour($arrayReturnPagadito["transaction"]["transaction_datetime"]),
                            "transaction_reference" => $arrayReturnPagadito["transaction"]["transaction_reference"],
                        );
                        $anullVoid = false;
                    }elseif ($typeReport == 2) {//Reporte Transacciones
                        $resultConvertWs['transaction'][] = array(
                            "transaction_status" => $arrayReturnPagadito["transaction"]["transaction_status"],
                            "transaction_token" => $arrayReturnPagadito["transaction"]["transaction_token"],
                            "transaction_ern" => $arrayReturnPagadito["transaction"]["transaction_ern"],
                            "transaction_amount" => $arrayReturnPagadito["transaction"]["transaction_amount"],
                            "transaction_datetime" => convertHour($arrayReturnPagadito["transaction"]["transaction_datetime"]),
                            "transaction_reference" => $arrayReturnPagadito["transaction"]["transaction_reference"],
                        );
                        $anullVoid = false;
                    }else{
                        $anullVoid = true;
                    }
                }
            }

            //Convertimos Array en XML
            $xmlConvertWs = new DOMDocument('1.0', 'UTF-8');
            header('Content-Type: application/json');
            $rootNode = $xmlConvertWs->appendChild($xmlConvertWs->createElement("terminal_transactions"));
            if(is_array($resultConvertWs['transaction'])){
                foreach ($resultConvertWs['transaction'] as $transaction) {
                    if (! empty($transaction)) {
                        $itemNode = $rootNode->appendChild($xmlConvertWs->createElement('transaction'));
                        foreach ($transaction as $k => $v) {
                            $itemNode->appendChild($xmlConvertWs->createElement($k, $v));
                        }
                    }
                }
            }
            $xmlConvertWs->formatOutput = true;
            $xmlReturnWs = $xmlConvertWs->saveXML();


            //Convertimos XML en JSON para envió de respuesta completa a la app
            $xmlReturnWsForArray = simplexml_load_string($xmlReturnWs);
            $jsonReturnWs = json_encode($xmlReturnWsForArray); //to json
            $arrayReturnWs = json_decode($jsonReturnWs,TRUE); //to array

            //Construimos JSON en stdClass()
                $responseJson->code = $response->code; //first object 
                $responseJson->message = $response->message; //first object
                $responseJson->anullVoid = $anullVoid;
                $responseJson->value = new stdClass(); //first object
                $responseJson->value->token = $response->value->token; //second object
                $responseJson->value->branch_office_id = $response->value->branch_office_id; //second object
                $responseJson->value->terminal_id = $response->value->terminal_id; //second object
                $responseJson->value->shift_code = $response->value->shift_code; //second object
                $responseJson->value->num_transactions = $response->value->num_transactions; //second object
                $responseJson->value->xml_transactions = $xmlReturnWs; //second object
                $responseJson->datetime = $response->datetime; //first object
                
        } catch (Exception $exc) {
            
            $responseJson->status = 'fail';
            
        }

        echo json_encode($responseJson); //end
        
}

function getCodTurno(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("getCodTurno", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function insertConfDispositivo(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("insertConfDispositivo", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function insertConfDispositivo_mobil(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $comercio = json_decode($_POST['comercio']);
        $dispositivo = json_decode($_POST['dispositivo']);
        $infoUsuario = json_decode($_POST['infoUsuario']);

        $params['comercio'] = $comercio;
        $params['dispositivo'] = $dispositivo;
        $params['infoUsuario'] = $infoUsuario;

        //llamar metodo
        $response = $client->__soapCall("insertConfDispositivo", $params);
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        $response->params = $params;
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function initLogin(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("initLogin", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);  

}

function insertUsuariosSystem(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("insertUsuariosSystem", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function getAllUsuarios(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("getAllUsuarios", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function deleteUsuariosSystem(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("deleteUsuariosSystem", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function editUsuariosSystem(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("editUsuariosSystem", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function readValidWSK(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("readValidWSK", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function restorePasswordAdmin(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("restorePasswordAdmin", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function initSystem(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $mac = $_POST['mac'];

        //llamar metodo
        $response = $client->__soapCall("initSystem", array($mac));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function getUsersPOS(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("getUsersPOS", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function insertAsignarTurno(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("insertAsignarTurno", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function validCodeSupervisor(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("validCodeSupervisor", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function insertTransactions(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("insertTransactions", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function generatePDF(){

    // Incluyo libreria generadora de PDF móvil
    include 'lib/generatePDF.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = generateToPDF($params);
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function generateVoidPDF(){

    // Incluyo libreria generadora de PDF móvil
    include 'lib/generateVoidPDF.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = generateVoidToPDF($params);
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function cerrarTurno(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("cerrarTurno", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}

function checkUserName(){
    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['username'];

        //llamar metodo
        $response = $client->__soapCall("checkUserName", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function checkCommerceExist(){
    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("checkCommerceExist", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function insertConfDispositivoNewSucursal(){
    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("insertConfDispositivoNewSucursal", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getCajeroTurnos(){
    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("getCajeroTurnos", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getSucursales(){
    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("getSucursales", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getDataCommerce(){
    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("getDataCommerce", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function updateConfDispositivo(){
    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("updateConfDispositivo", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getAllShift(){

    // Includs client to get $client object
    include 'lib/client.php';

    $response = new stdClass();
    
    try {

        // parametros
        $params = $_POST['param'];

        //llamar metodo
        $response = $client->__soapCall("getAllShift", array($params));
   
    } catch (Exception $exc) {
        
        $response->status = 'fail';
        $response->mensaje = $exc->getMessage();
        
    }

    header('Content-Type: application/json');
    echo json_encode($response);

}
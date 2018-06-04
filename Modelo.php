<?php

require_once "crudpdo.php";
require_once "config.php";

class Modelo {

	/**
	* Primer inicio del sistema
	* @param trae la dirección MAC del dispositivo
	* @return 1 -> Login, 2 -> Wizard, 3 -> No se puede usar
	*/
	public function initSystem($param){
		try{
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
			
			$mac = $param;

			$sql = 'SELECT setup, mac, WSK, UID, branchid, terminalid 
					FROM dispositivo INNER JOIN comercio ON (comercio_idcomercio = idcomercio)
					WHERE mac = "'.$mac.'"'; 
			
			$params = array(
					"mac" => $mac,
				);

			$row = $database->getRow($sql, $params);

			$response = new stdClass();
			
			if(($row['mac'] == $mac) && ($row['setup'] == 1)){ //Caso Setup 1 AND Mac 1 then login
				
				$response->status = 1;
				
			}elseif (($row['mac'] == '') && ($row['setup'] == '')){ //caso Setup 0 AND mac 0 then Wizard

				$response->status = 2;

			}elseif (($row['mac'] == '') && ($row['setup'] == 1)){ //caso no se puede acceder

				$response->status = 3;
				
			}

			return $response;

		} catch (PDOException $e) {
            return $e;
        }
	}

	/**
	* Obtenr usuarios cajeros
	* @param trae id del dispositivo del comercio
	* @return 
	*/
	public function getUsersPOS($params){
		try{
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);
			
			$idComercio = $data->infoComercio->idComercio;
			$cashier = $data->infoComercio->cashier;

			if($cashier == "1"){//cajeros para POS
				$sql = 'SELECT u.idUser, u.username, u.removeAt FROM usuarios u INNER JOIN dispositivo d ON (u.idDispositivo = d.idDispositivo) WHERE d.comercio_idcomercio = "'.$idComercio.'" AND idPrivilegio = "3" AND removeAt = "0"';
			}else{
				$sql = 'SELECT u.idUser, u.username, u.removeAt FROM usuarios u INNER JOIN dispositivo d ON (u.idDispositivo = d.idDispositivo) WHERE d.comercio_idcomercio = "'.$idComercio.'" AND idPrivilegio = "3" order by removeAt ASC, idUser';
			}
			
			$params = array(
				"idDispositivo" => $idDispositivo,
			);

			$getrows = $database->getRows($sql, $params);

			$response = new stdClass();
			
			if($getrows == true){
				$response->status = true;
				$response->getUsersPOS = $getrows;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;

		} catch (PDOException $e) {
            return $e;
        }
	}	

	/**
	* Recibe infomación del usuario
	* @params $condition (optional)
	* @return (array) mixed
	*/
	public function getAllUsuarios($params){

		try {
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			//infoUsuario
			$idComercio = $data->infoUsuario->idComercio;
			$idPrivilegio = $data->infoUsuario->idPrivilegio;

			if($idPrivilegio == 2){
				$sql = 'SELECT u.idUser, u.nombres, u.apellidos, p.rol, u.username, u.password, u.codigoAprobacion FROM usuarios u INNER JOIN privilegios p ON (u.idPrivilegio = p.idPrivilegio) INNER JOIN dispositivo d ON (u.idDispositivo = d.idDispositivo) WHERE p.idPrivilegio = "3" AND removeAt = "0" AND d.comercio_idcomercio = "'.$idComercio.'"';
			}else{
				$sql = 'SELECT u.idUser, u.nombres, u.apellidos, p.rol, u.username, u.password, u.codigoAprobacion FROM usuarios u INNER JOIN privilegios p ON (u.idPrivilegio = p.idPrivilegio) INNER JOIN dispositivo d ON (u.idDispositivo = d.idDispositivo) WHERE (p.idPrivilegio BETWEEN 2 AND 3) AND removeAt = "0" AND d.comercio_idcomercio = "'.$idComercio.'"';
			}
			
			$get = $database->getRows($sql);
			
			$response = new stdClass();
			
			if($get == true){
				$response->status = true;
				$response->allUsers = $get;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;
		
		} catch (PDOException $e) {
            return $e;
        }				
	}

	/**
	* Insert tipoDispositivo, comercio & dispositivo
	* @params 
	* @return 
	*/
	public function insertConfDispositivo($params){

		try {
			
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			//comercio
			$wsk = $data->comercio->wsk;
			$uid = $data->comercio->uid;
			$nombreComercio = $data->comercio->nombreComercio;
			$emailComercio = $data->comercio->emailComercio;
			$numeroRegistro = $data->comercio->numeroRegistro;

			//dispositivo
			$nombreSucursal = $data->dispositivo->nombreSucursal;
			$branchid = $data->dispositivo->branchid;
			$terminalid = $data->dispositivo->terminalid;
			$llaveCifrado = $data->dispositivo->llaveCifrado;
			$cifradoIV = $data->dispositivo->cifradoIV;
			$mac = $data->dispositivo->mac;
			$moneda = $data->dispositivo->moneda;
			$ambiente = $data->dispositivo->ambiente;
			$tipoDispositivo = $data->dispositivo->tipoDispositivo;
			$setup = '1';

			//infoUsuario
			$nombres = $data->infoUsuario->nombres;
			$apellidos = $data->infoUsuario->apellidos;
			$idPrivilegio = $data->infoUsuario->idPrivilegio;
			$username = $data->infoUsuario->username;
			$pass = $data->infoUsuario->password;
			$options = ['cost' => 15];
			$password = password_hash($pass, PASSWORD_BCRYPT, $options);

			//insert table tipoDispositivo
			$sql1 = "INSERT INTO tipoDispositivo (tipoDispositivo) VALUES ('$tipoDispositivo')";
			$params1 = array(
						"tipoDispositivo" => $tipoDispositivo,
					);

			$idTipoDispositivo = $database->insertRowid($sql1, $params1);
			
			//insert table comercio
			$sql2 = "INSERT INTO comercio (WSK, UID, nombreComercio, emailComercio, numeroRegistro) VALUES ('$wsk', '$uid', '$nombreComercio', '$emailComercio', '$numeroRegistro')";
			$params2 = array(
						"WSK" => $wsk,
						"UID" => $uid,
						"nombreComercio" => $nombreComercio,
						"emailComercio" => $emailComercio,
						"numeroRegistro" => $numeroRegistro,
					);

			$idComercio = $database->insertRowid($sql2, $params2);

			//insert table dispositivo
			$sql3 = "INSERT INTO dispositivo (nombreSucursal, branchid, terminalid, llaveCifrado, cifradoIV, mac, moneda, ambiente, setup, comercio_idcomercio, idTipoDispositivo, userCreation) VALUES ('$nombreSucursal', '$branchid', '$terminalid', '$llaveCifrado', '$cifradoIV', '$mac', '$moneda', '$ambiente', '$setup', '$idComercio', '$idTipoDispositivo', 'Admin')";
			$params3 = array(
						"nombreSucursal" => $nombreSucursal,
						"branchid" => $branchid,
						"terminalid" => $terminalid,
						"llaveCifrado" => $llaveCifrado,
						"cifradoIV" => $cifradoIV,
						"mac" => $mac,
						"moneda" => $moneda,
						"ambiente" => $ambiente,
						"setup" => $setup,
						"comercio_idcomercio" => $idComercio,
						"idTipoDispositivo" => $idTipoDispositivo,
						"userCreation" => 'Admin',
					);

			$idDispositivo = $database->insertRowid($sql3, $params3);

			//insert table usuarios
			$sql4 = "INSERT INTO usuarios (nombres, apellidos, idPrivilegio, username, password, idDispositivo, userCreation) VALUES ('$nombres', '$apellidos', '$idPrivilegio', '$username', '$password', '$idDispositivo', 'Admin')";
			$params4 = array(
						"nombres" => $nombres,
						"apellidos" => $apellidos,
						"idPrivilegio" => $idPrivilegio,
						"username" => $username,
						"password" => $password,
						"idDispositivo" => $idDispositivo,
						"userCreation" => 'Admin',
					);	

			$get = $database->insertRow($sql4, $params4);
			
			$response = new stdClass();
			
			if($get == true){
				$response->status = true;
			}else{
				$response->status = false;
				$response->sql1 = $sql1;
				$response->sql2 = $sql2;
				$response->sql3 = $sql3;
				$response->sql4 = $sql4;
			}

			return $response;
		
		} catch (PDOException $e) {
            return $e;
        }	
	}

	/**
	* Init Login
	* @params $username, $password
	* @return true / false
	*/
	public function initLogin($params){

		try {
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);
			
			$username = $data->initLogin->username;
			$password = $data->initLogin->password;
			$mac = $data->initLogin->mac;

			$sql = 'SELECT d.comercio_idcomercio, u.password, u.username, u.idPrivilegio, u.nombres, u.apellidos, u.idUser
					FROM (dispositivo d	INNER JOIN comercio c ON (d.comercio_idcomercio = c.idcomercio))
					INNER JOIN usuarios u ON (u.idDispositivo = d.idDispositivo)
					WHERE username = "'.$username.'" AND removeAt = "0"';

			$params = array(
				"username" => $username,
			);

			$row = $database->getRow($sql, $params);
			
			$initLoginData = new stdClass();

			if(password_verify(trim($password),$row['password'])){
            //if($row['password'] == md5($password)){
				$idComercio = $row['comercio_idcomercio'];	

				$sql1 = 'SELECT c.WSK,
								c.UID,
								c.nombreComercio,
								c.emailComercio,
								c.numeroRegistro,
								d.idDispositivo,
								d.nombreSucursal,
								d.comercio_idcomercio,
								d.branchid,
								d.terminalid,
								d.llaveCifrado,
								d.cifradoIV,
								d.moneda,
								d.ambiente
						FROM dispositivo d INNER JOIN comercio c ON (d.comercio_idcomercio = c.idcomercio)
						WHERE d.comercio_idcomercio = "'.$idComercio.'" AND d.mac = "'.$mac.'"';
			
				$params1 = array(
					"comercio_idcomercio" => $idComercio,
					"mac" => $mac,
				);

				$row1 = $database->getRow($sql1, $params1);

				if($row1 == false){	
					$initLoginData->status = false;
					$initLoginData->error = $sql1;
				}else{
					$initLoginData->username = $row['username'];
					$initLoginData->idPrivilegio = $row['idPrivilegio'];
					$initLoginData->nombreCompleto = $row['nombres'].' '.$row['apellidos'];
					$initLoginData->idUser = $row['idUser'];
					$initLoginData->idDispositivo = $row1['idDispositivo'];	
					$initLoginData->WSK = $row1['WSK'];	
					$initLoginData->UID = $row1['UID'];	
					$initLoginData->nombreComercio = $row1['nombreComercio'];
					$initLoginData->nombreSucursal = $row1['nombreSucursal'];
					$initLoginData->emailComercio = $row1['emailComercio'];
					$initLoginData->numeroRegistro = $row1['numeroRegistro'];
					$initLoginData->idComercio = $row1['comercio_idcomercio'];	
					$initLoginData->branchid = $row1['branchid'];	
					$initLoginData->terminalid = $row1['terminalid'];
					$initLoginData->llaveCifrado = $row1['llaveCifrado'];
					$initLoginData->cifradoIV = $row1['cifradoIV'];
					$initLoginData->moneda = $row1['moneda'];
					$initLoginData->ambiente = $row1['ambiente'];				
					$initLoginData->status = true;
				}

			}else{//sí la contraseña no coincide
				$initLoginData->status = false;
				$initLoginData->error = $sql;
			}

			return $initLoginData;

		} catch (PDOException $e) {
            return $e;
        }		
	}

	/**
	* Recibe infomación del idUser
	* @params $idUser
	* @return turnoCod
	*/
	public function getCodTurno($params){
		try {

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$idUser = $params;
			$horaActual = date("Y-m-d H:i:s");
			
			$sql = 'SELECT codeShift, turnoCod, idturno
					FROM turno
					WHERE idUser = "'.$idUser.'" AND estado = "1" AND fechaInicio <= "'.$horaActual.'" AND fechaFin IS NULL';
			
			$params = array(
				"idUser" => $idUser,
				"estado" => "1",
				"fechaInicio" => $horaActual
			);

			$row = $database->getRow($sql, $params);
			
			$response = new stdClass();

			if($row){
				$response->turnoCod = $row['turnoCod'];
				$response->codeShift = $row['codeShift'];
				$response->idTurno = $row['idturno'];
				$response->status = true;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;			

		} catch (PDOException $e) {
            return $e;
        }
	}		

	/**
	* Insert Usuarios del sistema.
	* @params 
	* @return 
	*/
	public function insertUsuariosSystem($params){

		try {
			
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			//infoUsuario
			$nombres = $data->infoUsuario->nombres;
			$apellidos = $data->infoUsuario->apellidos;
			$idPrivilegio = $data->infoUsuario->idPrivilegio;
			$username = $data->infoUsuario->username;
			$pass = $data->infoUsuario->password;
			$codigoAprobacion = $data->infoUsuario->codigoAprobacion;
			$options = ['cost' => 15];
			$password = password_hash($pass, PASSWORD_BCRYPT, $options);
			$codigoAprobacion = ($codigoAprobacion == '' ? null : password_hash($codigoAprobacion, PASSWORD_BCRYPT, $options));
			$idDispositivo = $data->infoUsuario->idDispositivo;
			
			//insert table usuarios
			$sql = "INSERT INTO usuarios (nombres, apellidos, idPrivilegio, username, password, codigoAprobacion, idDispositivo, userCreation) VALUES ('$nombres', '$apellidos', '$idPrivilegio', '$username', '$password', '$codigoAprobacion', '$idDispositivo', 'Admin')";
			$params = array(
						"nombres" => $nombres,
						"apellidos" => $apellidos,
						"idPrivilegio" => $idPrivilegio,
						"username" => $username,
						"password" => $password,
						"idDispositivo" => $idDispositivo,
                        "codigoAprobacion" => $codigoAprobacion,
						"userCreation" => 'Admin',
					);	

			$get = $database->insertRow($sql, $params);
			
			$response = new stdClass();
			
			if($get == true){
				$response->status = true;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;
		
		} catch (PDOException $e) {
            return $e;
        }	
	}

	/**
	* Delete usuarios del sistema
	* @params $idUser
	* @return true / false
	*/
	public function deleteUsuariosSystem($params){
		try{

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			$idUser = $data->deleteUser->idUser;

			//$sql = 'DELETE FROM usuarios WHERE idUser="'.$idUser.'"';
			$sql = 'UPDATE usuarios SET removeAt="1", fechaModificacion=current_timestamp WHERE idUser="'.$idUser.'"';
			$params = array(
			    "idUser" => $idUser,
			);

			$getrows = $database->updateRow($sql, $params);

			$response = new stdClass();

			if($getrows){
				$response->status = true;
				$response->sql = $sql;
			}else{
				$response->status = false;
				$response->error = $sql;
				$response->errort = $getrows;
			}

			return $response;

		} catch (PDOException $e) {
            return $e;
        }
	}

	/**
	* Editar usuarios del sistema
	* @params $idUser, $nombres, $apellidos, $username, $idPrivilegio, $password
	* @return true / false
	*/
	public function editUsuariosSystem($params){
		try{

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			$nombres = $data->editUser->nombres;
			$apellidos = $data->editUser->apellidos;
			$username = $data->editUser->username;
			$pass = $data->editUser->password;
			$codigoAprobacion = $data->editUser->codigoAprobacion;
			$options = ['cost' => 15];
			$password = password_hash($pass, PASSWORD_BCRYPT, $options);
			$codigoAprobacion = password_hash($codigoAprobacion, PASSWORD_BCRYPT, $options);
			$idDispositivo = $data->editUser->idDispositivo;
			$idUser = $data->editUser->idUser;

			$sql = 'UPDATE usuarios SET nombres = "'.$nombres.'", apellidos = "'.$apellidos.'", username = "'.$username.'", password = "'.$password.'", idDispositivo = "'.$idDispositivo.'", codigoAprobacion = "'.$codigoAprobacion.'", fechaModificacion = current_timestamp WHERE idUser="'.$idUser.'"';

			$params = array(
				"nombres" => $nombres,
				"apellidos" => $apellidos,
				"username" => $username,
				"password" => $password,
				"idDispositivo" => $idDispositivo,
				"codigoAprobacion" => $codigoAprobacion,
				"idUser" => $idUser,
			);

			$getrows = $database->updateRow($sql, $params);
			$response = new stdClass();

			if($getrows == true){
				$response->status = true;
				$response->error = $sql;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;

		} catch (PDOException $e) {
            return $e;
        }
	}

	/**
	* Recibe infomación del WSK para restaurar contraseña
	* @params $wsk
	* @return (array) mixed
	*/
	public function readValidWSK($params){
		try {

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			$wsk = $data->validWSK->wsk;
			
			$sql = 'SELECT idUser, WSK
					FROM (dispositivo INNER JOIN comercio ON (dispositivo.comercio_idcomercio = comercio.idcomercio))
     				INNER JOIN usuarios ON (usuarios.idDispositivo = dispositivo.idDispositivo)
					WHERE WSK = "'.$wsk.'" AND idPrivilegio = "1"';
			
			$params = array(
				"WSK" => $wsk,
			);

			$row = $database->getRow($sql, $params);
			
			$response = new stdClass();

			if($row['WSK'] == $wsk){
				$response->idUser = $row['idUser'];
				$response->status = true;
				$response->sql = $sql;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;			

		} catch (PDOException $e) {
            return $e;
        }
	}		

	/**
	* Recibe infomación de la nueva contraseña para restaurar usuario Administrador
	* @params $newpassword
	* @return (array) mixed
	*/
	public function restorePasswordAdmin($params){
		try {

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			$newpassword = $data->restorePswd->newpassword;
			$options = ['cost' => 15];
			$newpassword = password_hash($newpassword, PASSWORD_BCRYPT, $options);
			$idUser = $data->restorePswd->idUser;
			
			$sql = 'UPDATE usuarios SET password = "'.$newpassword.'" WHERE idUser="'.$idUser.'"';

			$params = array(
				"password" => $newpassword,
				"idUser" => $idUser,
			);

			$getrows = $database->updateRow($sql, $params);

			$response = new stdClass();

			if($getrows == true){
				$response->status = true;
				$response->sql = $sql;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;			

		} catch (PDOException $e) {
            return $e;
        }
	}		

	/**
	* Recibe código del supervisor para confirmar autorización
	* @params $code
	* @return (array) mixed
	*/
	public function validCodeSupervisor($params){
		try {

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			$code = $data->validCodeSupervisor->code;
			$idComercio = $data->validCodeSupervisor->idComercio;

			$sql = 'SELECT DISTINCT codigoAprobacion
					FROM usuarios u INNER JOIN dispositivo d ON (u.idDispositivo = d.idDispositivo)
					WHERE u.idPrivilegio = "2" AND removeAt = "0" AND u.codigoAprobacion <> "" AND d.comercio_idcomercio ="'.$idComercio.'"';
			
			$params = array(
				"comercio_idcomercio" => $idComercio,
			);

			$getrows = $database->getRows($sql, $params);
			$response = new stdClass();

			foreach ($getrows as $value) {
				if(password_verify(trim($code),$value['codigoAprobacion'])){
					$response->status = true;
					break;
				}else{
					$response->status = false;
					$response->error = $sql;
				}
			}

			return $response;			

		} catch (PDOException $e) {
            return $e;
        }
	}	


	/**
	* Insert asignación de turno.
	* @params 
	* @return 
	*/
	public function insertAsignarTurno($params){

		try {
			
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			//Captura de datos
			$idDispositivo = $data->asignarTurno->idDispositivo;
			$cmbUserAsign = $data->asignarTurno->cmbUserAsign;
			$idUserSupervisor = $data->asignarTurno->idUserSupervisor;
			$response = new stdClass();

			function CaptchaRandom(){
			    $salida	= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
				$length = 5;
			    $string = "";
			    $salida = str_split($salida,1);
			    for($i=1; $i<=$length; $i++){
			        mt_srand((double)microtime() * 1000000);
			        $num = mt_rand(1,count($salida));
			        $string .= $salida[$num-1];
			    }
			    return $string;
			}

			function CodeVisualShift(){
				$hoy = getdate();
					$year = $hoy["year"];
					$year = substr( $year, -2);
					if($hoy["mon"] < 10){
						$mon = '0'.$hoy["mon"];
					}else{
						$mon = $hoy["mon"];
					}
					if($hoy["mday"] < 10){
						$mday = '0'.$hoy["mday"];
					}else{
						$mday = $hoy["mday"];
					}
					if($hoy["hours"] < 10){
						$hours = '0'.$hoy["hours"];
					}else{
						$hours = $hoy["hours"];
					}
					if($hoy["minutes"] < 10){
						$min = '0'.$hoy["minutes"];
					}else{
						$min = $hoy["minutes"];
					}

					return $year.''.$mon.''.$mday.''.$hours.''.$min;
			}

			$sqlS = 'SELECT COUNT(*) AS SHIFT FROM turno WHERE estado = "1" AND idUser = "'.$cmbUserAsign.'"';
			$params = array(
				"idUser" => $cmbUserAsign,
			);
			$countShift = $database->getRow($sqlS, $params);
			
			if($countShift['SHIFT'] == "0"){

				$string = CaptchaRandom();
				$contador = 1;

				/* $sql = 'SELECT t.turnoCod, u.idDispositivo
						FROM turno t
     					INNER JOIN usuarios u ON (t.idUser = u.idUser)
						 WHERE u.idDispositivo = "'.$idDispositivo.'"'; */
				$sql = 'SELECT turnoCod FROM turno';
				$getrows = $database->getRows($sql);
					//print_r($getrows);
				if (!$getrows) {
						//echo "estoy en si esta vacio\n";
						$Codigo = $string;
				}else{
					while ( $contador > 0) {
						//echo "entre al while\n".$string."\n";
						foreach ($getrows as $value) {
						//echo "entre al for\n";
							if ($value['turnoCod'] == $string) {
								//print_r($value['Codigo'] == $string);
								$string = CaptchaRandom();
								$contador = 1;
								//echo "estoy en la validacion si es igual\n".$string."\n";
								break;
							}elseif($value['turnoCod'] != $string){
								//print_r($value['Codigo'] == $string);
								//echo "estoy en guardado\n";
								$Codigo = $string;
								$contador = 0;
							}
						}
					}
				}			
			
				//insert table usuarios
				$estado = 1;
				$codeShift = CodeVisualShift();
				$sql = "INSERT INTO turno (turnoCod, idUser, idUserSupervisor, estado, idDispositivo, codeShift) VALUES ('$Codigo', '$cmbUserAsign', '$idUserSupervisor', '$estado', '$idDispositivo', '$codeShift')";
				$params = array(
							"turnoCod" => $Codigo,
							"idUser" => $cmbUserAsign,
							"idUserSupervisor" => $idUserSupervisor,
							"estado" => $estado,
							"idDispositivo" => $idDispositivo,
							"codeShift" => $codeShift
						);	

				$getrows = $database->insertRow($sql, $params);

				if($getrows == true){
					$response->status = true;
					$response->sql = $sql;
				}else{
					$response->status = false;
					$response->error = $sql;
				}
			}else{
				//Sí cajero ya posee turno
				$response->status = "shift";
				$response->shift1 = $sqlS;
			}

				return $response;
		
		} catch (PDOException $e) {
            return $e;
        }	
	}		

	/**
	* Recibe infomación de la transaccion
	* @params $transaccion
	* @return (array) mixed
	*/
	public function insertTransactions($params){
		try {

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			$total = $data->total;
			$ern = $data->ern;
			$referencia = $data->referencia;
			$concepto = $data->concepto;
			$idTurno = $data->idTurno;
			$fecha = $data->fecha;
			$status = $data->status;
			
			$sql = "INSERT INTO transacciones (total, ERN, fecha, referencia, concepto, status, idturno) VALUES ('$total', '$ern', '$fecha', '$referencia', '$concepto', '$status', '$idTurno')";
			$params = array(
						"total" => $total,
						"ERN" => $ern,
						"fecha" => $fecha,
						"referencia" => $referencia,
						"concepto" => $concepto,
						"status" => $status,
						"idturno" => $idTurno
					);	

			$getrows = $database->insertRow($sql, $params);

			$response = new stdClass();

			if($getrows == true){
				$response->status = true;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			$response->data = $data->total;

			return $response;			

		} catch (PDOException $e) {
            return $e;
        }
	}

	/**
	* Actualiza la hora de finaliación del turno del cajero
	* @params $idUser, $turnoCod
	* @return (array) true/false
	*/
	public function cerrarTurno($params){
		try {

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			$idUser = $data->idUser;
			$turnoCod = $data->turnoCod;
			$fechaFin = date("Y-m-d H:i:s");
			
			$sql = 'UPDATE turno SET fechaFin = "'.$fechaFin.'", estado = "0" WHERE idUser="'.$idUser.'" AND turnoCod = "'.$turnoCod.'"';

			$params = array(
				"fechaFin" => $fechaFin,
				"idUser" => $idUser,
				"turnoCod" => $turnoCod
			);

			$getrows = $database->updateRow($sql, $params);

			$response = new stdClass();

			if($getrows == true){
				$response->status = true;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;			

		} catch (PDOException $e) {
            return $e;
        }
	}

	/**
	* Validación de usuario existente
	* @param trae el usuario ingresado a validar
	* @return true/false
	*/
	public function checkUserName($param){
		try{
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
			
			$username = $param;

			$sql = 'SELECT username FROM usuarios WHERE username = "'.$username.'"'; 
			
			$params = array(
					"username" => $username,
				);

			$row = $database->getRow($sql, $params);

			$response = new stdClass();
			
			if($row['username'] == $username){ //Caso username existe
				$response->status = "true";
			}else{ //Caso username no existe
				$response->status = "false";
			}

			return $response;

		} catch (PDOException $e) {
            return $e;
        }
	}	

	/**
	* Validación de comercio existente
	* @param trae el wsk y uid ingresado a validar
	* @return true con información de comercio existente / false
	*/
	function checkCommerceExist($params){
		try{
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
			
			$data = json_decode($params);

			$wsk = $data->checkCommerceExist->wsk;
			$uid = $data->checkCommerceExist->uid;

			$sql = 'SELECT c.WSK,
						   c.UID,
						   c.nombreComercio,
						   c.emailComercio,
						   c.numeroRegistro,
						   c.comercioLogo,
						   d.llaveCifrado,
						   d.cifradoIV,
						   d.moneda
					FROM dispositivo d
					INNER JOIN comercio c ON (d.comercio_idcomercio = c.idcomercio)
					WHERE c.WSK = "'.$wsk.'" AND c.UID = "'.$uid.'"'; 
			
			$params = array(
					"WSK" => $wsk,
					"UID" => $uid,
				);

			$row = $database->getRow($sql, $params);

			$loadDataCommerce = new stdClass();
			
			if(($row['WSK'] == $wsk) && ($row['UID'] == $uid)){ //Caso comercio existe
				$loadDataCommerce->status = true;
			}else{ //caso comercio no existe
				$loadDataCommerce->status = false;
				$loadDataCommerce->error = $sql;
			}

			return $loadDataCommerce;

		} catch (PDOException $e) {
            return $e;
        }
	}		

	/**
	* Insert tipoDispositivo, comercio & dispositivo para nueva sucursal del comercio
	* @params 
	* @return 
	*/
	public function insertConfDispositivoNewSucursal($params){

		try {
			
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			//comercio
			$wsk = $data->comercio->wsk;
			$uid = $data->comercio->uid;

			//dispositivo
			$nombreSucursal = $data->dispositivo->nombreSucursal;
			$branchid = $data->dispositivo->branchid;
			$terminalid = $data->dispositivo->terminalid;
			$mac = $data->dispositivo->mac;
			$ambiente = $data->dispositivo->ambiente;
			$tipoDispositivo = $data->dispositivo->tipoDispositivo;
			$setup = '1';

			//insert table tipoDispositivo
			$sql1 = "INSERT INTO tipoDispositivo (tipoDispositivo) VALUES ('$tipoDispositivo')";
			$params1 = array(
						"tipoDispositivo" => $tipoDispositivo,
					);

			$idTipoDispositivo = $database->insertRowid($sql1, $params1);
			
			//select id comercio
			$sql2 = "SELECT idcomercio FROM comercio WHERE WSK = '".$wsk."' AND UID = '".$uid."'";
			$params2 = array(
				"WSK" => $wsk,
				"UID" => $uid,
			);

			$row = $database->getRow($sql2, $params2);
			$idComercio = $row['idcomercio'];

			//select infoComercio
			$sql3 = "SELECT llaveCifrado, cifradoIV, moneda	FROM dispositivo WHERE comercio_idcomercio = '".$idComercio."'";
			$params3 = array(
				"comercio_idcomercio" => $idComercio,
			);

			$row3 = $database->getRow($sql3, $params3);
			$llaveCifrado = $row3['llaveCifrado'];
			$cifradoIV = $row3['cifradoIV'];
			$moneda = $row3['moneda'];

			//insert table dispositivo
			$sql4 = "INSERT INTO dispositivo (nombreSucursal, branchid, terminalid, llaveCifrado, cifradoIV, mac, moneda, ambiente, setup, comercio_idcomercio, idTipoDispositivo, userCreation) VALUES ('$nombreSucursal', '$branchid', '$terminalid', '$llaveCifrado', '$cifradoIV', '$mac', '$moneda', '$ambiente', '$setup', '$idComercio', '$idTipoDispositivo', 'Admin')";
			$params4 = array(
						"nombreSucursal" => $nombreSucursal,		
						"branchid" => $branchid,
						"terminalid" => $terminalid,
						"llaveCifrado" => $llaveCifrado,
						"cifradoIV" => $cifradoIV,
						"mac" => $mac,
						"moneda" => $moneda,
						"ambiente" => $ambiente,
						"setup" => $setup,
						"comercio_idcomercio" => $idComercio,
						"idTipoDispositivo" => $idTipoDispositivo,
						"userCreation" => 'Admin',
					);

			$get = $database->insertRow($sql4, $params4);
			
			$response = new stdClass();
			
			if($get == true){
				$response->status = true;
				$response->sql1 = $sql1;
				$response->sql2 = $sql2;
				$response->sql3 = $sql3;
				$response->sql4 = $sql4;
			}else{
				$response->status = false;
				$response->sql1 = $sql1;
				$response->sql2 = $sql2;
				$response->sql3 = $sql3;
				$response->sql4 = $sql4;
			}

			return $response;
		
		} catch (PDOException $e) {
            return $e;
        }	
	}

	/**
	* Obtener turnos de cajeros 
	* @param trae id de los turnos que ha realizado el cajero
	* @return 
	*/
	public function getCajeroTurnos($params){
		try{
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
			$data = json_decode($params);
			$idDispositivo = $data->cajeroTurnos->idDispositivo;
			$idUserCajero = $data->cajeroTurnos->idUserCajero;

			$sql = 'SELECT idturno, turnoCod FROM turno WHERE idUser = "'.$idUserCajero.'" AND idDispositivo = "'.$idDispositivo.'" AND estado = "0"'; 
			
			$params = array(
				"idUser" => $idUserCajero,
			);

			$getrows = $database->getRows($sql, $params);

			$response = new stdClass();
			
			if($getrows == true){
				$response->status = true;
				$response->getCajeroTurnos = $getrows;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;

		} catch (PDOException $e) {
            return $e;
        }
	}

	/**
	* Obtener todas las sucursales correspondientes a un comercio
	* @param trae id del comercio asociado al usuario
	* @return 
	*/
	public function getSucursales($params){
		try{
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$idComercio = $params;

			$sql = 'select idDispositivo, nombreSucursal from dispositivo where comercio_idcomercio = "'.$idComercio.'"'; 
			
			$params = array(
				"comercio_idcomercio" => $idComercio,
			);

			$getrows = $database->getRows($sql, $params);

			$response = new stdClass();
			
			if($getrows == true){
				$response->status = true;
				$response->sql = $sql;
				$response->getSucursales = $getrows;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;

		} catch (PDOException $e) {
            return $e;
        }
	}	

	/**
	* Obtener información de configuración del comercio
	* @param trae idDispositivo
	* @return (array) data true/false
	*/
	public function getDataCommerce($params){
		try{
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$idDispositivo = $params;

			$sql = 'SELECT 	c.WSK,
							c.UID,
							d.ambiente,
							c.nombreComercio,
							c.emailComercio,
							c.numeroRegistro,
							d.nombreSucursal,
							d.branchid,
							d.terminalid,
							d.llaveCifrado,
							d.cifradoIV,
							d.moneda
					FROM dispositivo d
					INNER JOIN comercio c ON (d.comercio_idcomercio = c.idcomercio)
					WHERE d.idDispositivo = "'.$idDispositivo.'"'; 
			
			$params = array(
				"idDispositivo" => $idDispositivo,
			);

			$getrows = $database->getRows($sql, $params);

			$response = new stdClass();
			
			if($getrows == true){
				$response->status = true;
				$response->getDataCommerce = $getrows;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;

		} catch (PDOException $e) {
            return $e;
        }
	}	

	/**
	* Actualiza la información del dispositivo/sucursal
	* @params credenciales de integración del dispositivo/sucursal
	* @return (array) true/false
	*/
	public function updateConfDispositivo($params){
		try {

			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			//comercio
			$wsk = $data->comercio->wsk;
			$uid = $data->comercio->uid;
			$nombreComercio = $data->comercio->nombreComercio;
			$emailComercio = $data->comercio->emailComercio;
			$numeroRegistro = $data->comercio->numeroRegistro;
			$idComercio = $data->comercio->idComercio;

			//dispositivo
			$idDispositivo = $data->dispositivo->idDispositivo;
			$nombreSucursal = $data->dispositivo->nombreSucursal;
			$branchid = $data->dispositivo->branchid;
			$terminalid = $data->dispositivo->terminalid;
			$llaveCifrado = $data->dispositivo->llaveCifrado;
			$cifradoIV = $data->dispositivo->cifradoIV;
			$ambiente = $data->dispositivo->ambiente;
			$moneda = $data->dispositivo->moneda;
			$userModification = $data->dispositivo->userModification;
			
			$sql = 'UPDATE dispositivo d INNER JOIN comercio c ON (d.comercio_idcomercio = c.idcomercio)
					SET c.WSK = "'.$wsk.'", c.UID = "'.$uid.'", c.nombreComercio = "'.$nombreComercio.'", d.nombreSucursal = "'.$nombreSucursal.'", c.emailComercio = "'.$emailComercio.'", 
						c.numeroRegistro = "'.$numeroRegistro.'", d.branchid = "'.$branchid.'", d.terminalid = "'.$terminalid.'", 
						d.llaveCifrado = "'.$llaveCifrado.'", d.cifradoIV = "'.$cifradoIV.'", d.ambiente = "'.$ambiente.'", d.moneda = "'.$moneda.'",
						d.fechaModification = current_timestamp, d.userModification = "'.$userModification.'"
					WHERE d.comercio_idcomercio = "'.$idComercio.'" AND d.idDispositivo = "'.$idDispositivo.'"';

			$params = array(
				"WSK" => $wsk,
				"UID" => $uid,
				"nombreComercio" => $nombreComercio,
				"nombreSucursal" => $nombreSucursal,
				"emailComercio" => $emailComercio,
				"numeroRegistro" => $numeroRegistro,
				"branchid" => $branchid,
				"terminalid" => $terminalid,
				"llaveCifrado" => $llaveCifrado,
				"cifradoIV" => $cifradoIV,
				"ambiente" => $ambiente,
				"comercio_idcomercio" => $idComercio,
				"idDispositivo" => $idDispositivo,
				"userModification" => $userModification,
			);

			$getrows = $database->updateRow($sql, $params);

			$response = new stdClass();

			if($getrows == true){
				$response->status = true;
				//$response->error = $sql;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;			

		} catch (PDOException $e) {
            return $e;
        }
	}

	/**
	* Recibe infomación de todos los turnos
	* @params $condition (optional)
	* @return (array) mixed
	*/
	public function getAllShift($params){

		try {
			$database = new dbPDO(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

			$data = json_decode($params);

			//infoShift
			$idDispositivo = $data->infoShift->idDispositivo;
			$CloseShiftTable = $data->infoShift->CloseShiftTable;
			$fecha_inicio = $data->infoShift->fecha_inicio;
			$fecha_fin = $data->infoShift->fecha_fin;
			$userCajero = $data->infoShift->userCajero;
			$codeShift = $data->infoShift->codeShift;

			/*if($fecha_inicio != ''){
				$fechaInicio = ' AND (DATE_FORMAT(t.fechaInicio,"%Y-%m-%d") >= "'.$fecha_inicio.'"';
			}

			if($fecha_fin != ''){
				$fechaFin = ' AND DATE_FORMAT(t.fechaFin,"%Y-%m-%d") <= "'.$fecha_fin.'")';
			}*/

			if($fecha_inicio != ''){
				$fechaInicio = ' AND (DATE_FORMAT(t.fechaInicio,"%Y-%m-%d") BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_fin.'")';
			}

			if($userCajero != ''){
				$idUserCajero = ' AND u.idUser = "'.$userCajero.'"';
			}

			if($codeShift != ''){
				$codeTurno = ' AND t.codeShift = "'.$codeShift.'"';
			}

			if($CloseShiftTable == "1"){
				$sql = 'SELECT t.codeShift, 
						   u.username, 
						   t.fechaInicio, 
						   t.fechaFin,
						   t.turnoCod
					FROM turno t
					INNER JOIN usuarios u
					ON (t.idUser = u.idUser)
					WHERE t.idDispositivo = "'.$idDispositivo.'" AND t.estado = "0"
					ORDER BY t.fechaInicio DESC, t.fechaFin DESC';

			}elseif($CloseShiftTable == "2"){
				$sql = 'SELECT t.codeShift, 
						   u.username, 
						   t.fechaInicio, 
						   t.fechaFin,
						   t.turnoCod
					FROM turno t
					INNER JOIN usuarios u
					ON (t.idUser = u.idUser)
					WHERE t.idDispositivo = "'.$idDispositivo.'" AND t.estado = "0"'
					//. $fechaInicio . '' . $fechaFin . '' . $idUserCajero . '' . $codeTurno . '
					. $fechaInicio . '' . $idUserCajero . '' . $codeTurno . '
					ORDER BY t.fechaInicio DESC, t.fechaFin DESC';

			}else{
				$sql = 'SELECT t.codeShift, 
						   u.username, 
						   t.fechaInicio, 
						   t.fechaFin, 
						   t.estado
					FROM turno t
					INNER JOIN usuarios u
					ON (t.idUser = u.idUser)
					WHERE t.idDispositivo = "'.$idDispositivo.'"
					ORDER BY t.fechaInicio DESC, t.fechaFin DESC';
			}
			
			$get = $database->getRows($sql);
			
			$response = new stdClass();
			
			if($get == true){
				$response->status = true;
				$response->getAllShift = $get;
				$response->sq = $sql;
			}else{
				$response->status = false;
				$response->error = $sql;
			}

			return $response;
		
		} catch (PDOException $e) {
            return $e;
        }				
	}	

}

?>
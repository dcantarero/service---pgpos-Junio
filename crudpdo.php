<?php

class dbPDO
{
    public $isConnected;
    static protected $instance;
    protected $datab;
    public function __construct($username, $password, $host, $dbname, $options = array()){
        $this->isConnected = true;
        try {
            $this->datab = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options);
            $this->datab->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->datab->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->isConnected = false;
            throw new Exception($e->getMessage());
        }
    }

    public static function getInstance() {
        if(!self::$instance) {
            // get the arguments to the constructor from configuration somewhere
            self::$instance = new self(DB_USER, DB_PASS, DB_HOST, DB_NAME, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        }
        return self::$instance;
    }

    public function Disconnect(){
        $this->datab = null;
        $this->isConnected = false;
    }

    public function getRow($query, $params = array()){
        try {
            $stmt = $this->datab->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getRows($query, $params = array()){
        try {
            $stmt = $this->datab->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getRowsJSON($query, $params = array()){
        try {
            $stmt = $this->datab->prepare($query);
            $stmt->execute($params);
            return json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function insertRow($query, $params){
        try {
            $this->datab->beginTransaction();
            $stmt = $this->datab->prepare($query);
            $stmt->execute($params);
            return $this->datab->commit();
        } catch (PDOException $e) {
            $error = $e->errorInfo;
            $this->datab->rollBack();
            return $error[1];
        }
    }

    public function insertRowid($query, $params){
        try {
            $this->datab->beginTransaction();
            $stmt = $this->datab->prepare($query);
            $stmt->execute($params);
            $id = $this->datab->lastInsertId();
            $this->datab->commit();
            return $id;
        } catch (PDOException $e) {
            $error = $e->errorInfo;
            $this->datab->rollBack();
            return $error[1];
        }
    }

    public function updateRow($query, $params){
        return $this->insertRow($query, $params);
    }

    public function deleteRow($query, $params){
        return $this->insertRow($query, $params);
    }
}
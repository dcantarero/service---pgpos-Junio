<?php

try {
            $this->dbh = new PDO('mysql:host=184.168.200.155;dbname=pgpos_db', 'pgpos_u_app','f1frRyf6UNi74uAfUZBQ');
            //define ( 'DB_HOST_1', 'us-cdbr-azure-southcentral-e.cloudapp.net' );
            // define ( 'DB_DB', 'ninjawebclv' );
            // define ( 'DB_USER_1', 'bc6e2ccc1d5a9b' );
            //define ( 'DB_PASS_1', 'f3b3a732' );
            $this->dbh->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage();
            die();
        }
?>
<?php
include "Modelo.php";

try {
  $server = new SOAPServer(
    NULL,
    array(
     'uri' => 'http://pgpos.pagaditogroup.com/lib/server.php',
     'encoding' => 'UTF-8'
    )
  );

  // SETTING UP THE Db CLASS
  $server->setClass('Modelo'); 
  $server->handle();
}

catch (SOAPFault $f) {
  print $f->faultstring; exit;
}

?>
<?php
$client = new SoapClient(null, array(
      'location' => "http://pgpos.pagaditogroup.com/lib/server.php",
      'uri'      => "http://pgpos.pagaditogroup.com/lib/server.php",
      'trace'    => 1,
      'encoding' => 'UTF-8'
    )
);
?>
<?php
   $xslDoc = new DOMDocument();
   $xslDoc->load("index.xsl");

   $xmlDoc = new DOMDocument();
   $xmlDoc->load("index.xml");

   $proc = new XSLTProcessor();
   $proc->importStylesheet($xslDoc);
   echo $proc->transformToXML($xmlDoc);


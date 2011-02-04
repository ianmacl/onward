<?php
   $xslDoc = new DOMDocument();
   $xslDoc->load("module.xsl");

   $xmlDoc = new DOMDocument();
   $xmlDoc->load("module.xml");

   $proc = new XSLTProcessor();
   $proc->importStylesheet($xslDoc);
   echo $proc->transformToXML($xmlDoc);


<?php

global $devs;

$devs = isset($devs) ? $devs : array() ;
$devs  = array_merge($devs, array(
	'stop_pv_api_send_leads_18427' => array('desc' => "[tr-18427] PLV - Girandieres - API - Mise en pause des envois via API", 'default'=>false, 'no_css' => false ),
));
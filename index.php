<?php 
session_start();

// Include and instantiate the class.
require_once 'includes/Mobile_Detect.php';
$detect = new Mobile_Detect;
 
// Any mobile device (phones or tablets).
if ( $detect->isMobile() ) {
	header('Location: index_mobile3.php');
	die();
} else {
	/*
	require_once __DIR__ . '/src/Facebook/autoload.php'; // download official fb sdk for php @ https://github.com/facebook/php-graph-sdk
	$fb = new Facebook\Facebook([
	  'app_id' => '209165813231127',
	  'app_secret' => '07994b7aa13cbaae440845198855e760',
	  'default_graph_version' => 'v2.12',
	]);

	$helper = $fb->getPageTabHelper();

	// Obtain a signed request entity from a page tab
	$helper = $fb->getPageTabHelper();
	$signedRequest = $helper->getSignedRequest();
	if(!$signedRequest) {
		header("Location: https://www.facebook.com/33ExportCameroun/app/209165813231127/");
		die;
	} 
	include 'index_page_tab.php'
	*/
}
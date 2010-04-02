<?php
	
	define('DOCROOT', '../..');
	define('DOMAIN', rtrim(rtrim($_SERVER['HTTP_HOST'], '\\/') . dirname($_SERVER['PHP_SELF']), '\\/'));
	
	
	require_once DOCROOT . '/symphony/lib/boot/bundle.php';
	require_once TOOLKIT . '/class.manager.php';
	require_once TOOLKIT . '/class.sectionmanager.php';
	require_once TOOLKIT . '/class.entrymanager.php';
	
	require_once CORE . '/class.frontend.php';
	require_once 'lib/class.images_info.php';
	
	
	ImagesInfo::setParent(Frontend::instance());
	ImagesInfo::setRootElement('image-info');
	
	$options = array(
		'iptc' => $_GET['iptc'],
		'exif' => $_GET['exif']
	);
	
	try {
		$section = ImagesInfo::findSection($_GET['section']);
		$entries = ImagesInfo::findEntries($_GET['entries'], $section);
		
		$xml = ImagesInfo::process($entries, $options);
		$xml->setIncludeHeader();
	
		echo $xml->generate();
	
	} catch (Exception $ex) {
		
		$error = new XMLElement(ImagesInfo::getRootElement());
		$error->appendChild(new XMLElement('error', $ex->getMessage()));
		
		die($error->generate());
	}

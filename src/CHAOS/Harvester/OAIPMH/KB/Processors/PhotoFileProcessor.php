<?php
namespace CHAOS\Harvester\OAIPMH\KB\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;
use CHAOS\Harvester\Shadows\SkippedObjectShadow;

function str_ends_with($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}
	return (substr($haystack, -$length) === $needle);
}

class PhotoFileProcessor extends \CHAOS\Harvester\Processors\FileProcessor {
	
	public function process($externalObject, &$shadow = null) {
		$this->_harvester->debug(__CLASS__." is processing.");
		
		$record = $externalObject->metadata->record;
		assert($record instanceof \SimpleXMLElement);
		assert($shadow instanceof \CHAOS\Harvester\Shadows\ObjectShadow);
		
		$photoURLs = $record->xpath('europeana:object');
		foreach($photoURLs as $photoURL) {
			if(!str_ends_with(strtolower($photoURL), '.jpg')) {
				$this->_harvester->info("An europeana:object element (%s) didn't have the .jpg suffix.", $photoURL);
				//continue; // But this might be okay?
			}
			$fileShadow = $this->createFileShadowFromURL($photoURL);
			if($fileShadow) {
				$shadow->fileShadows[] = $fileShadow;
			} else {
				$this->_harvester->info("Skipping the file %s as it seems to not exist or no destination can be used.", $photoURL);
			}
		}
		return $shadow;
	}
	
	protected function concludeFileExistance($response) {
		if(preg_match('/Content-Type: ([^;.]*).*/', $response, $contentType)) {
			$contentType = $contentType[1];
			var_dump($contentType);
			return $contentType == "image/jpeg";
		} else {
			return false;
		}
		/*
		// TODO: Consider checking the content type.
		if(preg_match('/Content-Length: (.*)?/', $response, $contentLength)) {
			$contentLength = intval($contentLength[1]);
			return $contentLength > 1000;
		} else {
			return false;
		}
		*/
	}
	
	protected function extractURLPathinfo($photoURL, $size = null) {
		$pathinfo = pathinfo($photoURL);
		// The imageService is a part of the destination.
		$pathinfo['dirname'] = preg_replace('#imageService#', '', $pathinfo['dirname']) . '/';
		if($size != null) {
			$pathinfo['dirname'] = '/w' . $size . $pathinfo['dirname'];
		}
		return $pathinfo;
	}
}
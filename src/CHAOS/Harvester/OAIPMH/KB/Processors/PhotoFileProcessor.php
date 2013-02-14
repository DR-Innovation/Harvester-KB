<?php
namespace CHAOS\Harvester\OAIPMH\KB\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;
use CHAOS\Harvester\Shadows\SkippedObjectShadow;

class PhotoFileProcessor extends \CHAOS\Harvester\Processors\FileProcessor {
	
	public function process($externalObject, &$shadow = null) {
		$this->_harvester->debug(__CLASS__." is processing.");
		
		$record = $externalObject->metadata->record;
		assert($record instanceof \SimpleXMLElement);
		assert($shadow instanceof \CHAOS\Harvester\Shadows\ObjectShadow);
		
		$photoURLs = $record->xpath('europeana:object');
		foreach($photoURLs as $photoURL) {
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
		if(preg_match('/Content-Length: (.*)?/', $response, $contentLength)) {
			$contentLength = intval($contentLength[1]);
			return $contentLength > 1000;
		} else {
			return false;
		}
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
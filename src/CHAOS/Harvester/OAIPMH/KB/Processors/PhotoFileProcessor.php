<?php
namespace CHAOS\Harvester\OAIPMH\KB\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;

function str_ends_with($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}
	return (substr($haystack, -$length) === $needle);
}

class PhotoFileProcessor extends \CHAOS\Harvester\Processors\FileProcessor {
	
	public function process(&$externalObject, &$shadow = null) {
		$this->_harvester->debug(__CLASS__." is processing.");
		
		$record = $externalObject->metadata->record;
		assert($record instanceof \SimpleXMLElement);
		assert($shadow instanceof \CHAOS\Harvester\Shadows\ObjectShadow);
		
		$photoURLs = $record->xpath('europeana:object');
		foreach($photoURLs as $photoURL) {
			/*
			if(!str_ends_with(strtolower($photoURL), '.jpg')) {
				$this->_harvester->info("An europeana:object element (%s) didn't have the .jpg suffix.", $photoURL);
				// continue; // But this might be okay?
			}
			*/
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
		if(preg_match('#Content-Type: ?image/jpeg.*#', $response)) {
			return true;
		} else {
			return false;
		}
	}
	
	protected function extractURLPathinfo($photoURL) {
		$pathinfo = pathinfo($photoURL);
		// The imageService is a part of the destination.
		// TODO: Consider if this line should still be here ..
		$pathinfo['dirname'] = preg_replace('#imageService#', '', $pathinfo['dirname']) . '/';
		/*
		if($size != null) {
			$pathinfo['dirname'] = '/w' . $size . $pathinfo['dirname'];
		}
		*/
		return $pathinfo;
	}
}
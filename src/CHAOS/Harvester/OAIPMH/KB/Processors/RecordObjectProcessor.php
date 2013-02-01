<?php
namespace CHAOS\Harvester\OAIPMH\KB\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;
use CHAOS\Harvester\Shadows\SkippedObjectShadow;

class RecordObjectProcessor extends \CHAOS\Harvester\Processors\ObjectProcessor {
	
	protected function generateQuery($externalObject) {
		assert($externalObject->header->identifier);
		$identifier = strval($externalObject->header->identifier);
		/*
		$legacyQuery = sprintf('(DKA-Organization:"%s" AND ObjectTypeID:%u AND m00000000-0000-0000-0000-000063c30000_da_all:"%s")', 'DR', $this->_objectTypeId, strval($externalObject->AssetId));
		$newQuery = sprintf('(FolderTree:%u AND ObjectTypeID:%u AND DKA-ExternalIdentifier:"%s")', $this->_folderId, $this->_objectTypeId, strval($externalObject->AssetId));
		return sprintf("(%s OR %s)", $legacyQuery, $newQuery);
		*/
		return sprintf('(FolderTree:%u AND ObjectTypeID:%u AND DKA-ExternalIdentifier:"%s")', $this->_folderId, $this->_objectTypeId, $identifier);
	}
	
	public function process($externalObject, $shadow = null) {
		$this->_harvester->debug(__CLASS__." is processing.");
		
		/* @var $externalObject \SimpleXMLElement */
		assert($externalObject instanceof \SimpleXMLElement);
		
		assert($externalObject->header->identifier);
		$identifier = strval($externalObject->header->identifier);
		
		assert($externalObject->metadata && $externalObject->metadata->record);
		
		$externalObject->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
		$externalObject->registerXPathNamespace('dcterms', 'http://purl.org/dc/terms/');
		$externalObject->registerXPathNamespace('europeana', 'http://www.europeana.eu/schemas/ese/');
		
		$record = $externalObject->metadata->record;
		assert($record instanceof \SimpleXMLElement);
		
		$title = $record->xpath('(dc:title[@lang="da"] | dc:title)[1]');
		if(count($title) == 0) {
			$title = "Untitled";
		} else {
			$title = strval($title[0]);
		}
		
		$this->_harvester->info("Processing '%s' #%s", $title, $identifier);
		
		$shadow = new ObjectShadow();
		$shadow = $this->initializeShadow($shadow);
		$shadow->extras["identifier"] = strval($identifier);
		
		$shadow->query = $this->generateQuery($externalObject);
		$shadow = $this->_harvester->process('record_metadata_dka2', $externalObject, $shadow);
		$shadow = $this->_harvester->process('photo_file', $externalObject, $shadow);
		$shadow = $this->_harvester->process('thumb_photo_file', $externalObject, $shadow);
		
		$shadow->commit($this->_harvester);
		
		return $shadow;
	}
}
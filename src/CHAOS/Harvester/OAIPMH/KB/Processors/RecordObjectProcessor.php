<?php
namespace CHAOS\Harvester\OAIPMH\KB\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;

class RecordObjectProcessor extends \CHAOS\Harvester\Processors\ObjectProcessor {
	
	protected function generateQuery($externalObject) {
		assert($externalObject->header->identifier);
		$identifier = strval($externalObject->header->identifier);
		$newQuery = sprintf('(FolderTree:%u AND ObjectTypeID:%u AND DKA-ExternalIdentifier:"%s")', $this->_folderId, $this->_objectTypeId, $identifier);
		
		if(preg_match("#:object([^:]*)#", $identifier, $nummeric_id_matches) == 0) {
			// But this might not be a problem.
			$this->_harvester->info("Cannot extract a nummeric ID from the identifier.");
			return $newQuery;
			//throw new \Exception("Cannot extract a nummeric ID from the identifier.");
		} else {
			$nummeric_id = intval($nummeric_id_matches[1]);
			$legacyQuery = sprintf('(DKA-Organization:"%s" AND ObjectTypeID:%u AND m00000000-0000-0000-0000-000063c30000_da_all:"%s")', 'Det Kongelige Bibliotek', $this->_objectTypeId, "{$nummeric_id}");
			return sprintf("(%s OR %s)", $legacyQuery, $newQuery);
		}
	}
	
	public function process($externalObject, &$shadow = null) {
		$this->_harvester->debug(__CLASS__." is processing.");
		
		/* @var $externalObject \SimpleXMLElement */
		assert($externalObject instanceof \SimpleXMLElement);
		
		assert($externalObject->header->identifier);
		$identifier = strval($externalObject->header->identifier);
		
		assert($externalObject->metadata && $externalObject->metadata->record);
		
		$externalObject->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
		$externalObject->registerXPathNamespace('dcterms', 'http://purl.org/dc/terms/');
		$externalObject->registerXPathNamespace('europeana', 'http://www.europeana.eu/schemas/ese/');
		$externalObject->registerXPathNamespace('oa', 'http://www.openarchives.org/OAI/2.0/');
		$externalObject->registerXPathNamespace('ese', 'http://www.europeana.eu/schemas/ese/');
		
		$record = $externalObject->metadata->record;
		assert($record instanceof \SimpleXMLElement);
		
		$title = self::extractTitle($record);
		
		$this->_harvester->info("Processing '%s' #%s", $title, $identifier);
		
		$shadow = new ObjectShadow();
		$shadow = $this->initializeShadow($shadow);
		$shadow->extras["identifier"] = strval($identifier);
		
		$shadow->query = $this->generateQuery($externalObject);
		$this->_harvester->process('record_metadata_dka2', $externalObject, $shadow);
		$this->_harvester->process('photo_file', $externalObject, $shadow);
		$this->_harvester->process('thumb_photo_file', $externalObject, $shadow);
		
		$shadow->commit($this->_harvester);
		
		return $shadow;
	}
	
	public static function extractTitle($record) {
		$title = $record->xpath('(dc:title[@lang="da"] | dc:title)[1]');
		if(count($title) == 0) {
			$title = "";
		} else {
			$title = strval($title[0]);
		}
		$title = str_replace("\n", "", $title);
		$title = str_replace("\t", "", $title);
		$title = trim($title);
		if(strlen(trim($title)) == 0) {
			$title = "[Untitled external object]";
		}
		return $title;
	}
}
<?php
namespace CHAOS\Harvester\OAIPMH\KB\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;
use CHAOS\Harvester\Shadows\SkippedObjectShadow;

class ThumbPhotoFileProcessor extends PhotoFileProcessor {
	
	protected function extractURLPathinfo($externalObject) {
		return parent::extractURLPathinfo($externalObject, 150);
	}
}
<?php
namespace CHAOS\Harvester\OAIPMH\KB\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;

class ThumbPhotoFileProcessor extends PhotoFileProcessor {
	
	protected function extractURLPathinfo($externalObject) {
		return parent::extractURLPathinfo($externalObject, 150);
	}
}
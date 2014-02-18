<?php
namespace CHAOS\Harvester\OAIPMH\KB\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;

class ThumbPhotoFileProcessor extends PhotoFileProcessor {
	
	/*
	protected function extractURLPathinfo($externalObject) {
		return parent::extractURLPathinfo($externalObject, 150);
	}
	*/

	public function createFileShadowFromURL($url) {
		$url = preg_replace('#imageService/#', 'imageService/w400/', $url);
		return parent::createFileShadowFromURL($url);
	}
}

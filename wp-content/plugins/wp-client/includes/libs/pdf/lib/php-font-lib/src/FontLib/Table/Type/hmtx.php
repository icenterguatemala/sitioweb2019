<?php
 namespace FontLib\Table\Type; use FontLib\Table\Table; class hmtx extends Table { protected function _parse() { $font = $this->getFont(); $offset = $font->pos(); $numOfLongHorMetrics = $font->getData("hhea", "numOfLongHorMetrics"); $numGlyphs = $font->getData("maxp", "numGlyphs"); $font->seek($offset); $data = array(); $metrics = $font->readUInt16Many($numOfLongHorMetrics * 2); for ($gid = 0, $mid = 0; $gid < $numOfLongHorMetrics; $gid++) { $advanceWidth = $metrics[$mid++]; $leftSideBearing = $metrics[$mid++]; $data[$gid] = array($advanceWidth, $leftSideBearing); } if ($numOfLongHorMetrics < $numGlyphs) { $lastWidth = end($data); $data = array_pad($data, $numGlyphs, $lastWidth); } $this->data = $data; } protected function _encode() { $font = $this->getFont(); $subset = $font->getSubset(); $data = $this->data; $length = 0; foreach ($subset as $gid) { $length += $font->writeUInt16($data[$gid][0]); $length += $font->writeUInt16($data[$gid][1]); } return $length; } }
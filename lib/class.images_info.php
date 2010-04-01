<?php
	
	class ImagesInfo {
	
		static protected $parent, $root;
		
		public static function findSection($handle)
		{
			$sm  = new SectionManager(self::getParent());
			if (!is_numeric($handle)) {
				$handle = $sm->fetchIDFromHandle($handle);
			}
			
			$ret = $sm->fetch($handle);
			if (!is_object($ret)) {
				self::throwEx('Section does not exist');
			}
			
			return $ret;
		}
		
		public static function findEntries($entries, $section)
		{
			$entries = explode(',', $entries);
			$em = new EntryManager(self::getParent());
			
			$ret = $em->fetch($entries, $section->get('id'));
			if ($ret === false) {
				self::throwEx('An error occurred while processing entries');
			}
			
			return $ret;
		}
		
		public static function process($entries, $options = array())
		{
			$default = array(
				'iptc' => true,
				'exif' => true
			);
			
			$options = self::merge($default, $options);
			self::checkRequirements($options['exif']);
			
			$root = new XMLElement(self::getRootElement());
			
			foreach ($entries as $entry)
			{
			
				foreach ($entry->getData() as $d)
				{
					// file upload field?
					if (!array_key_exists('file', $d)) continue;
			
					$rel = $d['file'];
					$img = WORKSPACE. $rel;
					$xml = new XMLElement(
						'image',
						null,
						array('path' => $rel)
					);
					
					if ($options['iptc']) {
						$result = self::processIptc($img);
						$xml->appendChild($result);
					}

					if ($options['exif']) {
						$result = self::processExif($img);
						$xml->appendChild($result);
					}
					
					$root->appendChild($xml);
				}
			}
			
			return $root;
		}
		
		
		public static function processIptc($img, $node = 'iptc')
		{
			$size = getimagesize($img, $info);
			$node = new XMLElement($node);
			
			if(isset($info['APP13']))
			{
				$iptc = iptcparse($info['APP13']);
				
				foreach ($iptc as $tag => $val)
				{
					$temp = new XMLElement(
						'data',
						self::clean($val),
						array('tag' => $tag)
					);
					$node->appendChild($temp);
				}
			}
			
			return $node;
		}
		
		public static function processExif($img, $node = 'exif')
		{
			$exif = exif_read_data($img, 0, true);
			$node = new XMLElement($node);
			
			if ($exif) {
				foreach ($exif as $name => $section)
				{
					
					$elem = new XMLElement(
						'section',
						null,
						array('name' => $name)
					);
					
					foreach ($section as $tag => $val)
					{
						$temp = new XMLElement(
							'data',
							self::clean($val),
							array('tag' => $tag)
						);
						$elem->appendChild($temp);
					}

					$node->appendChild($elem);
				}
			}
			
			return $node;
		}
				
		public static function clean($value)
		{
			if (is_array($value)) $value = join(',', $value);
	
			// this sucks a lot :(
			return (!@simplexml_load_string('<a>'. $value. '</a>'))
					? '' : $value;
		}
		
		public static function merge(array $default, array $options)
		{
			foreach ($default as $key => $val)
			{
				if (array_key_exists($key, $options)
				&& (!is_null($options[$key]))) {
					
					$opt = $options[$key];
					$default[$key] = 
						($opt == '0') ||
						($opt == 'false')
						?
						false : true;
				}
			}
			
			return $default;
		}
		
		public static function checkRequirements($throw = false)
		{
			$ret = function_exists('exif_read_data');
			if (!$ret && $throw) {
				self::throwEx(
					'Exif extension not available'
				);
			}
			
			return $ret;
		}
		
		public static function setParent($parent)
		{
			self::$parent = $parent;
		}
		
		public static function getParent()
		{
			return self::$parent;
		}
		
		public static function setRootElement($root)
		{
			self::$root = $root;
		}
		
		public static function getRootElement()
		{
			return self::$root;
		}
		
		protected static function throwEx($msg)
		{
			throw new Exception($msg);
		}
	}
	

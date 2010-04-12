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
		
		public static function findEntries($entries, Section $section)
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
			
				$data = $entry->getData();
				foreach ($data as $field)
				{
					// file upload field?
					if (!array_key_exists('file', $field)) continue;
			
					$rel = $field['file'];
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
			getimagesize($img, $info);
			$node = new XMLElement($node);
			
			if(isset($info['APP13']))
			{
				$iptc = iptcparse($info['APP13']);
				
				foreach ($iptc as $handle => $val)
				{
					$tag  = self::iptcHandle($handle);
					$temp = new XMLElement(
						'data',
						self::clean($val),
						array('tag' => $tag, 'handle' => $handle)
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
			
			if ($exif)
			{
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
				&& (!is_null($options[$key])))
				{
					
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
		
		public static function iptcHandle($tag)
		{
			$handles = array(
				'2#004' => 'intellectual-genre',
				'2#005' => 'title',
				'2#009' => 'urgency',
				'2#012' => 'subject-code',
				'2#015' => 'category',
				'2#020' => 'supplemental-category',
				'2#025' => 'keywords',
				'2#040' => 'special-instructions',
				'2#055' => 'date-created',
				'2#080' => 'creator',
				'2#085' => 'creator-job-title',
				'2#090' => 'city',
				'2#092' => 'location',
				'2#095' => 'state',
				'2#100' => 'iso-country-code',
				'2#101' => 'country',
				'2#103' => 'job-identifier',
				'2#105' => 'headline',
				'2#110' => 'provider',
				'2#115' => 'source',
				'2#116' => 'copyright-notice',
				'2#120' => 'description',
				'2#122' => 'description-writer'

			);
			
			return array_key_exists($tag, $handles) !== false ?
				$handles[$tag] : '';
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

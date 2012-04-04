<?php

	class ImagesInfo {

		static protected $root;

		public static function findSection($handle)
		{
			if (!is_numeric($handle)) {
				$handle = SectionManager::fetchIDFromHandle($handle);
			}

			$ret = SectionManager::fetch($handle);
			if (!is_object($ret))
				self::throwEx('Section does not exist');

			return $ret;
		}

		public static function findEntries($entries, Section $section)
		{
			$entries = explode(',', $entries);
			$ret = EntryManager::fetch($entries, $section->get('id'));
			if ($ret === false)
				self::throwEx('An error occurred while processing entries');

			return $ret;
		}

		public static function process($options = array())
		{
			$default = array(
				'entries' => array(),
				'section' => null,
				'field_name' => null,
				'iptc' => true,
				'exif' => true
			);

			$options = array_merge($default, $options);
			self::checkRequirements($options['exif']);

			if (!$options['field_name'] || !$options['entries'] || !$options['section'])
				self::throwEx('Missing required option');

			$root  = new XMLElement(self::getRootElement());
			$field = FieldManager::fetchFieldIDFromElementName($options['field_name'], $options['section']->get('id'));

			foreach ($options['entries'] as $entry)
			{

				$data = $entry->getData($field);
				$rel  = $data['file'];
				$img  = WORKSPACE. $rel;
				$xml  = new XMLElement(
					'image',
					null,
					array('path' => $rel, 'entry_id' => $entry->get('id'))
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
				'2#004' => 'IntellectualGenre',
				'2#005' => 'Title',
				'2#009' => 'Urgency',
				'2#012' => 'SubjectCode',
				'2#015' => 'Category',
				'2#020' => 'SupplementalCategory',
				'2#025' => 'Keywords',
				'2#040' => 'SpecialInstructions',
				'2#055' => 'DateCreated',
				'2#080' => 'Creator',
				'2#085' => 'CreatorJobTitle',
				'2#090' => 'City',
				'2#092' => 'Location',
				'2#095' => 'State',
				'2#100' => 'IsoCountryCode',
				'2#101' => 'Country',
				'2#103' => 'JobIdentifier',
				'2#105' => 'Headline',
				'2#110' => 'Provider',
				'2#115' => 'Source',
				'2#116' => 'CopyrightNotice',
				'2#120' => 'Description',
				'2#122' => 'DescriptionWriter'

			);

			return array_key_exists($tag, $handles) !== false ?
				$handles[$tag] : '';
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

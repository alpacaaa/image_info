<?php

	class extension_Images_Info extends Extension {

		public function about(){
			return array(
				'name' => 'Image Information',
				'version' => '1.0',
				'release-date' => '2010-04-01',
				'author' => array(
					'name'  => 'Marco Sampellegrini',
					'email' => 'm@rcosa.mp'
				),
				'description' => 'Extracts Iptc/Exif data from images.'
			);
		}
	}

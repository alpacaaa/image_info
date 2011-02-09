<?php

	class extension_Image_Info extends Extension {

		public function about(){
			return array(
				'name' => 'Image Information',
				'version' => '1.1',
				'release-date' => '2011-02-09',
				'author' => array(
					'name'  => 'Marco Sampellegrini',
					'email' => 'm@rcosa.mp'
				),
				'description' => 'Extracts Iptc/Exif data from images.'
			);
		}
	}

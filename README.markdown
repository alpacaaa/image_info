# Image Information #

- Version: 0.2
- Date: 4th April 2012
- Github Repository: <http://github.com/alpacaaa/image_info/>


## Synopsis

This is a Symphony CMS extension that extracts Iptc and Exif data from uploaded images.

### Server Requirements

- PHP compiled with `--enable-exif` (only for exif metadata).

## Installing

Install [as always](http://symphony-cms.com/learn/tasks/view/install-an-extension/).
You don't need to enable it.

## Usage

This extension provides a sort of webservice that returns XML you can use as a dynamic datasource.

The URL to use is the following: `{$root}/extensions/image_info/service.php`

Image Information accepts 5 parameters:

- `section`*
An handle or a section id
- `entries`*
a set of entries ids comma separated (eg.: 1,5,7)
- `field_name`*
field handle of the upload field where images are stored
- `iptc`
whether or not to include iptc info (bool, default to **true**)
- `exif`
whether or not to include exif info (bool, default to **true**)

\* parameter is *required*


To pass parameters, just append them to the url. The following is a valid possible call:

    {$root}/extensions/image_info/service.php?section=my-images&entries=10,12,17,22&field_name=file&iptc=false

This would output something like this:

    <image-info>
      <image path="/1431294624_14c70d71e8_b.jpg" entry_id="10">
        <iptc/>
        <exif>
          <section name="FILE">
            <data tag="FileName">1431294624_14c70d71e8_b.jpg</data>
            <data tag="FileDateTime">1269940534</data>
            ...
          </section>
          <section name="COMPUTED">
            <data tag="html">width="1024" height="768"</data>
            ...
          </section>
        </exif>
      </image>
      <image path="/s_gps.jpg" entry_id="12">
        <iptc>
          <data tag="" handle="2#000"/>
          <data tag="Description" handle="2#120">Communications</data>
          <data tag="DescriptionWriter" handle="2#122">Ian Britton</data>
          <data tag="Headline" handle="2#105">Communications</data>
          <data tag="Creator" handle="2#080">Ian Britton</data>
          <data tag="CreatorJobTitle" handle="2#085">Photographer</data>
          <data tag="Provider" handle="2#110">Ian Britton</data>
          ...
        </iptc>
        <exif>
          <section name="FILE">
            <data tag="FileName">s_gps.jpg</data>
            ...
          </section>
          <section name="COMPUTED">
            <data tag="html">width="600" height="400"</data>
            ..
          </section>
          <section name="IFD0">
            <data tag="ImageDescription">Communications</data>
            <data tag="Make">FUJIFILM</data>
            <data tag="Model">FinePixS1Pro</data>
            ...
          </section>
          <section name="THUMBNAIL">
            ...
          </section>
          <section name="EXIF">
            <data tag="FNumber">1074135040/1677721600</data>
            ...
          </section>
          <section name="GPS">
            <data tag="GPSVersion"/>
            <data tag="GPSLatitudeRef">N</data>
            <data tag="GPSLatitude">54/1,5938/100,0/1</data>
            ...
          </section>
        </exif>
      </image>
    </image-info>

Yeah... you already got it!
You can use a dynamic XML datasource to have these informations available on your page.

Obviously the url you supply to the DS doesn't have to be static. You can use any datasource System ID parameter output to dinamically pass parameters to the extension.

Therefore, something as simple as this would work as expected:

    {$root}/extensions/image_info/service.php?section=my-images&entries={$ds-whatever}&field_name=file

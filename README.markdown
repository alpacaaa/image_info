# Image Information #

- Version: 1.0
- Date: 2nd April 2010
- Github Repository: <http://github.com/alpacaaa/image-info/>


## Synopsis

This is a Symphony CMS extension that extracts Iptc and Exif data from uploaded images.

### Server Requirements

- PHP compiled with `--enable-exif` (only for exif metadata).

## Installing

Install [as always](http://symphony-cms.com/learn/tasks/view/install-an-extension/).
You don't need to enable it.

## Usage

This extension provides a sort of webservice that returns XML you can use as a dynamic datasource.

The URL to use is the following: `yourdomain.com/extensions/image_info/service.php`
(If you have installed symphony in a sub folder, you have to change the url accordingly).

Image Information accepts 4 parameters:

- `section`*
An handle or a section id
- `entries`*
a set of entries ids comma separated (eg.: 1,5,7)
- `iptc`
whether or not to include iptc info (bool, default to **true**)
- `exif`
whether or not to include exif info (bool, default to **true**)

\* parameter is *required*


To pass parameters, just append them to the url. The following is a valid possible call:

    yourdomain.com/extensions/image_info/service.php?section=my-images&entries=9,12,17,22&iptc=false

This would output something like this:

    <image-info>
      <image path="/1431294624_14c70d71e8_b.jpg">
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
      <image path="/s_gps.jpg">
        <iptc>
          <data tag="2#000"/>
          <data tag="2#005">Communications</data>
          <data tag="2#055">20020620</data>
          <data tag="2#090"> </data>
          <data tag="2#095"> </data>
          <data tag="2#101">Ubited Kingdom</data>
          <data tag="2#015">BUS</data>
          <data tag="2#020">Communications</data>
          <data tag="2#010">5</data>
          <data tag="2#025">Communications</data>
          <data tag="2#116">ian Britton - FreeFoto.com</data>
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

    yourdomain.com/extensions/image_info/service.php?section=my-images&entries={$ds-whatever}

<?php
use Gwa\Flickr\gwFlickrPhotoSet;

class gwFlickrPhotoSetTest extends PHPUnit_Framework_TestCase
{
	public function testRead()
	{
		$photoset = gwFlickrPhotoSet::read(1, 'abcdef');
		$this->assertInstanceOf('\Gwa\Flickr\gwFlickrPhotoSet', $photoset);
	}

	public function testGetData()
	{
		$config = require __DIR__.'/../config.php';
		$photoset = gwFlickrPhotoSet::read($config['idphotoset'], $config['flickrapikey']);
		$data = $photoset->getData();
		$this->assertInternalType('array', $data);
		$photo = $data[0];
		$this->assertInternalType('array', $photo);
		$this->assertTrue(array_key_exists('id', $photo));
	}
}

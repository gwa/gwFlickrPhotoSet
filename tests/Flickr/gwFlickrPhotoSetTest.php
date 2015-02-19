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
		$config = require $this->getConfig();

		$photoset = gwFlickrPhotoSet::read($config['idphotoset'], $config['flickrapikey']);
		$data = $photoset->getData();
		$this->assertInternalType('array', $data);
		$photo = $data[0];
		$this->assertInternalType('array', $photo);
		$this->assertTrue(array_key_exists('id', $photo));
	}

	public function testCache()
	{
		$config = require $this->getConfig();

		$photoset = gwFlickrPhotoSet::read($config['idphotoset'], $config['flickrapikey'], __DIR__.'/../temp');
		$data = $photoset->getData();
		$this->assertInternalType('array', $data);

		$photoset = gwFlickrPhotoSet::read($config['idphotoset'], $config['flickrapikey'], __DIR__.'/../temp');
		$cache = $photoset->getCache();
		$this->assertTrue($cache->isCached());
		$cache->clear();
	}

	public function testException()
	{
		try {
			$config = require $this->getConfig();

			$photoset = gwFlickrPhotoSet::read($config['idphotoset2'], $config['flickrapikey'], __DIR__.'/../temp');
			$data = $photoset->getData();
		} catch (Exception $exception) {
			return;
		}

		$this->fail('An expected exception has not been raised.');
	}

	protected function getConfig()
	{
		$localConfig = __DIR__.'/../config.php';

		if (file_exists($localConfig)) {
			return $localConfig;
		} else {
			return __DIR__.'/../flickrConfig.php';
		}
	}
}

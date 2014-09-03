<?php
namespace Gwa\Flickr;

use Gwa\Exception\gwCoreException;
use Gwa\Cache\gwCache;

/**
 * @brief Model representing a Flickr photoset
 */
class gwFlickrPhotoSet
{
	/**
	 * @var int
	 */
	private $_idphotoset;

	/**
	 * @var string
	 */
	private $_flickrapikey;

	/**
	 * @var Gwa\Cache\gwCache
	 */
	private $_cache;

	/**
	 * @var string
	 */
	private $_cachedir;

	/**
	 * @var int
	 */
	private $_cacheminutes;

	/**
	 * @brief Reads a flickr photoset.
	 * @param int $idphotoset
	 * @param string $flickrapikey
	 * @param string $cachedir
	 * @param int $cacheminutes
	 * @throws gwCoreException
	 * @return gwFlickrPhotoSet
	 */
	static public function read( $idphotoset, $flickrapikey, $cachedir=null, $cacheminutes=30 )
	{
		if (!preg_match('/^[0-9]+$/', $idphotoset)) {
			throw new gwCoreException(
				gwCoreException::ERR_INVALID_ARGUMENT,
				'condition must be a photoset id: '.$idphotoset
			);
		}
		return new gwFlickrPhotoSet($idphotoset, $flickrapikey, $cachedir, $cacheminutes);
	}

	/**
	 * @param int $idphotoset
	 * @param string $flickrapikey
	 * @param string $cachedir
	 * @param int $cacheminutes
	 */
	private function __construct( $idphotoset, $flickrapikey, $cachedir, $cacheminutes )
	{
		$this->_idphotoset = $idphotoset;
		$this->_flickrapikey = $flickrapikey;
		$this->_cachedir = $cachedir;
		$this->_cacheminutes = $cacheminutes;
	}

	/**
	 * @brief Getter method for photoset data.
	 *
	 * ~~~~~~~~
	 * [
	 *     'id' => string
	 *     'secret' => string
	 *     'server' => string
	 *     'farm' => float
	 *     'title' => string
	 *     'isprimary' => string
	 *     'url_s' => string
	 *     'height_s' => string
	 *     'width_s' => string
	 *     'url_l' => string
	 *     'height_l' => string
	 *     'width_l' => string
	 * ]
	 * ~~~~~~~~
	 * @url http://www.flickr.com/services/api/flickr.galleries.getPhotos.html
	 * @return array
	 */
	public function getData()
	{
		if (isset($this->_cachedir)) {
			$cache = $this->getCache();
		}

		$params = array(
			'api_key' => $this->_flickrapikey,
			'method' => 'flickr.photosets.getPhotos',
			'photoset_id' => $this->_idphotoset,
			'extras' => 'url_s,url_l',
			'format' => 'php_serial'
		);
		$encodedparams = array();
		foreach ($params as $k => $v) {
			$encodedparams[] = urlencode($k).'='.urlencode($v);
		}
		$url = 'https://api.flickr.com/services/rest/?'.implode('&', $encodedparams);
		$response = file_get_contents($url);
		$responseobject = unserialize($response);

		if (isset($cache)) {
			$cache->set($responseobject['photoset']['photo']);
		}

		return $responseobject['photoset']['photo'];
	}

	/**
	 * @return Gwa\Cache\gwCache
	 */
	public function getCache()
	{
		if (!isset($this->_cache)) {
			$this->_cache = new gwCache(
				'flickrphotoset-'.$this->_idphotoset,
				$this->_cachedir,
				$this->_cacheminutes,
				gwCache::TYPE_OBJECT
			);
		}
		return $this->_cache;
	}
}

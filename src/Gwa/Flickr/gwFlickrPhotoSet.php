<?php
namespace Gwa\Flickr;

use Gwa\Cache\gwCache;
use Gwa\Exception\gwCoreException;

/**
 * @brief Model representing a Flickr photoset
 */
class gwFlickrPhotoSet
{
    /**
     * @var int
     */
    private $idPhotoset;

    /**
     * @var string
     */
    private $flickrApiKey;

    /**
     * @var \Gwa\Cache\gwCache
     */
    private $cache;

    /**
     * @var string|null
     */
    private $cacheDir;

    /**
     * @var int
     */
    private $cacheMinutes;

    /**
     * @brief Reads a flickr photoset.
     * @param  int              $idphotoset
     * @param  string           $flickrapikey
     * @param  string           $cacheDir
     * @param  int              $cacheMinutes
     *
     * @throws gwCoreException
     *
     * @return gwFlickrPhotoSet
     */
    public static function read($idphotoset, $flickrapikey, $cacheDir = null, $cacheMinutes = 30)
    {
        if (!preg_match('/^[0-9]+$/', $idphotoset)) {
            throw new gwCoreException(
                gwCoreException::ERR_INVALID_ARGUMENT,
                'condition must be a photoset id: '.$idphotoset
            );
        }

        return new gwFlickrPhotoSet($idphotoset, $flickrapikey, $cacheDir, $cacheMinutes);
    }

    /**
     * @param int    $idphotoset
     * @param string $flickrapikey
     * @param string $cacheDir
     * @param int    $cacheMinutes
     */
    private function __construct($idphotoset, $flickrapikey, $cacheDir, $cacheMinutes)
    {
        $this->idPhotoset   = $idphotoset;
        $this->flickrApiKey = $flickrapikey;
        $this->cacheDir     = $cacheDir;
        $this->cacheMinutes = $cacheMinutes;
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
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->cacheDir)) {
            $cache = $this->getCache();
        }

        $params = array(
            'api_key' => $this->flickrApiKey,
            'method' => 'flickr.photosets.getPhotos',
            'photoset_id' => $this->idPhotoset,
            'extras' => 'url_s,url_l',
            'format' => 'php_serial',
        );

        $encodedparams = array();

        foreach ($params as $k => $v) {
            $encodedparams[] = urlencode($k).'='.urlencode($v);
        }

        $url = 'https://api.flickr.com/services/rest/?'.implode('&', $encodedparams);

        $response       = file_get_contents($url);
        $responseobject = unserialize($response);

        $this->checkStat($responseobject['stat']);

        if (isset($cache)) {
            $cache->set($responseobject['photoset']['photo']);
        }

        return $responseobject['photoset']['photo'];
    }

    protected function checkStat($stat = 'ok')
    {
        if ($stat === 'fail') {
            throw new gwCoreException(
                gwCoreException::ERR_INVALID_ARGUMENT,
                'Your id '.$this->idPhotoset.'is not a valid photoset.'
            );
        }
    }

    /**
     * @return \Gwa\Cache\gwCache
     */
    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = new gwCache(
                'flickrphotoset-'.$this->idPhotoset,
                $this->cacheDir,
                $this->cacheMinutes,
                gwCache::TYPE_OBJECT
            );
        }

        return $this->cache;
    }
}

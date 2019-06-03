<?php

namespace RebelCode\Wpra\Core\Importer\Images;

use Aventura\Wprss\Core\Caching\ImageCache;
use Dhii\Di\Exception\ContainerException;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use WPRSS_Image_Cache_Image;

/**
 * A container implementation for fetching remote images and checking if they exist remotely.
 *
 * @since [*next-version*]
 */
class ImageContainer implements ContainerInterface
{
    /**
     * @since [*next-version*]
     *
     * @var ImageCache
     */
    protected $cache;

    /**
     * @since [*next-version*]
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ImageCache      $cache  The image cache instance.
     * @param LoggerInterface $logger The logger instance.
     */
    public function __construct(ImageCache $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     *
     * @return WPRSS_Image_Cache_Image
     */
    public function get($url)
    {
        if (empty($url)) {
            throw new ContainerException(__('Image URL cannot be empty', 'wprss'), 0, null);
        }

        try {
            return $this->cache->get_images($url);
        } catch (Exception $e) {
            $message = $e->getMessage();

            $this->logger->warning(
                'Image could not be downloaded from {url}. Error: {error}',
                [
                    'url' => $url,
                    'error' => $message,
                ]
            );

            throw new ContainerException($message);
        }
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function has($url)
    {
        $exists = false;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        $response = curl_exec($curl);

        if ($response !== false && curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
            $exists = true;
        }

        curl_close($curl);

        return $exists;
    }
}

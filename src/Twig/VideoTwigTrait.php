<?php

namespace PiedWeb\CMSBundle\Twig;

use PiedWeb\CMSBundle\Service\AppConfig;

trait VideoTwigTrait
{
    abstract public function getApp(): AppConfig;

    public function renderVideo($url, $image, $alternativeText = '', $forceUrl = false)
    {
        $template = $this->getApp()->getView('/component/video.html.twig', $this->twig);
        $youtube = $forceUrl ? null : static::getYoutubeVideoUrl($url);

        return trim($this->twig->render($template, [
            'url' => $youtube ? $youtube : $url,
            'image' => $image,
            'alt' => $alternativeText,
            'embed_code' => $youtube && ! $forceUrl ? $this->getEmbedCode($url) : null,
        ]));
    }

    protected static function getYoutubeVideoUrl($url)
    {
        if (preg_match('~^(?:https?://)?(?:www[.])?(?:youtube[.]com/watch[?]v=|youtu[.]be/)([^&]{11})~', $url, $m)) {
            return $m[1];
        }
    }

    public function getEmbedCode($embed_code)
    {
        if ($id = self::getYoutubeVideoUrl($embed_code)) {
            $template = $this->getApp()->getView('/component/video_youtube_embed.html.twig', $this->twig);

            return $this->twig->render($template, ['id' => $id]);
        }
    }
}

<?php

namespace PiedWeb\CMSBundle\Twig;

trait VideoTwigTrait
{
    public function renderVideo($url, $image, $alternativeText)
    {
        $template = $this->getApp()->getTemplate('/component/video.html.twig', $this->twig);

        return trim($this->twig->render($template, [
            'url' => static::getYoutubeVideoUrl($url),
            'image' => $image,
            'alt' => $alternativeText,
        ]));
    }

    protected static function getYoutubeVideoUrl($url)
    {
        if (preg_match('~^(?:https?://)?(?:www[.])?(?:youtube[.]com/watch[?]v=|youtu[.]be/)([^&]{11})~', $url, $m)) {
            return $m[1];
        }

        return $url;
    }

    public static function getEmbedCode($embed_code)
    {
        if ($id = self::getYoutubeVideoUrl($embed_code)) {
            $embed_code = '<iframe src=https://www.youtube-nocookie.com/embed/'.$id.' frameborder=0'
                    .' allow=autoplay;encrypted-media allowfullscreen class=w-100></iframe>';
        }

        return $embed_code;
    }
}

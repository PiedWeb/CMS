<?php

namespace PiedWeb\CMSBundle\EventListener;

use Vich\UploaderBundle\Event\Event;
use Doctrine\ORM\EntityManager;

class MediaListener
{
    private $projectDir;
    private $iterate = 1;
    private $em;

    public function __construct(string $projectDir, EntityManager $em)
    {
        $this->projectDir = $projectDir;
        $this->em = $em;
    }

    /**
     * Check if name exist.
     */
    public function onVichUploaderPreUpload(Event $event)
    {
        $media = $event->getObject();

        $this->checkIfThereIsAName($media);
        $this->checkIfNameEverExistInDatabase($media);
        $this->checkIfFileLocationIsChanging($media);
    }

    /**
     * Todo: log i
     * If file location is changing
     * Create a redirection to the new Image.
     */
    private function checkIfFileLocationIsChanging($media)
    {
        if (null !== $media->getName() && 1 == 'todo') {
            // TODO
        }
    }

    /**
     * Si l'utilisateur ne propose pas de nom pour l'image,
     * on récupère celui d'origine duquel on enlève son extension.
     */
    private function checkIfThereIsAName($media)
    {
        if (null === $media->getName() || empty($media->getName())) {
            $media->setName(preg_replace('/\\.[^.\\s]{3,4}$/', '', $media->getMediaFile()->getClientOriginalName()));
        }
    }

    private function checkIfNameEverExistInDatabase($media)
    {
        $same = $this->em->getRepository('PiedWebCMS:Media')->findOneBy(['name' => $media->getName()]);
        if ($same && (null == $media->getId() || $media->getId() != $same->getId())) {
            $media->setName($media->getName().' ('.$this->iterate.')');
            ++$this->iterate;
            $this->iterateName($media);
        }

        return $media;
    }

    /**
     * Update RelativeDir.
     */
    public function onVichUploaderPostUpload(Event $event)
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();

        $absoluteDir = $mapping->getUploadDestination().'/'.$mapping->getUploadDir($object);
        $relativeDir = substr_replace($absoluteDir, '', 0, strlen($this->projectDir) + 1);

        $object->setRelativeDir($relativeDir);
    }
}

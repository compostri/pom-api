<?php

namespace App\EventListener;

use App\Entity\MediaObject;
use Intervention\Image\ImageManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MediaObjectListener
{
    private $parameterBag;
    private $urlHelper;
    private $params;

    public function __construct(ParameterBagInterface $parameterBag, UrlHelper $urlHelper, ContainerBagInterface $params)
    {
        $this->parameterBag = $parameterBag;
        $this->urlHelper = $urlHelper;
        $this->params = $params;
    }

    /**
     * @throws \Exception
     */
    public function prePersist(MediaObject $mediaObject): void
    {
        $this->uploadMedia($mediaObject);
    }

    public function postRemove(MediaObject $mediaObject): void
    {
        $imageName = $mediaObject->getImageName();
        $webPath = $this->parameterBag->get('upload_destination').$imageName;
        $filesystem = new Filesystem();
        $filesystem->remove($webPath);
    }

    public function postLoad(MediaObject $mediaObject): void
    {
        $mediaObject->setContentUrl($this->getAbsoluteUrl($mediaObject->getImageName()));
    }

    /**
     * @throws \Exception
     */
    private function uploadMedia(MediaObject $mediaObject): void
    {
        $data = $mediaObject->getData();
        if (!$data) {
            throw new BadRequestHttpException('Paramétre "data" obligatoire');
        }

        $uploadDir = $this->parameterBag->get('upload_destination');

        // Fonction qui renome si besoin les fichier pour éviter les doublons
        $this->setRealFileName($mediaObject);

        $webPath = $uploadDir.$mediaObject->getImageName();

        // Si on a pas encore de fichier il est temps de tenté de le télécharger
        $content = 0 === strpos($data, 'data') ?
            file_get_contents($data) :
            base64_decode($data);

        file_put_contents($webPath, $content);

        $newFile = new File($webPath);

        $mediaObject->setFile($newFile);

        // On retaille les images
        if ($this->isImage($newFile)) {
            $manager = new ImageManager(['driver' => 'imagick']);

            $manager->make($webPath)
                ->widen(942, function ($constraint) {
                    $constraint->upsize();
                })
                ->save($webPath);

            $mediaObject->setImageDimensions(getimagesize($webPath));
        }
    }

    private function getAbsoluteUrl(string $imageName): string
    {
        $dir = str_replace($this->params->get('kernel.project_dir').'/public', '', $this->params->get('upload_destination'));

        return $this->urlHelper->getAbsoluteUrl($dir.$imageName);
    }

    private function isImage(File $file): bool
    {
        $mimeType = $file->getMimeType();

        return 0 === strpos($mimeType, 'image');
    }

    /**
     * Détermine le vrai nom du fichier en partant du nom demander et en évitant les doublons.
     */
    private function setRealFileName(MediaObject $mediaObject): void
    {
        $imageName = $mediaObject->getImageName();

        if (!$imageName) {
            throw new BadRequestHttpException('Paramétre "imageName" obligatoire');
        }

        $uploadDir = $this->parameterBag->get('upload_destination');
        // On rénome les fichiers qui ont le même nom
        if (file_exists($uploadDir.$imageName)) {
            $imageName = preg_replace('/(.)([^.]+)$/', '-'.uniqid('', false).'.$2', $imageName);
            $mediaObject->setImageName($imageName);
        }
    }
}

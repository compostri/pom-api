<?php

namespace App\Controller;

use App\Entity\Composter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Annotation\Route;

class ComposterController extends AbstractController
{
    private $urlHelper;

    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @Route("/composters.geojson", name="composters-geojson")
     */
    public function getCompostersGeojson(Request $request): Response
    {
        $composters = $this->getDoctrine()
            ->getRepository(Composter::class)
            ->findAllForFrontMap();

        // On prépare un GeoJSON de centre formater le l'affichage sur la carte
        $features = [];
        /** @var Composter $c */
        foreach ($composters as $c) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$c->getLng(), $c->getLat()],
                ],
                'properties' => [
                    'commune' => $c->getCommune() ? $c->getCommune()->getId() : null,
                    'communeName' => $c->getCommune() ? $c->getCommune()->getName() : null,
                    'categorie' => $c->getCategorie() ? $c->getCategorie()->getId() : null,
                    'categorieName' => $c->getCategorie() ? $c->getCategorie()->getName() : null,
                    'id' => $c->getId(),
                    'slug' => $c->getSlug(),
                    'name' => $c->getName(),
                    'status' => $c->getStatus(),
                    'acceptNewMembers' => $c->getAcceptNewMembers(),
                    'image' => $c->getImage() ? $this->getImageUrl($c->getImage()->getImageName()) : null,
                ],
            ];
        }
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];

        return $this->json($geojson);
    }

    /**
     * @Route("/composters-cartoquartiers.json", name="composters-cartoquartiers")
     */
    public function getCompostersCartoQuartiers(Request $request): Response
    {
        $composters = $this->getDoctrine()
            ->getRepository(Composter::class)
            ->findAllForCartoQuartierFrontMap();

        $cartoQuartierfeatures = [];

        /** @var Composter $c */
        foreach ($composters as $c) {
            $description = $c->getDescription();

            if (!$description) {
                if (3 === $c->getCategorie()->getId()) {
                    $description = 'Cet équipement est réservé à un usage pédagogique au sein de l’école';
                } else {
                    $description = 'Vous pouvez déposer vos déchets organiques lors des permanences assurées par un collectif d’habitants. Distribution de compost en retour. Nous vous invitons à vérifier qu’il reste de la place auprès du référent en le contactant via le formulaire ou en vous rendant sur place lors d[’une permanence';
                }
            }
            $cartoQuartierfeatures[] = [
                'IDOBJ' => $c->getSerialNumber(),
                'descriptif' => $description,
                'photo' => $c->getImage() ? $this->getImageUrl($c->getImage()->getImageName()) : 'https://www.cartoquartiers.fr/medias/2018/02/9.compostage.jpg',
                'horaires' => $c->getPermanencesDescription(),
                'mail' => 'https://composteurs.compostri.fr/composteur/'.$c->getSlug(),
            ];
        }

        return $this->json($cartoQuartierfeatures);
    }

    /**
     * @Route("/composters-opendata.json", name="composters-opendata")
     */
    public function getCompostersOpenData(Request $request): Response
    {
        $composters = $this->getDoctrine()
            ->getRepository(Composter::class)
            ->findAllForFrontMap();

        $openDataFeatures = [];
        /** @var Composter $c */
        foreach ($composters as $c) {
            $openDataFeatures[] = [
                'id' => ''.$c->getId(),
                'nom' => $c->getName(),
                'categorie' => $c->getCategorie() ? $c->getCategorie()->getName() : null,
                'adresse' => $c->getAddress(),
                'lieu' => $c->getCommune() ? $c->getCommune()->getName() : null,
                'annee' => date_format($c->getDateMiseEnRoute(), 'Y'),
                'lat' => $c->getLat(),
                'lon' => $c->getLng(),
                'lien' => getenv('FRONT_DOMAIN').'/composteur/'.$c->getSlug(),
            ];
        }

        return $this->json($openDataFeatures);
    }

    private function getImageUrl(string $imageName): string
    {
        $dir = str_replace($this->getParameter('kernel.project_dir').'/public', '', $this->getParameter('upload_destination'));

        return $this->urlHelper->getAbsoluteUrl($dir.$imageName);
    }
}

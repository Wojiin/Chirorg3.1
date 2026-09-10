<?php

namespace App\Controller;

use App\Service\TechnicalSheetImageStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** Adapte la requête HTTP d’upload au stockage des illustrations techniques. */
#[IsGranted('ROLE_ADMIN')]
final readonly class TechnicalSheetImageUploadController
{
    public function __construct(private TechnicalSheetImageStorage $imageStorage)
    {
    }

    #[Route('/api/fiche-technique-images', name: 'api_fiche_technique_image_upload', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $url = $this->imageStorage->store($request->files->get('image'));

        return new JsonResponse(['url' => $url], Response::HTTP_CREATED);
    }
}

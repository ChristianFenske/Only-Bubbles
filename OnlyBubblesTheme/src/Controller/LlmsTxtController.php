<?php declare(strict_types=1);

namespace OnlyBubblesTheme\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * /llms.txt für Only Bubbles (Inhalt: Resources/llms/llms.txt).
 * Läuft in derselben Shopware-Installation wie Weine & Feinkost -> nur auf den Only-Bubbles-Domains ausliefern.
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
class LlmsTxtController
{
    #[Route(path: '/llms.txt', name: 'frontend.only_bubbles.llms_txt', methods: ['GET'])]
    public function llmsTxt(Request $request): Response
    {
        $host = strtolower($request->getHost());
        if (!str_contains($host, 'bubbles')) {
            throw new NotFoundHttpException();
        }

        $content = (string) file_get_contents(__DIR__ . '/../Resources/llms/llms.txt');

        $response = new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=UTF-8']);
        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}

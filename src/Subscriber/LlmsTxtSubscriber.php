<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Liefert /llms.txt für weine-feinkost.de aus (Datei: Resources/llms/weine-feinkost.txt).
 * Nur für diese Domain – andere Verkaufskanäle (z. B. Only Bubbles) sind nicht betroffen.
 */
class LlmsTxtSubscriber implements EventSubscriberInterface
{
    private const FILE = __DIR__ . '/../Resources/llms/weine-feinkost.txt';

    public static function getSubscribedEvents(): array
    {
        // vor dem Routing (Priorität 32), damit kein 404 entsteht
        return [KernelEvents::REQUEST => ['onRequest', 64]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (rtrim($request->getPathInfo(), '/') !== '/llms.txt') {
            return;
        }

        $host = strtolower($request->getHost());
        if ($host !== 'weine-feinkost.de' && !str_ends_with($host, '.weine-feinkost.de')) {
            return;
        }

        $content = @file_get_contents(self::FILE);
        if ($content === false) {
            return;
        }

        $event->setResponse(new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]));
    }
}

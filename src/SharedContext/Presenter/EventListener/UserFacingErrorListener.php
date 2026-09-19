<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Service\Flash\FlashServiceInterface;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[AsEventListener(KernelEvents::EXCEPTION)]
final readonly class UserFacingErrorListener
{
    public function __construct(
        private TranslatorInterface $translator,
        private FlashServiceInterface $flash,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {

        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!$exception instanceof UserFacingError) {
            return;
        }

        $message = $this->translator->trans(
            $exception->translationId(),
            $exception->translationParameters(),
            $exception->translationDomain()
        );

        /** ✅ Détection API fiable */
        $isApiRequest = $request->getPreferredFormat() === 'json';

        if ($isApiRequest) {
            $event->setResponse(new JsonResponse([
                'type' => 'https://symfony.com/errors/domain',
                'title' => $exception->translationId(),
                'detail' => $message,
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ], Response::HTTP_UNPROCESSABLE_ENTITY));
            return;
        }

        $this->flash->danger($message);

        $event->setResponse(
            new RedirectResponse(SafeRedirectUrlResolver::resolve($request, '/'))
        );
    }
}

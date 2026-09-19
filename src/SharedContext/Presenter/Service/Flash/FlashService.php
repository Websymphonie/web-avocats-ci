<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Flash;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Websymphonie\SharedContext\Domain\Enum\FlashEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Service\Flash\FlashServiceInterface;

readonly class FlashService implements FlashServiceInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private RequestStack        $requestStack
    )
    {
    }

    public function success(string $message): void
    {
        $this->addFlash(FlashEnum::SUCCESS->value, $message);
    }

    private function addFlash(string $type, string $message): void
    {
        $flashBag = $this->getSession()->getBag('flashes');
        if ($flashBag instanceof FlashBagInterface) {
            $flashBag->add($type, $message);
        }
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function info(string $message): void
    {
        $this->addFlash(FlashEnum::INFO->value, $message);
    }

    public function warning(string $message): void
    {
        $this->addFlash(FlashEnum::WARNING->value, $message);
    }

    public function danger(string $message): void
    {
        $this->addFlash(FlashEnum::DANGER->value, $message);
    }

    public function errorFromException(UserFacingError $exception): void
    {
        $translatedMessage = $this->translator->trans(
            $exception->translationId(),
            $exception->translationParameters(),
            $exception->translationDomain()
        );

        $this->addFlash(FlashEnum::DANGER->value, $translatedMessage);
    }
}

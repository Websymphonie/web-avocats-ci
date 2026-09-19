<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Framework\Symfony\Handlers;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsMonologProcessor(channel: 'db')]
readonly class DbLogProcessor
{
    public function __construct(
        private RequestStack $requestStack,
        private Security     $security
    )
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getMainRequest();
        $record->extra['clientIp'] = $request?->getClientIp();
        $record->extra['url'] = $request?->getBaseUrl();
        $record->extra['user'] = $this->security->getUser();
        return $record;
    }
}
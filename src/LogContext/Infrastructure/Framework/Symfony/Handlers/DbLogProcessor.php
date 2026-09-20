<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Framework\Symfony\Handlers;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Websymphonie\LogContext\Application\Service\SensitiveLogDataSanitizer;

#[AsMonologProcessor(channel: 'db')]
readonly class DbLogProcessor
{
    public function __construct(
        private RequestStack $requestStack,
        private Security     $security,
        private SensitiveLogDataSanitizer $sanitizer,
    )
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getMainRequest();
        $user = $this->security->getUser();
        $record->extra['clientIp'] = $request?->getClientIp();
        $record->extra['url'] = $request?->getPathInfo();
        $record->extra['method'] = $request?->getMethod();
        $record->extra['route'] = $request?->attributes->get('_route');
        $record->extra['user'] = $this->userReference($user);
        $context = $this->sanitizer->sanitize($record->context);
        $extra = $this->sanitizer->sanitize($record->extra);

        return $record->with(
            message: $this->sanitizer->sanitizeMessage($record->message),
            context: is_array($context) ? $context : [],
            extra: is_array($extra) ? $extra : [],
        );
    }

    /** @return array{id: int|null, uuid: string|null}|null */
    private function userReference(?object $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $id = method_exists($user, 'getId') ? $user->getId() : null;
        $uuid = method_exists($user, 'getUuidAsString') ? $user->getUuidAsString() : null;

        return [
            'id' => is_int($id) ? $id : null,
            'uuid' => is_string($uuid) ? $uuid : null,
        ];
    }
}

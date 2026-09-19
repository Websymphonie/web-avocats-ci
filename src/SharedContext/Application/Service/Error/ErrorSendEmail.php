<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Error;

use Websymphonie\SharedContext\Application\Service\Mailing\EmailDefinition;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final readonly class ErrorSendEmail implements EmailDefinition
{
    public function __construct(
        private Email   $recipient,
        private string  $title,
        private ?string $errerMessage = null,
    )
    {
    }

    /**
     * @return Email
     */
    public function recipient(): Email
    {
        return $this->recipient;
    }

    /**
     * @return array<string, mixed>
     */
    public function subjectVariables(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'error/error_mail';
    }

    /**
     * @return array<string, mixed>
     */
    public function templateVariables(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->errerMessage,
        ];
    }

    /**
     * @return string
     */
    public function subject(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function locale(): string
    {
        return 'fr';
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return 'error_context';
    }
}

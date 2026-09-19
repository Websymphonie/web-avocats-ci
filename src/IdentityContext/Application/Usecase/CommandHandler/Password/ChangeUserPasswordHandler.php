<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\Password;

use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeUserPasswordCommand;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Validator\User\ChangeUserPasswordValidator;
use Websymphonie\IdentityContext\Presenter\Service\Password\PasswordChange;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\Service\Event\NotificationEvent;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;


final readonly class ChangeUserPasswordHandler implements CommandHandler
{
    public function __construct(
        private CurrentUserProvider          $currentUserProvider,
        private UserModelRepositoryInterface $repository,
        private ChangeUserPasswordValidator  $validator,
        private EventDispatcher              $eventDispatcher,
        private PasswordChange               $passwordChanger
    )
    {
    }

    public function __invoke(ChangeUserPasswordCommand $command): User
    {
        $author = $this->currentUserProvider->getUser();
        $this->validator->validate($command);

        $user = $this->repository->getById($command->id);

        $resultUser = $this->passwordChanger->changePassword($user, $command->password);

        $message = "Votre mot de passe à été modifié.";
        $event = new NotificationEvent(
            title: 'Mot de passe',
            message: $message,
            type: NotificationTypeEnum::NOTIF_PASSWORD,
            action: NotificationActionEnum::NOTIF_UPDATE,
            userIds: [$resultUser->getId()], //userIds: [1, 2, 3]
            context: ['userId' => $resultUser->getId(), 'author' => ['id' => $author->getId(), 'email' => $author->getEmail()]],
        );
        $resultUser->emitEvent($event);
        $this->eventDispatcher->dispatch($resultUser->releaseEvents());
        return $resultUser;
    }
}

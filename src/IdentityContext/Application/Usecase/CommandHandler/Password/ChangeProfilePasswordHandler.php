<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\Password;


use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeProfilePasswordCommand;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Validator\User\ChangeProfilePasswordValidator;
use Websymphonie\IdentityContext\Presenter\Service\Password\PasswordChange;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\Service\Event\NotificationEvent;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class ChangeProfilePasswordHandler implements CommandHandler
{
    public function __construct(
        private CurrentUserProvider            $currentUserProvider,
        private UserModelRepositoryInterface   $repository,
        private ChangeProfilePasswordValidator $validator,
        private EventDispatcher                $eventDispatcher,
        private PasswordChange                 $passwordChanger
    )
    {
    }

    public function __invoke(ChangeProfilePasswordCommand $command): User
    {
        $author = $this->currentUserProvider->getUser();
        $this->validator->validate($command);

        $user = $this->repository->getById($command->id);
        $this->passwordChanger->validateCurrentPassword($user, $command->currentPassword);
        $resultUser = $this->passwordChanger->changePassword($user, $command->password);

        $resultUser->emitEvent(new NotificationEvent(
            title: 'Mot de passe',
            message: 'Vous avez modifié votre mot de passe.',
            type: NotificationTypeEnum::NOTIF_PASSWORD,
            action: NotificationActionEnum::NOTIF_UPDATE,
            userIds: [$resultUser->getId()], //userIds: [1, 2, 3]
            context: ['userId' => $resultUser->getId(), 'author' => ['id' => $author->getId(), 'email' => $author->getEmail()]],
        ));
        $this->eventDispatcher->dispatch($resultUser->releaseEvents());

        return $resultUser;
    }
}
<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User\Password;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeUserPasswordCommand;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Presenter\Form\Password\UsersChangePasswordType;

#[AsTwigComponent('UserPasswordChangeModalFormComponent', template: 'identity/user/components/password/user_change_password_modal_form_component.html.twig')]
class UserPasswordChangeModalFormComponents extends AbstractController
{
    use ComponentWithFormTrait;

    #[LiveProp(useSerializerForHydration: true)]
    public ?ChangeUserPasswordCommand $command = null;

    #[LiveProp]
    public string $id;

    #[LiveProp]
    public string $title;

    #[LiveProp]
    public string $urlPath;

    public function __construct(private readonly Security $security)
    {
    }

    public function mount(?ChangeUserPasswordCommand $command = null): void
    {
        if ($command !== null) {
            /** @var User $user */
            $user = $this->security->getUser();
            $command->id = $user->getId();
            $this->command = $command;
        }
    }

    /** @return FormInterface<ChangeUserPasswordCommand> */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UsersChangePasswordType::class, $this->command, [
            'attr' => ['id' => 'form-user-' . $this->id]
        ]);
    }
}

<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User\Password;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeProfilePasswordCommand;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Presenter\Form\Password\ProfileChangePasswordType;

#[AsTwigComponent('ProfilePasswordChangeModalFormComponent', template: 'identity/user/components/password/profile_change_password_modal_form_component.html.twig')]
class ProfilePasswordChangeModalFormComponents extends AbstractController
{
    use ComponentWithFormTrait;

    public ?ChangeProfilePasswordCommand $command = null;

    public ?string $id = null;

    public ?string $title = null;

    public ?string $urlPath = null;

    public function __construct(private readonly Security $security)
    {
    }

    public function mount(
        #[MapQueryParameter] ?string  $id = null,
        #[MapQueryParameter] ?string  $title = null,
        #[MapQueryParameter] ?string  $urlPath = null,
        ?ChangeProfilePasswordCommand $command = null
    ): void
    {
        $this->id = $id ?? $this->id;
        $this->title = $title ?? $this->title;
        $this->urlPath = $urlPath ?? $this->urlPath;

        /** @var User $user */
        $user = $this->security->getUser();

        if ($command !== null) {
            $command->id = $user->getId();
            $this->command = $command;
        } else {
            $this->command = new ChangeProfilePasswordCommand();
            $this->command->id = $user->getId();
        }
    }

    /** @return FormInterface<ChangeProfilePasswordCommand> */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ProfileChangePasswordType::class, $this->command);
    }
}

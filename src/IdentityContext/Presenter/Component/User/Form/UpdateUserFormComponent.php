<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Presenter\Form\User\UpdateUserFormType;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[AsTwigComponent('UpdateUserFormComponent', template: 'identity/user/components/form/update_user_form_component.html.twig')]
class UpdateUserFormComponent extends AbstractController
{
    use ComponentWithFormTrait;

    public ?UpdateUserCommand $command = null;

    public function mount(UpdateUserCommand $command): void
    {
        $this->command = $command;
    }

    /** @return FormInterface<UpdateUserCommand> */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UpdateUserFormType::class, $this->command);
    }
}

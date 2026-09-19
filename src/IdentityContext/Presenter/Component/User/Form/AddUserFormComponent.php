<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\AddUserCommand;
use Websymphonie\IdentityContext\Presenter\Form\User\AddUserFormType;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[AsTwigComponent('AddUserFormComponent', template: 'identity/user/components/form/add_user_form_component.html.twig')]
class AddUserFormComponent extends AbstractController
{
    use ComponentWithFormTrait;

    public ?AddUserCommand $command = null;

    /** @return FormInterface<AddUserCommand> */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(AddUserFormType::class, $this->command);
    }
}

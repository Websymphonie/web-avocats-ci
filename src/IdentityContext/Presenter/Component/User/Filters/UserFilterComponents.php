<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Component\User\Filters;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Application\Usecase\Query\User\GetUserListQuery;
use Websymphonie\IdentityContext\Presenter\Form\User\UserFilterType;

#[AsTwigComponent('UserFilterComponent', template: 'identity/user/components/filter/user_filter_component.html.twig')]
class UserFilterComponents extends AbstractController
{
    use ComponentWithFormTrait;

    public ?GetUserListQuery $query = null;

    public string $id;
    public string $title;

    public function mount(string $id, string $title, ?GetUserListQuery $query = null): void
    {
        if ($query !== null) {
            $this->query = $query;
        }
        $this->id = $id;
        $this->title = $title;
    }

    /** @return FormInterface<GetUserListQuery> */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UserFilterType::class, $this->query);
    }
}

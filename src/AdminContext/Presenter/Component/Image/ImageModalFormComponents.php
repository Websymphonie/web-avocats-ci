<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Component\Image;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\AdminContext\Application\Usecase\Command\Image\UpdateImageCommand;
use Websymphonie\AdminContext\Domain\Model\Image\ImageModel;
use Websymphonie\AdminContext\Presenter\Form\Parametre\UpdateImageFormType;

#[AsTwigComponent('ImageModalFormComponent', template: 'admin/image/component/image_modal_form_component.html.twig')]
class ImageModalFormComponents extends AbstractController
{
    use ComponentWithFormTrait;

    public ?UpdateImageCommand $command = null;

    public string $id;

    public string $title;

    public ?string $urlPath = 'javascript:';

    public function mount(?ImageModel $image = null): void
    {
        if ($image !== null) {
            $this->command = new UpdateImageCommand(
                id: $image->id,
                name: $image->name,
                label: $image->label,
                filename: $image->url,
            );
        }
    }

    /** @return FormInterface<UpdateImageCommand> */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UpdateImageFormType::class, $this->command);
    }
}

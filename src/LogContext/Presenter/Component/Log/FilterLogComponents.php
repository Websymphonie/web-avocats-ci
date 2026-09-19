<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Component\Log;

use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('FilterLogComponent', template: 'logs/log/component/filter_log_component.html.twig')]
class FilterLogComponents
{
    public ?FormView $form = null;
    public ?string $id = null;
    public ?string $title = null;
}
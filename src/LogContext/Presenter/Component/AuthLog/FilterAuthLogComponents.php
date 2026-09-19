<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Presenter\Component\AuthLog;

use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('FilterAuthLogComponent', template: 'logs/authlog/components/filter_auth_log_component.html.twig')]
class FilterAuthLogComponents
{
    public ?FormView $form = null;
    public ?string $id = null;
    public ?string $title = null;
}
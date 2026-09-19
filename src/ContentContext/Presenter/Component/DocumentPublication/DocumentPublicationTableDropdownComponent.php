<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\DocumentPublication;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;

#[AsTwigComponent('DocumentPublicationTableDropdown', template: 'content/admin/document/components/table_dropdown_component.html.twig')]
final class DocumentPublicationTableDropdownComponent { public DocumentPublication $document; public string $instanceId = ''; }

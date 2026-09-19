<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Component\DocumentPublication;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteDocumentPublications', template: 'content/admin/document/components/bulk_delete_component.html.twig')]
final class BulkDeleteDocumentPublicationsComponent { public string $formId = 'document-bulk-delete'; }

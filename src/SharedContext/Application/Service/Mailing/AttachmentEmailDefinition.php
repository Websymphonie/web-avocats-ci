<?php
declare(strict_types=1);
namespace Websymphonie\SharedContext\Application\Service\Mailing;
interface AttachmentEmailDefinition extends EmailDefinition { /** @return list<array{filename:string,content:string,mediaType:string}> */ public function attachments(): array; }

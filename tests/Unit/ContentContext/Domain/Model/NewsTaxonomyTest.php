<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Domain\Model\Tag;

final class NewsTaxonomyTest extends TestCase
{
    public function testNewsReplacesCategoriesAndTagsAsDomainModels(): void
    {
        $news = new News(1, 'uuid', 'Titre', 'titre', null, 'Corps');
        $category = new NewsCategory(2, 'category-uuid', 'Institution', 'institution');
        $tag = new Tag(3, 'tag-uuid', 'Formation', 'formation');

        $news->replaceCategories([$category]);
        $news->replaceTags([$tag]);

        self::assertSame([$category], $news->categories);
        self::assertSame([$tag], $news->tags);
    }
}

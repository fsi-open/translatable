<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Entity;

class Comment
{
    private ?int $id = null;
    private ?string $content;
    private ?ArticleTranslation $translation;

    public function __construct(?string $content = null)
    {
        $this->content = $content;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getTranslation(): ?ArticleTranslation
    {
        return $this->translation;
    }

    public function setTranslation(?ArticleTranslation $translation): void
    {
        $this->translation = $translation;
    }
}

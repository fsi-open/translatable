<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Entity;

use FSi\Component\Files\WebFile;

class Banner
{
    private ?int $id = null;
    private ?WebFile $image = null;
    private ?string $imagePath = null;
    private ?ArticleTranslation $translation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?WebFile
    {
        return $this->image;
    }

    public function setImage(?WebFile $image): void
    {
        $this->image = $image;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): void
    {
        $this->imagePath = $imagePath;
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

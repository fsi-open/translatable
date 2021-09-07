<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FSi\Component\Files\WebFile;

class ArticleTranslation
{
    private ?int $id = null;
    private ?string $locale = null;
    private ?string $title = null;
    private ?string $description = null;
    private ?WebFile $photo = null;
    private ?string $photoPath = null;
    private ?Author $author = null;
    private ?Article $article;
    private ?Banner $banner = null;
    /**
     * @var Collection<int, Comment>
     */
    private Collection $comments;

    public function __construct(
        ?string $locale = null,
        ?string $title = null,
        ?string $description = null,
        ?Author $author = null,
        ?Article $article = null
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->description = $description;
        $this->author = $author;
        $this->article = $article;
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPhoto(): ?WebFile
    {
        return $this->photo;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function getBanner(): ?Banner
    {
        return $this->banner;
    }

    public function setBanner(?Banner $banner): void
    {
        $this->banner = $banner;
    }

    /**
     * @return array<Comment>
     */
    public function getComments(): array
    {
        return $this->comments->toArray();
    }
}

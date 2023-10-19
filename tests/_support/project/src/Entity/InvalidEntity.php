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

final class InvalidEntity
{
    private ?int $id = null;
    private ?string $locale;
    private string $nonNullableField = '';
    private ?string $mismatchedField;
    private ?string $undefinedFieldTypeDeclaration;
    /**
     * @var Collection<string, InvalidEntityTranslation>
     */
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getNonNullableField(): string
    {
        return $this->nonNullableField;
    }

    public function setNonNullableField(string $nonNullableField): void
    {
        $this->nonNullableField = $nonNullableField;
    }

    public function getMismatchedField(): ?string
    {
        return $this->mismatchedField;
    }

    public function setMismatchedField(?string $mismatchedField): void
    {
        $this->mismatchedField = $mismatchedField;
    }

    public function getUndefinedFieldTypeDeclaration(): ?string
    {
        return $this->undefinedFieldTypeDeclaration;
    }

    public function setUndefinedFieldTypeDeclaration(?string $undefinedFieldTypeDeclaration): void
    {
        $this->undefinedFieldTypeDeclaration = $undefinedFieldTypeDeclaration;
    }

    /**
     * @return array<string, InvalidEntityTranslation>
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }
}

<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Entity;

final class InvalidEntityTranslation
{
    private ?int $id = null;
    private ?string $locale = null;
    private ?string $nonNullableField = null;
    private ?int $mismatchedField = null;
    private $undefinedFieldTypeDeclaration = null;
    private ?InvalidEntity $entity = null;

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

    public function getEntity(): ?InvalidEntity
    {
        return $this->entity;
    }

    public function setEntity(?InvalidEntity $entity): void
    {
        $this->entity = $entity;
    }
}

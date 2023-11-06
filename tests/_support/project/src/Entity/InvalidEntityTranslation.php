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
    private ?string $locale;
    private ?int $mismatchedField;
    /**
     * @var string|null
     */
    private $undefinedFieldTypeDeclaration;
    private ?InvalidEntity $entity;

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

    public function getMismatchedField(): ?int
    {
        return $this->mismatchedField;
    }

    public function setMismatchedField(?int $mismatchedField): void
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

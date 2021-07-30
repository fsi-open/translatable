<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable;

interface TranslationManager
{
    public function initializeTranslatableWithNoTranslation(object $entity): void;
    public function saveTranslation(object $entity): void;
    public function removeTranslation(object $translation): void;
    public function clearTranslationsForEntity(object $entity): void;
    public function isTranslationEmpty(object $translation): bool;
    /**
     * @param mixed $value
     * @return mixed
     */
    public function sanitizeTranslationValue($value);
    /**
     * @param object $translation
     * @param string $field
     * @param mixed $translatableValue
     * @return mixed
     */
    public function sanitizeTranslatableValue(
        object $translation,
        string $field,
        $translatableValue
    );
}

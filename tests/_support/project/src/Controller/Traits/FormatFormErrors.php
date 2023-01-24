<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Controller\Traits;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

trait FormatFormErrors
{
    /**
     * @param FormErrorIterator<FormError> $errors
     */
    private function formErrorsToMessage(FormErrorIterator $errors): ?string
    {
        $message = '';

        /** @var FormError $error */
        foreach ($errors as $error) {
            /** @var FormInterface<FormInterface> $origin */
            $origin = $error->getOrigin();
            $message .= "[{$origin->getName()}]: {$error->getMessage()}\r\n";
        }

        return '' !== $message ? $message : null;
    }
}

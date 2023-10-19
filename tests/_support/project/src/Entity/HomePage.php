<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Entity;

class HomePage extends Page
{
    private ?string $preface;

    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $preface = null
    ) {
        parent::__construct($title, $description);
        $this->preface = $preface;
    }

    public function getPreface(): ?string
    {
        return $this->preface;
    }

    public function setPreface(?string $preface): void
    {
        $this->preface = $preface;
    }
}

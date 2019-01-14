<?php

namespace App\Twig;

use App\Services\UuidEncoder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
class UuidExtension extends AbstractExtension
{
    private $encoder;

    public function __construct(UuidEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFilter(
                'uuid_encode',
                [$this, 'encodeUuid'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function encodeUuid(int $uuid): string
    {
        return $this->encoder->encode($uuid);
    }
}
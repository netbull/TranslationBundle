<?php

namespace NetBull\TranslationBundle\ORM\Subscribers\Translation;

interface TranslationInterface
{
    /**
     * @return array
     */
    public function getMandatoryFields(): array;

    /**
     * @return array
     */
    public function toArray(): array;
}

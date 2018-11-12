<?php

namespace NetBull\TranslationBundle\ORM\Subscribers\Translation;

/**
 * Interface TranslationInterface
 * @package NetBull\TranslationBundle\ORM\Subscribers\Translation
 */
interface TranslationInterface
{
    /**
     * Get Mandatory Fields
     * @return array
     */
    public function getMandatoryFields();

    /**
     * @return array
     */
    public function toArray();
}

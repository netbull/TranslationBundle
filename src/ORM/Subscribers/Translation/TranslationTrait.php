<?php

namespace NetBull\TranslationBundle\ORM\Subscribers\Translation;

use NetBull\DoctrineBehaviors\Model\Translatable\TranslationTrait as NetBullTranslationTrait;
use ReflectionClass;

trait TranslationTrait
{
    use NetBullTranslationTrait;

    /**
     * @return array
     */
    public function getMandatoryFields(): array
    {
        return $this->mandatoryFields ?? [];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $output = [];
        $ref = new ReflectionClass($this);
        $properties = $ref->getProperties();
        foreach ($properties as $property) {
            $getMethod = 'get'.ucfirst($property->name);
            $isMethod = 'is'.ucfirst($property->name);
            if ($ref->hasMethod($getMethod)) {
                $output[$property->name] = $this->{$getMethod}();
                continue;
            }
            if ($ref->hasMethod($isMethod)) {
                $output[$property->name] = $this->{$isMethod}();
            }
        }

        return $output;
    }
}

<?php

namespace NetBull\TranslationBundle\ORM\Subscribers\Translation;

use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait as KnpTranslationTrait;

/**
 * Trait TranslationTrait
 * @package NetBull\TranslationBundle\ORM\Subscribers\Translation
 */
trait TranslationTrait
{
    use KnpTranslationTrait;

    /**
     * {@inheritdoc}
     */
    public function getMandatoryFields()
    {
        return $this->mandatoryFields ?? [];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray()
    {
        $output = [];
        $ref = new \ReflectionClass($this);
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

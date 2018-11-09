<?php

namespace NetBull\TranslationBundle\ORM\Subscribers\Translation;

use Knp\DoctrineBehaviors\Model\Translatable\Translation;

/**
 * Trait TranslationTrait
 * @package NetBull\TranslationBundle\ORM\Subscribers\Translation
 */
trait TranslationTrait
{
    use Translation;

    /**
     * Tells if translation is empty
     * @return bool true if translation is not filled
     */
    public function isEmpty()
    {
        $ignore = true;
        if (isset($this->mandatoryFields)) {
            foreach ($this->mandatoryFields as $man) {
                if (!is_null($this->{$man})) {
                    $ignore = false;
                }
            }
        }

        return $ignore;
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryFields()
    {
        return $this->mandatoryFields;
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

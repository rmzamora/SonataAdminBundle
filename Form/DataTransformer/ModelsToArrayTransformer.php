<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\DataTransformer;

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Symfony\Component\Form\ChoiceList\LegacyChoiceListAdapter;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Class ModelsToArrayTransformer.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelsToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ModelChoiceList
     */
    protected $choiceList;

    /**
     * ModelsToArrayTransformer constructor.
     *
     * @param ModelChoiceList|LazyChoiceList $choiceList
     * @param ModelManagerInterface          $modelManager
     * @param                                $class
     */
    public function __construct($choiceList, ModelManagerInterface $modelManager, $class)
    {
        if ($choiceList instanceof LegacyChoiceListAdapter && $choiceList->getAdaptedList() instanceof ModelChoiceList) {
            $this->choiceList = $choiceList->getAdaptedList();
        } elseif ($choiceList instanceof ModelChoiceList) {
            $this->choiceList = $choiceList;
        } else {
            new \InvalidArgumentException('Argument 1 passed to '.__CLASS__.'::'.__METHOD__.' must be an instance of Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList, instance of '.get_class($choiceList).' given');
        }

        $this->choiceList   = $choiceList;
        $this->modelManager = $modelManager;
        $this->class        = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return array();
        }

        $array = array();
        foreach ($collection as $key => $entity) {
            $id = implode('~', $this->getIdentifierValues($entity));

            $array[] = $id;
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($keys)
    {
        if (!is_array($keys)) {
            throw new UnexpectedTypeException($keys, 'array');
        }

        $collection = $this->modelManager->getModelCollectionInstance($this->class);
        $notFound = array();

        // optimize this into a SELECT WHERE IN query
        foreach ($keys as $key) {
            if ($entity = $this->modelManager->find($this->class, $key)) {
                $collection[] = $entity;
            } else {
                $notFound[] = $key;
            }
        }

        if (count($notFound) > 0) {
            throw new TransformationFailedException(sprintf('The entities with keys "%s" could not be found', implode('", "', $notFound)));
        }

        return $collection;
    }

    /**
     * @param object $entity
     *
     * @return array
     */
    private function getIdentifierValues($entity)
    {
        try {
            return $this->modelManager->getIdentifierValues($entity);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to retrieve the identifier values for entity %s', ClassUtils::getClass($entity)), 0, $e);
        }
    }
}

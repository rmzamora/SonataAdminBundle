<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Widget;

use Symfony\Component\HttpKernel\Kernel;

class FormChoiceWidgetTest extends BaseWidgetTest
{
    protected $type = 'form';

    public function setUp()
    {
        parent::setUp();
    }

    public function testLabelRendering()
    {
        $choices = array('some', 'choices');
        if (!method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) {
            $choices = array_flip($choices);
        }

        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getDefaultOption() + array(
                'multiple' => true,
                'expanded' => true,
            ) + compact('choices')
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertContains(
            '<span>[trans]some[/trans]</span>',
            $this->cleanHtmlWhitespace($html)
        );
    }

    public function testDefaultValueRendering()
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getDefaultOption()
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertContains(
            '<option value="" selected="selected">[trans]Choose an option[/trans]</option>',
            $this->cleanHtmlWhitespace($html)
        );
    }

    public function testRequiredIsDisabledForEmptyPlaceholder()
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getRequiredOption()
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertNotContains(
            'required="required"',
            $this->cleanHtmlWhitespace($html)
        );
    }

    public function testRequiredIsEnabledIfPlaceholderIsSet()
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            array_merge($this->getRequiredOption(), $this->getDefaultOption())
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertContains(
            'required="required"',
            $this->cleanHtmlWhitespace($html)
        );
    }

    protected function getRequiredOption()
    {
        return array('required' => true);
    }

    protected function getChoiceClass()
    {
        if (version_compare(Kernel::VERSION, '2.8.0', '>=')) {
            return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
        } else {
            return 'choice';
        }
    }

    /**
     * For SF < 2.6, we use 'empty_data' to provide default empty value.
     * For SF >= 2.6, we must use 'placeholder' to achieve the same.
     */
    protected function getDefaultOption()
    {
        if (version_compare(Kernel::VERSION, '2.6.0', '>=')) {
            return array(
                'placeholder' => 'Choose an option',
            );
        } else {
            return array(
                'empty_value' => 'Choose an option',
            );
        }
    }
}

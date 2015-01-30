<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Validator;

use Sonata\CoreBundle\Validator\InlineValidator as BaseInlineValidator;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\Validator\Constraint;

/**
 * @deprecated
 */
class InlineValidator extends BaseInlineValidator
{
    /**
     * @param mixed $value
     *
     * @return ErrorElement
     */
    protected function getErrorElement($value)
    {
        return new ErrorElement(
            $value,
            $this->constraintValidatorFactory,
            $this->context,
            $this->context->getGroup()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $errorElement = new ErrorElement(
            $value,
            $this->constraintValidatorFactory,
            $this->context,
            $this->context->getGroup()
        );

        if ($constraint->isClosure()) {
            $function = $constraint->getClosure();
        } else {
            if (is_string($constraint->getService())) {
                $service = $this->container->get($constraint->getService());
            } else {
                $service = $constraint->getService();
            }

            $function = array($service, $constraint->getMethod());
        }

        call_user_func($function, $errorElement, $value);
    }
}

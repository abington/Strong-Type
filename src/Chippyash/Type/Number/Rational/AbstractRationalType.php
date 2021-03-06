<?php
/**
 * Hard type support
 * For when you absolutely want to know what you are getting
 *
 * Thanks to Florian Wolters for the inspiration
 *
 * @copyright Ashley Kitson, UK, 2012
 * @author Ashley Kitson <akitson@zf4.biz>
 * @licence GPL V3 or later : http://www.gnu.org/licenses/gpl.html
 *
 * @link http://github.com/FlorianWolters/PHP-Component-Number-Fraction
 */
namespace Chippyash\Type\Number\Rational;

use Chippyash\Type\AbstractMultiValueType;
use Chippyash\Type\Number\IntType;
use Chippyash\Type\Interfaces\RationalTypeInterface;
use Chippyash\Type\Number\Complex\ComplexType;
use Chippyash\Type\Number\FloatType;

/**
 * Abstract rational number type
 */
abstract class AbstractRationalType extends AbstractMultiValueType implements RationalTypeInterface
{
    /**
     * Do we reduce to lowest form on construct and set?
     *
     * @var boolean
     */
    protected $reduce = true;
        
    /**
     * Map of values for this type
     * @var array
     */
    protected $valueMap = array(
        0 => array('name' => 'num', 'class' => 'Chippyash\Type\Interfaces\NumericTypeInterface'),
        1 => array('name' => 'den', 'class' => 'Chippyash\Type\Interfaces\NumericTypeInterface')
    );

    /**
     * Return the number as a Complex number i.e. n+0i
     *
     * @return \Chippyash\Type\Number\Complex\ComplexType
     */
    public function asComplex()
    {
        return new ComplexType(
            new RationalType(clone $this->numerator(), clone $this->denominator()),
            new RationalType(new IntType(0), new IntType(1))
        );
    }

    /**
     * Return number as Rational number.
     * NB, numerator and denominator will be caste as IntTypes
     *
     * @return \Chippyash\Type\Number\Rational\RationalType
     */
    public function asRational()
    {
        return clone $this;
    }

    /**
     * Return number as an IntType number.
     * Will return floor(n/d)
     *
     * @return \Chippyash\Type\Number\IntType
     */
    public function asIntType()
    {
        return new IntType(floor($this->get()));
    }

    /**
     * Return number as a FloatType number.
     *
     * @return \Chippyash\Type\Number\FloatType
     */
    public function asFloatType()
    {
        return new FloatType($this->get());
    }

    /**
     * Get the numerator
     * @return mixed
     */
    public function numerator()
    {
        return $this->value['num'];
    }

    /**
     * Get the denominator
     *
     * @return mixed
     */
    public function denominator()
    {
        return $this->value['den'];
    }
    
    /**
     * Return the absolute value of the number
     *
     * @return \Chippyash\Type\Number\Rational\RationalType
     */
    public function abs()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return new static($this->value['num']->abs(), $this->value['den']->abs());
    }

    /**
     * Negates the number
     *
     * @return \Chippyash\Type\Number\Rational\RationalType Fluent Interface
     */
    public function negate()
    {
        $this->value['num']->negate();

        return $this;
    }
    

    /**
     * Magic method - convert to string
     * Returns "<num>/<den>" or "<num>" if isInteger()
     *
     * @return string
     */
    public function __toString()
    {
        $num = $this->value['num']->get();
        if ($this->isInteger()) {
            return "{$num}";
        }

        $den = $this->value['den']->get();

        return "{$num}/{$den}";
    }

    /**
     * Get the basic PHP value of the object type properly
     * In this case, the type is an int or float
     *
     * @return int|float
     */
    public function getAsNativeType()
    {
        if ($this->isInteger()) {
            return intval($this->value['num']->get());
        }

        return floatval($this->value['num']->get() / $this->value['den']->get());
    }

    /**
     * Is this Rational an expression of an integer, i.e. n/1
     *
     * @return boolean
     */
    public function isInteger()
    {
        return ($this->value['den']->get() === 1);
    }

    /**
     * Return the sign of this number
     * -1 if < 0
     * 0 if == 0
     * 1 if > 0
     *
     * @return int
     */
    public function sign()
    {
        return $this->value['num']->sign();
    }

    
    /**
     * Reduce this number to it's lowest form
     *
     * @return void
     */
    abstract protected function reduce();

    /**
     * Maps values passed in as parameters to constructor and set methods into
     * the value array
     *
     * @param array $params
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function setFromTypes(array $params)
    {
        parent::setFromTypes($params);
        
        if ($this->reduce) {
            $this->reduce();
        }
        
        if ($this->value['den']->get() < 0) {
            //normalise the sign
            $this->value['num']->negate();
            $this->value['den']->negate();
        }
    }
}

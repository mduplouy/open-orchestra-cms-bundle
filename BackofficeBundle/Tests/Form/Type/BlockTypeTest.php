<?php

namespace OpenOrchestra\BackofficeBundle\Tests\Form\Type;

use OpenOrchestra\BackofficeBundle\Form\Type\BlockType;
use Phake;

/**
 * Test BlockTypeTest
 */
class BlockTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockType
     */
    protected $blockType;

    protected $templateName = 'template';
    protected $generateFormManager;
    protected $fixedParameters;
    protected $formFactory;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->generateFormManager = Phake::mock('OpenOrchestra\BackofficeBundle\StrategyManager\GenerateFormManager');

        Phake::when($this->generateFormManager)->getTemplate(Phake::anyParameters())->thenReturn($this->templateName);
        $this->fixedParameters = array('component', 'submit', 'label', 'class', 'id', 'max_age');
        $this->formFactory = Phake::mock('Symfony\Component\Form\FormFactoryInterface');

        $this->blockType = new BlockType($this->generateFormManager, $this->fixedParameters, $this->formFactory);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->blockType);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('block', $this->blockType->getName());
    }

    /**
     * Test default options
     */
    public function testSetDefaultOptions()
    {
        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $this->blockType->setDefaultOptions($resolver);

        Phake::verify($resolver)->setDefaults(array(
            'blockPosition' => 0,
            'data_class' => null
        ));
    }

    /**
     * @param array $options
     * @param int   $subscriberCount
     *
     * @dataProvider provideOptionsAndCount
     */
    public function testBuildForm(array $options, $subscriberCount)
    {
        $block = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $options = array_merge(array('blockPosition' => 0, 'data' => $block), $options);
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');

        $this->blockType->buildForm($builder, $options);

        Phake::verify($builder, Phake::times(4))->add(Phake::anyParameters());
        Phake::verify($builder)->setAttribute('template', $this->templateName);
        Phake::verify($builder)->addViewTransformer(Phake::anyParameters());
        Phake::verify($builder, Phake::times($subscriberCount))->addEventSubscriber(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function provideOptionsAndCount()
    {
        return array(
            array(array(), 2),
            array(array('disabled' => false), 2),
            array(array('disabled' => true), 1),
        );
    }
}

<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\MediaMetaType;

/**
 * Class MediaMetaTypeTest
 */
class MediaMetaTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MediaMetaType
     */
    protected $form;

    protected $mediaClass = 'site';
    protected $translateValueInitializer;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->translateValueInitializer =  Phake::mock('OpenOrchestra\BackofficeBundle\EventListener\TranslateValueInitializerListener');
        $this->form = new MediaMetaType($this->translateValueInitializer, $this->mediaClass);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->form);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('media_meta', $this->form->getName());
    }

    /**
     * Test builder
     */
    public function testBuilder()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($builder)->add(Phake::anyParameters())->thenReturn($builder);
        Phake::when($builder)->addEventSubscriber(Phake::anyParameters())->thenReturn($builder);

        $this->form->buildForm($builder, array());

        Phake::verify($builder, Phake::times(5))->add(Phake::anyParameters());
        Phake::verify($builder)->addEventSubscriber(Phake::anyParameters());
    }

    /**
     * Test resolver
     */
    public function testSetDefaultOptions()
    {
        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $this->form->setDefaultOptions($resolver);

        Phake::verify($resolver)->setDefaults(array(
            'data_class' => $this->mediaClass
        ));
    }
}

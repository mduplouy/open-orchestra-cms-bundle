<?php

namespace OpenOrchestra\BackofficeBundle\Tests\Functional\EventSubscriber;

use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ContentListStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\SampleStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\VideoStrategy;
use OpenOrchestra\MediaBundle\DisplayBlock\Strategies\GalleryStrategy;
use OpenOrchestra\ModelBundle\Document\Block;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class BlockTypeSubscriberTest
 */
class BlockTypeSubscriberTest extends KernelTestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * Set up the test
     */
    public function setUp()
    {
        static::bootKernel();
        $this->formFactory = static::$kernel->getContainer()->get('form.factory');
    }

    /**
     * Test video block : checkbox unique to uncheck
     */
    public function testVideoBlock()
    {
        $block = new Block();
        $block->setComponent(VideoStrategy::VIDEO);
        $block->addAttribute('videoType', 'youtube');
        $block->addAttribute('youtubeFs', true);

        $form = $this->formFactory->create('block', $block, array('csrf_protection' => false));

        $form->submit(array(
            'id' => 'testId',
            'class' => 'testClass',
            'videoType' => 'youtube',
            'youtubeVideoId' => 'videoId',
            'youtubeAutoplay' => '1',
        ));

        $this->assertTrue($form->isSynchronized());
        /** @var BlockInterface $data */
        $data = $form->getConfig()->getData();
        $this->assertBlock($data);
        $this->assertSame('videoId', $data->getAttribute('youtubeVideoId'));
        $this->assertTrue($data->getAttribute('youtubeAutoplay'));
        $this->assertNull($data->getAttribute('youtubeFs'));
    }

    /**
     * @param string $component
     * @param array  $value
     *
     * @dataProvider provideComponentAndData
     */
    public function testMultipleBlock($component, $value)
    {
        $block = new Block();
        $block->setComponent($component);

        $form = $this->formFactory->create('block', $block, array('csrf_protection' => false));

        $submittedValue = array_merge(array('id' => 'testId', 'class' => 'testClass'), $value);
        $form->submit($submittedValue);

        $this->assertTrue($form->isSynchronized());
        /** @var BlockInterface $data */
        $data = $form->getConfig()->getData();
        $this->assertBlock($data);
        foreach ($value as $key => $sendData) {
            $this->assertSame($sendData, $data->getAttribute($key));
        }
    }

    /**
     * @return array
     */
    public function provideComponentAndData()
    {
        return array(
            array(SampleStrategy::SAMPLE, array(
                'title' => 'title',
                'news' => 'news',
                'author' => 'author',
                'multipleChoice' => array('foo', 'none'),
            )),
            array(GalleryStrategy::GALLERY, array(
                'columnNumber' => 1,
                'itemNumber' => 0,
                'pictures' => array(
                    'media1',
                    'media2'
                )
            )),
            array(ContentListStrategy::CONTENT_LIST, array(
                'contentNodeId' => 'news',
                'contentTemplateEnabled' => true,
            ))
        );
    }

    /**
     * @param $data
     */
    protected function assertBlock($data)
    {
        $this->assertInstanceOf('OpenOrchestra\ModelInterface\Model\BlockInterface', $data);
        $this->assertSame('testId', $data->getId());
        $this->assertSame('testClass', $data->getClass());
    }
}

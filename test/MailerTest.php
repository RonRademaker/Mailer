<?php
namespace RonRademaker\Mailer\Test;

use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;
use RonRademaker\Mailer\Mailer;
use Swift_Message;
use Twig_Environment;

/**
 * Unit test for the Mailer wrapper
 *
 * @author Ron Rademaker
 */
class MailerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $received;

    /**
     * @var MockInterface
     */
    private $swiftMailer;

    /**
     * @var MockInterface
     */
    private $template;

    /**
     * @var MockInterface
     */
    private $environment;

    /**
     * Setup mocks for tests
     */
    public function setUp()
    {
        $this->received = [];
        $this->swiftMailer = Mockery::mock('Swift_Mailer');
        $this->swiftMailer->shouldReceive('send')->andReturnUsing(function (Swift_Message $message) {
                $this->received['from'] = $message->getFrom();
                $this->received['to'] = $message->getTo();
                $this->received['subject'] = $message->getSubject();
        });

        $this->template = Mockery::mock();
        $this->template->shouldReceive('renderBlock')->andReturnUsing(function ($block) {
            return $block;
        });

        $this->environment = \Mockery::mock(Twig_Environment::class);
        $this->environment->shouldReceive('loadTemplate')->andReturn($this->template);
    }


    /**
     * Test sending a mail without really building a Swift_Message
     */
    public function testMailWithoutSwiftMessage()
    {
        $mailer = new Mailer($this->environment, $this->swiftMailer);
        $mailer->sendEmail(['example@example.org' => 'Example Example'], ['sender@example.org' => 'Example Sender'], 'Template');

        $this->assertEquals(['sender@example.org' => 'Example Sender'], $this->received['from']);
        $this->assertEquals(['example@example.org' => 'Example Example'], $this->received['to']);
        $this->assertEquals('subject', $this->received['subject']);
    }

    /**
     * Tests getMessage
     */
    public function testGetMessage()
    {
        $mailer = new Mailer($this->environment, $this->swiftMailer);
        $this->assertNull($mailer->getMessage());

        $mailer->sendEmail(['example@example.org' => 'Example Example'], ['sender@example.org' => 'Example Sender'], 'Template');

        $this->assertInstanceOf(Swift_Message::class, $mailer->getMessage());
    }
}

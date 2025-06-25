<?php
use PHPUnit\Framework\TestCase;
use TwsmsSender\TwsmsSender;

class TwsmsSenderTest extends TestCase
{
        protected $username;
        protected $password;

        private function createMockSender(array $responses)
        {
            $mock = $this->getMockBuilder(TwsmsSender::class)
                ->setConstructorArgs([$this->username, $this->password])
                ->onlyMethods(['getContent'])
                ->getMock();

            $mock->method('getContent')
                ->willReturnOnConsecutiveCalls(...$responses);

            return $mock;
        }

	protected function setUp()
    {
        $config = [];
        if (file_exists('twsms.ini')) {
            $config = parse_ini_file('twsms.ini', true);
        } elseif (file_exists('twsms.ini.bak')) {
            $config = parse_ini_file('twsms.ini.bak', true);
        }

        $this->username = getenv('TWSMS_USERNAME') ?: ($config['twsms']['username'] ?? 'twsms');
        $this->password = getenv('TWSMS_PASSWORD') ?: ($config['twsms']['password'] ?? 'pass');
    }

    public function testQueryPoint()
    {
        $sender = $this->createMockSender([
            '<result><point>10</point><code>00000</code><text>Success</text></result>'
        ]);

        $result = $sender->querypoint();
        $this->assertTrue($result['point'] > 1);
    }
    // //17499786
    // public function testSendQueryAndDel()
    // {
    //     $TwsmsSender = new TwSmsSender($this->username,$this->password);

    //     $result_del = $TwsmsSender->deltime('0975000000' , '17499786' );
    //     $this->assertEquals('Success' , $result_del['text']);   
    // }

    public function testSendQueryAndDel()
    {
        $sender = $this->createMockSender([
            '<result><msgid>1234</msgid><code>00000</code><text>Success</text><statustext>OK</statustext></result>',
            '<result><point>0</point><code>00000</code><text>Success</text></result>',
            '<result><point>0</point><code>00000</code><text>Success</text></result>'
        ]);

        $result = $sender->send('0975000000', 'test sms message', '201612311256' );
        $this->assertNotNull($result['id']);
        $this->assertEquals('Success', $result['text']);

        $result_que = $sender->query('0975000000' , $result['id'] );
        $this->assertEquals('Success' , $result_que['text']);

        $result_del = $sender->deltime('0975000000' , $result['id'] );
        $this->assertEquals('Success' , $result_del['text']);

    }
	public function testSendAndQuery()
    {
        $sender = $this->createMockSender([
            '<result><msgid>5678</msgid><code>00000</code><text>Success</text><statustext>OK</statustext></result>'
        ]);
        $result = $sender->send('0975000000', 'test sms message', '201612312359' );

        $this->assertNotNull($result['id']);
        $this->assertEquals('Success', $result['text']);
        $this->assertEquals('0975000000', $result['recipient']);
        $this->assertEquals('test sms message', $result['body']);        

        // $result = $sender->query('0975000000', $result['id'] );
    }

    public function testSendDirect()
    {
        $sender = $this->createMockSender([
            '<result><msgid>91011</msgid><code>00000</code><text>Success</text><statustext>OK</statustext></result>'
        ]);
        $result = $sender->send('0975000000', 'test sms message', null);

        $this->assertNotNull($result['id']);
        $this->assertEquals('Success', $result['text']);     
    }

}
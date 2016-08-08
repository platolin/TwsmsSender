<?php
use PHPUnit\Framework\TestCase;
use TwsmsSender\TwsmsSender;

class TwsmsSenderTest extends TestCase
{
	protected $username;
	protected $password;

	protected function setUp()
    {
    	$config= parse_ini_file("twsms.ini",true) ;     	
    	$this->username = $config['twsms']['username'];
    	$this->password = $config['twsms']['password'];
    }

	public function testSend()
    {
        $TwsmsSender = new TwSmsSender($this->username,$this->password);
        $result = $TwsmsSender->send('0975000000', 'test sms message', '201612312359' );

        $this->assertNotNull($result['id']);
        $this->assertEquals('Success', $result['text']);
        $this->assertEquals('0975000000', $result['recipient']);
        $this->assertEquals('test sms message', $result['body']);        
    }

    public function testSendDirect()
    {
        $TwsmsSender = new TwSmsSender($this->username,$this->password);
        $result = $TwsmsSender->send('0975000000', 'test sms message', null);

        $this->assertNotNull($result['id']);
        $this->assertEquals('Success', $result['text']);     
    }

}
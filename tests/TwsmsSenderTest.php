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

    public function testQueryPoint()
    {
        $TwsmsSender = new TwSmsSender($this->username,$this->password);
        $result = $TwsmsSender->querypoint();        
        $this->assertTrue($result['point'] > 1 );
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
        $TwsmsSender = new TwSmsSender($this->username,$this->password);

        $result = $TwsmsSender->send('0975000000', 'test sms message', '201612311256' );
        $this->assertNotNull($result['id']);
        $this->assertEquals('Success', $result['text']);
        
        $result_que = $TwsmsSender->query('0975000000' , $result['id'] );
        $this->assertEquals('Success' , $result_que['text']);   

        $result_del = $TwsmsSender->deltime('0975000000' , $result['id'] );
        $this->assertEquals('Success' , $result_del['text']);   

    }
	public function testSendAndQuery()
    {
        $TwsmsSender = new TwSmsSender($this->username,$this->password);
        $result = $TwsmsSender->send('0975000000', 'test sms message', '201612312359' );

        $this->assertNotNull($result['id']);
        $this->assertEquals('Success', $result['text']);
        $this->assertEquals('0975000000', $result['recipient']);
        $this->assertEquals('test sms message', $result['body']);        

        // $result = $TwsmsSender->query('0975000000', $result['id'] );
    }

    public function testSendDirect()
    {
        $TwsmsSender = new TwSmsSender($this->username,$this->password);
        $result = $TwsmsSender->send('0975000000', 'test sms message', null);

        $this->assertNotNull($result['id']);
        $this->assertEquals('Success', $result['text']);     
    }

}
<?php

class ContentManagerServiceProviderTest extends BaseTest
{
	public function testContentManagerWhenUsing() {
		$this->assertInstanceOf(\MicroweberPackages\ContentManager\ContentManager::class, app('content_manager'));
	}
}
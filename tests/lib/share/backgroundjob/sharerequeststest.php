<?php

use OC\Share\BackgroundJob\ShareRequests;

class ShareRequestsTest extends \Test\TestCase {

	/** @var \OCP\IConfig*/
	private $config;

	protected function setUp() {
		parent::setUp();

		$this->config = $this->getMockBuilder('\OCP\IConfig')
			->disableOriginalConstructor()->getMock();
	}

	public function testRequestIsRemovedIfSuccessful() {

		// mock the queue
		$queue = $this->mockQueue('removeRequest');

		$job = $this->getMock('OC\Share\BackgroundJob\ShareRequests',
			['post'],
			[$this->config, $queue]);
		$job->expects($this->any())->method('post')->willReturn(
			['success' => true]
		);
		/** @var OC\Share\BackgroundJob\ShareRequests $job */
		$job->runStep(1);
	}

	public function testRequestIsUpdatedIfFailing() {

		// mock the queue
		$queue = $this->mockQueue('updateRequest');

		// mock the job - necessary to bypass httphelper
		$job = $this->getMock('OC\Share\BackgroundJob\ShareRequests',
			['post'],
			[$this->config, $queue]);
		$job->expects($this->any())->method('post')->willReturn(
			['success' => false]
		);

		/** @var OC\Share\BackgroundJob\ShareRequests $job */
		$job->runStep(1);
	}

	public function testRequestIsRemovedIfFailingTooOften() {

		// mock the queue
		$queue = $this->mockQueue('removeRequest', 5);

		// mock the job - necessary to bypass httphelper
		$job = $this->getMock('OC\Share\BackgroundJob\ShareRequests',
			['post'],
			[$this->config, $queue]);
		$job->expects($this->any())->method('post')->willReturn(
			['success' => false]
		);

		/** @var OC\Share\BackgroundJob\ShareRequests $job */
		$job->runStep(1);
	}

	/**
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockQueue($expectedMethod, $tries = 0) {
		/** @var PHPUnit_Framework_MockObject_MockObject $queue */
		$queue = $this->getMockBuilder('OC\Share\RequestQueue')
			->disableOriginalConstructor()->getMock();
		$queue->expects($this->any())->method('getRequests')->willReturn([
			['url' => 'http://example.com', 'data' => '[]', 'protocol' => '', 'uid' => 'peter', 'tries' => $tries]
		]);
		$queue->expects($this->once())->method($expectedMethod);
		return $queue;
	}
}
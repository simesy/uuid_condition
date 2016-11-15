<?php

/**
 * @file
 * Contains \Drupal\Tests\uuid_condition\Unit\UuidConditionTest
 */

namespace Drupal\Tests\uuid_condition\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\uuid_condition\Plugin\Condition\UuidCondition
 */
class UuidConditionTest extends UnitTestCase {

  /**
   * An otherwise unremarkable UUID to use in tests.
   */
  const MOCK_UUID = '69ebffd1-4f99-4cd8-b73a-d632e49f790c';

  /**
   * @var \Drupal\Tests\uuid_condition\Unit\UnitTestableUuidCondition
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Plugin being tested.
    $this->plugin = new UnitTestableUuidCondition();

    parent::setUp();
  }

  /**
   * Tests evaluate().
   *
   * GIVEN the class UuidCondition
   * WHEN I call evaluate with no configuration.
   * THEN the plugin should always return TRUE.
   *
   * @covers ::evaluate
   *
   * @test
   */
  public function evaluateAlwaysTrueWithNoConfig() {
    $mock_config = $this->getConfigTemplateStub();
    $this->plugin->setConfiguration($mock_config);

    // Test without config: first with no context.
    $this->assertEquals(TRUE, $this->plugin->evaluate(), 'Plugin evaluated with no uuid config should always evaluate to TRUE');

    // Test again with no configuraiton, but now with a valid context.
    /** @var \Drupal\node\Entity\Node|\PHPUnit_Framework_MockObject_MockObject $mock_node */
    $mock_node = $this->getMockBuilder('\Drupal\node\Entity\Node')->disableOriginalConstructor()->getMock();
    $mock_node->expects($this->any())->method('uuid')->will($this->returnValue(self::MOCK_UUID));
    $this->plugin->setMockContextNode($mock_node);
    $this->assertEquals(TRUE, $this->plugin->evaluate(), 'Plugin evaluated with no uuid config should always evaluate to TRUE');
  }

  /**
   * Tests evaluate().
   *
   * GIVEN the class UuidCondition
   * WHEN I call evaluate with no entity context.
   * THEN the plugin should always return TRUE.
   *
   * @covers ::evaluate
   *
   * @test
   */
  public function evaluateAlwaysTrueWithNoContext() {
    // Set some config to ensure the context is checked.
    $mock_config = $this->getConfigTemplateStub();
    $mock_config['uuids'] = self::MOCK_UUID;

    $this->plugin->setConfiguration($mock_config);
    $this->assertEquals(FALSE, $this->plugin->evaluate(), 'Plugin evaluated with no context should always evaluate to FALSE');
  }

  /**
   * Tests evaluate().
   *
   * GIVEN the class UuidCondition
   * WHEN I call evaluate with an entity that has a uuid
   * AND I have various configuration that contains that uuid
   * THEN the plugin should always return TRUE.
   *
   * @covers ::evaluate
   *
   * @test
   */
  public function evaluateTrueWhenConfigMatchesContext() {

    $mock_uuid = self::MOCK_UUID;

    // The context is a valid entity with a ->uuid() that returns $mock_uuid.
    /** @var \Drupal\node\Entity\Node|\PHPUnit_Framework_MockObject_MockObject $mock_node */
    $mock_node = $this->getMockBuilder('\Drupal\node\Entity\Node')->disableOriginalConstructor()->getMock();
    $mock_node->expects($this->any())->method('uuid')->will($this->returnValue($mock_uuid));
    $this->plugin->setMockContextNode($mock_node);

    // We test multiple configuration values.
    $mock_uuid_configs = array(
      'the only config' => $mock_uuid,
      'at the start' => "19dd9013-7a61-46b4-bd96-56506127b85d\n$mock_uuid",
      'at the end' => "$mock_uuid \n 19dd9013-7a61-46b4-bd96-56506127b85d",
      'in the middle' => "19dd9013-7a61-46b4-bd96-56506127b85d  $mock_uuid \r\n 19dd9013-7a61-46b4-bd96-56506127b85d\n",
    );

    $mock_config = $this->getConfigTemplateStub();
    foreach ($mock_uuid_configs as $message => $config) {
      $mock_config['uuids'] = $config;
      $this->plugin->setConfiguration($mock_config);
      // Test without a context with no uuid config.
      $this->assertEquals(TRUE, $this->plugin->evaluate(), "Plugin should evaluate to TRUE when valid uuid is $message.");
    }
  }

  /**
   * Tests evalaute().
   *
   * GIVEN the class UuidCondition
   * WHEN I call evaluate with an entity that has a uuid
   * AND I have valid configuration that does not contain that uuid
   * THEN the plugin should always return FALSE.
   *
   * @covers ::evaluate
   *
   * @test
   */
  public function evaluateFalseWhenConfigDoesNotMatchContext() {
    $mock_config_uuid = self::MOCK_UUID;
    $mock_context_uuid = '19dd9013-7a61-46b4-bd96-56506127b85d';

    /** @var \Drupal\node\Entity\Node|\PHPUnit_Framework_MockObject_MockObject $mock_node */
    $mock_node = $this->getMockBuilder('\Drupal\node\Entity\Node')->disableOriginalConstructor()->getMock();
    $mock_node->expects($this->any())->method('uuid')->will($this->returnValue($mock_context_uuid));
    $this->plugin->setMockContextNode($mock_node);

    // Mock config.
    $mock_config = $this->getConfigTemplateStub();
    $mock_config['uuids'] = $mock_config_uuid;
    $this->plugin->setConfiguration($mock_config);

    $this->assertEquals(FALSE, $this->plugin->evaluate(), "Plugin should evaluate to FALSE when the context uuid does not match config.");
  }

  /**
   * Get a config template stub.
   *
   * @returns array
   *   An array of condition plugin configuration.
   */
  private function getConfigTemplateStub() {
    return array(
      'id' => 'uuid',
      'negate' => FALSE,
      'uuids' => '',
      'context_mapping' => array(
        'node' => '@node.node_route_context:node',
      ),
    );
  }

}

<?php

/**
 * @file
 * Contains \Drupal\uuid_condition\Plugin\Condition\UuidCondition
 *
 * This code is released under the GNU General Public License.
 * This work is Copyright 2016 by Department of Justice and Regulation, Victoria, Australia.
 */

namespace Drupal\uuid_condition\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Content has UUID' condition.
 *
 * @Condition(
 *   id = "uuid",
 *   label = @Translation("Content has UUID"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", required = FALSE, label = @Translation("Node"))
 *   },
 * )
 *
 */
class UuidCondition extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array('uuids' => '') + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['uuids'] = array(
      '#title' => $this->t('Entity UUIDs, one per line'),
      '#type' => 'textarea',
      '#default_value' => $this->configuration['uuids'],
    );
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['uuids'] = $form_state->getValue('uuids');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $uuids = $this->configuration['uuids'];
    return $this->t('The entity id configuration is @uuids', array('@uuids' => $uuids));
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['uuids'])) {
      // Pass through if no configuration found.
      return TRUE;
    }

    // @todo load any type of entity, not just a node.

    $entity = $this->getContextValue('node');
    if (!$entity) {
      // No entity was returned from the context.
      return FALSE;
    }

    $uuid = $entity->uuid();
    if (!$uuid) {
      // Entity has no UUID.
      return FALSE;
    }

    // Return TRUE if the uuid matches any uuid in the block configuration.
    if (strpos($this->configuration['uuids'], $uuid) !== FALSE) {
      return TRUE;
    }
  }

}

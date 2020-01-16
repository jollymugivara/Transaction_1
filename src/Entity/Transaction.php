<?php

namespace Drupal\transaction\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Transaction entity.
 *
 * @ingroup transaction
 *
 * @ContentEntityType(
 *   id = "transaction",
 *   label = @Translation("Transaction"),
 *   handlers = {
 *     "storage" = "Drupal\transaction\TransactionStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\transaction\TransactionListBuilder",
 *     "views_data" = "Drupal\transaction\Entity\TransactionViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\transaction\Form\TransactionForm",
 *       "add" = "Drupal\transaction\Form\TransactionForm",
 *       "edit" = "Drupal\transaction\Form\TransactionForm",
 *       "delete" = "Drupal\transaction\Form\TransactionDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\transaction\TransactionHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\transaction\TransactionAccessControlHandler",
 *   },
 *   base_table = "transaction",
 *   revision_table = "transaction_revision",
 *   revision_data_table = "transaction_field_revision",
 *   translatable = FALSE,
 *   admin_permission = "administer transaction entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/transaction/{transaction}",
 *     "add-form" = "/admin/structure/transaction/add",
 *     "edit-form" = "/admin/structure/transaction/{transaction}/edit",
 *     "delete-form" = "/admin/structure/transaction/{transaction}/delete",
 *     "version-history" = "/admin/structure/transaction/{transaction}/revisions",
 *     "revision" = "/admin/structure/transaction/{transaction}/revisions/{transaction_revision}/view",
 *     "revision_revert" = "/admin/structure/transaction/{transaction}/revisions/{transaction_revision}/revert",
 *     "revision_delete" = "/admin/structure/transaction/{transaction}/revisions/{transaction_revision}/delete",
 *     "collection" = "/admin/structure/transaction/submissions",
 *   },
 *   field_ui_base_route = "transaction.settings"
 * )
 */
class Transaction extends EditorialContentEntityBase implements TransactionInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If no revision author has been set explicitly,
    // make the transaction owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Transaction entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Transaction entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Transaction is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

      //custom
    $fields['summ'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Summ'))
      ->setDescription(t('The summ of the transaction.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 20,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['sender'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Sender'))
      ->setDescription(t('The sender of the transaction.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['receiver'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Receiver'))
      ->setDescription(t('The receiver of the transaction.'))
        ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getSumm() {
    return $this->get('summ')->value;
  }

  public function setSumm($summ) {
    $this->set('summ', $summ);
    return $this;
  }
  /**
   * {@inheritdoc}
   */
  public function getSender() {
    return $this->get('sender')->value;
  }

  public function setSender($sender) {
    $this->set('sender', $sender);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getReceiver() {
    return $this->get('receiver')->value;
  }

  public function setReceiver($receiver) {
    $this->set('receiver', $receiver);
    return $this;
  }


}

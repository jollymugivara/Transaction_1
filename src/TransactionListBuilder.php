<?php

namespace Drupal\transaction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Transaction entities.
 *
 * @ingroup transaction
 */
class TransactionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Transaction ID');
    $header['name'] = $this->t('Name');
    $header['sender'] = $this->t('Sender');
    $header['receiver'] = $this->t('Receiver');
    $header['summ'] = $this->t('Summ');
    $header['date'] = $this->t('Date');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\transaction\Entity\Transaction $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.transaction.edit_form',
      ['transaction' => $entity->id()]
    );
    $row['sender'] = $entity->getSender();
    $row['receiver'] = $entity->getReceiver();
    $row['summ'] = $entity->getSumm();
    $row['date'] = $entity->getCreatedTime();
    return $row + parent::buildRow($entity);
  }

}

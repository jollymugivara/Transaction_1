<?php

namespace Drupal\transaction;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\transaction\Entity\TransactionInterface;

/**
 * Defines the storage handler class for Transaction entities.
 *
 * This extends the base storage class, adding required special handling for
 * Transaction entities.
 *
 * @ingroup transaction
 */
class TransactionStorage extends SqlContentEntityStorage implements TransactionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(TransactionInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {transaction_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {transaction_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

}

<?php

namespace Drupal\transaction;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Transaction entity.
 *
 * @see \Drupal\transaction\Entity\Transaction.
 */
class TransactionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\transaction\Entity\TransactionInterface $entity */

    switch ($operation) {

      case 'view':

        if($account->hasPermission('view all transaction revisions') || $account->hasPermission('edit transaction entities')) {
          return AccessResult::allowed();
        }
        else return AccessResult::allowedIfHasPermission($account, 'view all transaction revisions');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit transaction entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'administer transaction entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer transaction entities');
  }


}

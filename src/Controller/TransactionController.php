<?php

namespace Drupal\transaction\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\transaction\Entity\TransactionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TransactionController.
 *
 *  Returns responses for Transaction routes.
 */
class TransactionController extends ControllerBase implements ContainerInjectionInterface {


  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new TransactionController.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   */
  public function __construct(DateFormatter $date_formatter, Renderer $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays a Transaction revision.
   *
   * @param int $transaction_revision
   *   The Transaction revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($transaction_revision) {
    $transaction = $this->entityTypeManager()->getStorage('transaction')
      ->loadRevision($transaction_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('transaction');

    return $view_builder->view($transaction);
  }

  /**
   * Page title callback for a Transaction revision.
   *
   * @param int $transaction_revision
   *   The Transaction revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($transaction_revision) {
    $transaction = $this->entityTypeManager()->getStorage('transaction')
      ->loadRevision($transaction_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $transaction->label(),
      '%date' => $this->dateFormatter->format($transaction->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Transaction.
   *
   * @param \Drupal\transaction\Entity\TransactionInterface $transaction
   *   A Transaction object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(TransactionInterface $transaction) {
    $account = $this->currentUser();
    $transaction_storage = $this->entityTypeManager()->getStorage('transaction');

    $build['#title'] = $this->t('Revisions for %title', ['%title' => $transaction->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all transaction revisions") || $account->hasPermission('administer transaction entities')));
    $delete_permission = (($account->hasPermission("delete all transaction revisions") || $account->hasPermission('administer transaction entities')));

    $rows = [];

    $vids = $transaction_storage->revisionIds($transaction);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\transaction\TransactionInterface $revision */
      $revision = $transaction_storage->loadRevision($vid);
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $transaction->getRevisionId()) {
          $link = $this->l($date, new Url('entity.transaction.revision', [
            'transaction' => $transaction->id(),
            'transaction_revision' => $vid,
          ]));
        }
        else {
          $link = $transaction->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => Url::fromRoute('entity.transaction.revision_revert', [
                'transaction' => $transaction->id(),
                'transaction_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.transaction.revision_delete', [
                'transaction' => $transaction->id(),
                'transaction_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
    }

    $build['transaction_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
